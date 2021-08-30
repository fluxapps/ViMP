<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use srag\Plugins\ViMP\Database\Settings\SettingsAR;
use srag\Plugins\ViMP\Database\SelectedMedia\SelectedMediaAR;
use srag\Plugins\ViMP\Database\UserLPStatus\UserLPStatusAR;
use srag\Plugins\ViMP\Database\EventLog\EventLogAR;

require_once __DIR__ . '/../vendor/autoload.php';
/**
 * Class ilObjViMP
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 *
 */
class ilObjViMP extends ilObjectPlugin implements ilLPStatusPluginInterface {

	protected function initType() {
		$this->setType(ilViMPPlugin::XVMP);
	}


	protected function doCreate() {
		$SettingsAR = new SettingsAR();
		$SettingsAR->setObjId($this->getId());
		$SettingsAR->create();
	}


	protected function doDelete() {
		SettingsAR::find($this->getId())->delete();
		foreach (SelectedMediaAR::where(array('obj_id' => $this->getId()))->get() as $selected_media) {
			$selected_media->delete();
		}
		foreach (UserLPStatusAR::where(array('obj_id' => $this->getId()))->get() as $user_status) {
			$user_status->delete();
		}
		foreach (EventLogAR::where(array('obj_id' => $this->getId()))->get() as $event_log) {
			$event_log->delete();
		}
	}


	public function getLPCompleted() {
		return UserLPStatusAR::where(array(
			'status' => ilLPStatus::LP_STATUS_COMPLETED_NUM,
			'obj_id' => $this->getId()
		))->getArray(null, 'user_id');
	}


	public function getLPNotAttempted() {
		$operators = array(
			'status' => '!=',
			'obj_id' => '='
		);
		$other_than_not_attempted = UserLPStatusAR::where(array(
			'status' => ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM,
			'obj_id' => $this->getId()
		), $operators)->getArray(null, 'user_id');

		return array_diff(xvmp::getCourseMembers($this->getId(), false), $other_than_not_attempted);

	}


	public function getLPFailed() {
		return array(); // it's not possible to fail
	}


	public function getLPInProgress() {
		return UserLPStatusAR::where(array(
			'status' => ilLPStatus::LP_STATUS_IN_PROGRESS_NUM,
			'obj_id' => $this->getId()
		))->getArray(null, 'user_id');
	}


	public function getLPStatusForUser($a_user_id) {
		$user_status = UserLPStatusAR::where(array(
			'user_id' => $a_user_id,
			'obj_id' => $this->getId()
		))->first();
		if ($user_status) {
			return $user_status->getStatus();
		}
		return ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
	}
}