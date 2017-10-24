<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class xvmpSelectedMedia
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xvmpSelectedMedia extends ActiveRecord {

	const DB_TABLE_NAME = 'xvmp_selected_media';


	public static function returnDbTableName() {
		return self::DB_TABLE_NAME;
	}


	/**
	 * @var int
	 *
	 * @db_has_field        true
	 * @db_is_unique        true
	 * @db_is_primary       true
	 * @db_fieldtype        integer
	 * @db_length           8
	 * @con_sequence        true
	 */
	protected $id = 0;
	/**
	 * @var int
	 *
	 * @db_has_field        true
	 * @db_fieldtype        integer
	 * @db_length           8
	 */
	protected $obj_id;
	/**
	 * @var int
	 *
	 * @db_has_field        true
	 * @db_fieldtype        integer
	 * @db_length           8
	 */
	protected $mid;
	/**
	 * @var int
	 *
	 * @db_has_field        true
	 * @db_fieldtype        integer
	 * @db_length           1
	 */
	protected $visible = 1;
	/**
	 * @var int
	 *
	 * @db_has_field        true
	 * @db_fieldtype        integer
	 * @db_length           8
	 */
	protected $sort;


	/**
	 * @param $mid
	 * @param $obj_id
	 *
	 * @return bool
	 */
	public static function isSelected($mid, $obj_id) {
		return self::where(array('mid' => $mid, 'obj_id' => $obj_id))->hasSets();
	}


	/**
	 * @param $mid
	 * @param $obj_id
	 *
	 * @return bool
	 */
	public static function addVideo($mid, $obj_id) {
		$set = self::where(array('mid' => $mid, 'obj_id' => $obj_id))->first();
		if ($set) {
			return false; // already added
		}

		$set = new self();
		$set->setMid($mid);
		$set->setObjId($obj_id);
		$sort = self::where(array('obj_id' => $obj_id))->count() + 1;
		$set->setSort($sort);
		$set->create();
		return true;
	}

	/**
	 * @param $mid
	 * @param $obj_id
	 *
	 * @return bool
	 */
	public static function removeVideo($mid, $obj_id) {
		$set = self::where(array('mid' => $mid, 'obj_id' => $obj_id))->first();
		if (!$set) {
			return false; // already added
		}

		$set->delete();
		return true;
	}


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
	public function getMid() {
		return $this->mid;
	}


	/**
	 * @param int $mid
	 */
	public function setMid($mid) {
		$this->mid = $mid;
	}


	/**
	 * @return int
	 */
	public function getVisible() {
		return $this->visible;
	}


	/**
	 * @param int $visible
	 */
	public function setVisible($visible) {
		$this->visible = $visible;
	}


	/**
	 * @return int
	 */
	public function getSort() {
		return $this->sort;
	}


	/**
	 * @param int $sort
	 */
	public function setSort($sort) {
		$this->sort = $sort;
	}

}