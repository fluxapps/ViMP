<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace srag\Plugins\ViMP\Database\Settings;

use ActiveRecord;
use xvmp;

/**
 * Class SettingsAR
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class SettingsAR extends ActiveRecord {

	const DB_TABLE_NAME = 'xvmp_setting';

	const LAYOUT_TYPE_LIST = 1;
	const LAYOUT_TYPE_TILES = 2;
	const LAYOUT_TYPE_PLAYER = 3;

	/**
	 * @var int
	 *
	 * @db_has_field        true
	 * @db_is_unique        true
	 * @db_is_primary       true
	 * @db_fieldtype        integer
	 * @db_length           8
	 */
	protected $obj_id;
	/**
	 * @var int
	 *
	 * @db_has_field        true
	 * @db_fieldtype        integer
	 * @db_length           1
	 */
	protected $is_online = 0;
	/**
	 * @var int
	 *
	 * @db_has_field        true
	 * @db_fieldtype        integer
	 * @db_length           2
	 */
	protected $layout_type = self::LAYOUT_TYPE_LIST;
	/**
	 * @var int
	 *
	 * @db_has_field        true
	 * @db_fieldtype        integer
	 * @db_length           2
	 */
	protected $repository_preview = 0;
	/**
	 * @var int
	 *
	 * @db_has_field        true
	 * @db_fieldtype        integer
	 * @db_length           1
	 */
	protected $lp_active = 0;


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
	public function getIsOnline() {
		return $this->is_online;
	}


	/**
	 * @param int $is_online
	 */
	public function setIsOnline($is_online) {
		$this->is_online = $is_online;
	}



	/**
	 * @return int
	 */
	public function getLayoutType() {
		return $this->layout_type;
	}


	/**
	 * @param int $layout_type
	 */
	public function setLayoutType($layout_type) {
		$this->layout_type = $layout_type;
	}


	/**
	 * @return int
	 */
	public function getRepositoryPreview() {
		return $this->repository_preview;
	}


	/**
	 * @param int $repository_preview
	 */
	public function setRepositoryPreview($repository_preview) {
		$this->repository_preview = $repository_preview;
	}


	/**
	 * @return int
	 */
	public function getLpActive() {
		return $this->lp_active && xvmp::isLearningProgressPossible($this->getObjId());
	}


	/**
	 * @param int $lp_active
	 */
	public function setLpActive($lp_active) {
		$this->lp_active = $lp_active;
	}


	public static function returnDbTableName() {
		return self::DB_TABLE_NAME;
	}





}