<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace srag\Plugins\ViMP\Database\UserLPStatus;

use ActiveRecord;
use ilLPStatus;
use ilLPStatusWrapper;
use srag\Plugins\ViMP\Database\SelectedMedia\SelectedMediaAR;
use srag\Plugins\ViMP\Database\UserProgress\UserProgressAR;
use xvmp;
use ilObject2;

/**
 * Class xvmpUserLPStatus
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class UserLPStatusAR extends ActiveRecord {

	const TABLE_NAME = 'xvmp_lp_status';

	/**
	 * @return string
	 */
	static function returnDbTableName() {
		return self::TABLE_NAME;
	}

	/**
	 * @var int
	 *
	 * @db_has_field    true
	 * @db_fieldtype    integer
	 * @db_length       8
	 * @db_is_primary   true
	 * @db_sequence     true
	 */
	protected $id = 0;

	/**
	 * @var int
	 *
	 * @db_has_field    true
	 * @db_fieldtype    integer
	 * @db_length       8
	 */
	protected $obj_id = 0;

	/**
	 * @var int
	 *
	 * @db_has_field    true
	 * @db_fieldtype    integer
	 * @db_length       8
	 */
	protected $user_id = 0;

	/**
	 * @var string
	 *
	 * @db_has_field    true
	 * @db_fieldtype    timestamp
	 */
	protected $created_at;

	/**
	 * @var string
	 *
	 * @db_has_field    true
	 * @db_fieldtype    timestamp
	 */
	protected $updated_at;

	/**
	 * @var int
	 *
	 * @db_has_field    true
	 * @db_fieldtype    integer
	 * @db_length       8
	 */
	protected $status = ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;



	/**
	 * @var bool
	 */
	protected $status_changed = false;

	/**
	 * @var int
	 */
	protected $old_status;


	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}


	/**
	 * @param int $id
	 */
	public function setId($id) {
		$this->id = $id;
	}


	/**
	 * @return int
	 */
	public function getObjId() {
		return $this->obj_id;
	}


	/**
	 * @param int $obj_id
	 */
	public function setObjId($obj_id) {
		$this->obj_id = $obj_id;
	}


	/**
	 * @return int
	 */
	public function getUserId() {
		return $this->user_id;
	}


	/**
	 * @param int $user_id
	 */
	public function setUserId($user_id) {
		$this->user_id = $user_id;
	}


	/**
	 * @return string
	 */
	public function getCreatedAt() {
		return $this->created_at;
	}


	/**
	 * @param string $created_at
	 */
	public function setCreatedAt($created_at) {
		$this->created_at = $created_at;
	}


	/**
	 * @return string
	 */
	public function getUpdatedAt() {
		return $this->updated_at;
	}


	/**
	 * @param string $updated_at
	 */
	public function setUpdatedAt($updated_at) {
		$this->updated_at = $updated_at;
	}


	/**
	 * @return int
	 */
	public function getStatus() {
		return $this->status;
	}


	/**
	 * @param int $status
	 */
	public function setStatus($status) {
		if ($status != $this->status) {
			$this->old_status = $this->status;
			$this->status_changed = true;
		}
		$this->status = $status;
	}


	/**
	 * @return bool
	 */
	public function hasStatusChanged() {
		return $this->status_changed;
	}


	/**
	 * @param bool $status_changed
	 */
	public function setStatusChanged($status_changed) {
		$this->status_changed = $status_changed;
	}


	/**
	 * @return int
	 */
	public function getOldStatus() {
		return $this->old_status;
	}


	/**
	 * @param int $old_status
	 */
	public function setOldStatus($old_status) {
		$this->old_status = $old_status;
	}


	/**
	 *
	 */
	public function create()
	{
		$this->created_at = date('Y-m-d H:i:s');
		$this->updated_at = date('Y-m-d H:i:s');
		parent::create();
	}

	/**
	 *
	 */
	public function update()
	{
		$this->updated_at = date('Y-m-d H:i:s');
		parent::update();

		if ($this->hasStatusChanged()) {
			ilLPStatusWrapper::_updateStatus($this->getObjId(), $this->getUserId());
		}
	}

	/**
	 * @param $user_id
	 * @param $obj_id
	 *
	 * @return ActiveRecord|UserLPStatusAR
	 */
	public static function getInstance($user_id, $obj_id) {
		$xvmpUserLPStatus = UserLPStatusAR::where(array('user_id' => $user_id, 'obj_id' => $obj_id))->first();
		if (!$xvmpUserLPStatus) {
			$xvmpUserLPStatus = new self();
			$xvmpUserLPStatus->setUserId($user_id);
			$xvmpUserLPStatus->setObjId($obj_id);
		}
		return $xvmpUserLPStatus;
	}


	/**
	 *
	 */
	public function updateStatus() {
		$progress = false;
		$complete = true;
		/** @var SelectedMediaAR $selected_medium */
		foreach (SelectedMediaAR::where(array('obj_id' => $this->getObjId(), 'lp_is_required' => 1, 'visible' => 1))->get() as $selected_medium) {
			$reached_percentage = UserProgressAR::calcPercentage($this->getUserId(), $selected_medium->getMid());
			if ($reached_percentage > 0) {
				$progress = true;
			}
			if ($reached_percentage < $selected_medium->getLpReqPercentage()) {
				$complete = false;
			}
		}

		if ($complete && $progress) {   // the check for $progress catches the case if no media is "lp_required"
			$this->setStatus(ilLPStatus::LP_STATUS_COMPLETED_NUM);
		}
		elseif ($progress) {
			$this->setStatus(ilLPStatus::LP_STATUS_IN_PROGRESS_NUM);
		}
		else {
			$this->setStatus(ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM);
		}
	}


    /**
     * @param int  $id
     * @param bool $is_ref_id
     * @param int  $user_id
     */
	public static function updateLPStatuses($id = 0, $is_ref_id = true, $user_id = 0) {
		if (!$id) {
			$id = $_GET['ref_id'];
		}
        $users = ($user_id > 0) ? [$user_id] : xvmp::getCourseMembers($id, $is_ref_id);
        foreach ($users as $usr_id) {
			$user_status = self::getInstance($usr_id, $is_ref_id ? ilObject2::_lookupObjectId($id) : $id);
			$user_status->updateStatus();
			$user_status->store();
		}
	}
}