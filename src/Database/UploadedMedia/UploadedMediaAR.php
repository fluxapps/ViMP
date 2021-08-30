<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace srag\Plugins\ViMP\Database\UploadedMedia;

use ActiveRecord;

/**
 * Class xvmpUploadedMedia
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class UploadedMediaAR extends ActiveRecord {
	const DB_TABLE_NAME = 'xvmp_uploaded_media';


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
	 */
	protected $mid = 0;
	/**
	 * @var String
	 *
	 * @db_has_field        true
	 * @db_is_unique        true
	 * @db_fieldtype        text
	 * @db_length           256
	 */
	protected $tmp_id = 0;
	/**
	 * @var int
	 *
	 * @db_has_field        true
	 * @db_fieldtype        integer
	 * @db_length           8
	 */
	protected $user_id;
    /**
     * @var int
     *
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           8
     */
	protected $ref_id;
	/**
	 * @var int
	 *
	 * @db_has_field        true
	 * @db_fieldtype        text
	 * @db_length           256
	 */
	protected $email;
	/**
	 * @var int
	 *
	 * @db_has_field        true
	 * @db_fieldtype        integer
	 * @db_length           1
	 */
	protected $notification = 1;



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
	 * @return String
	 */
	public function getTmpId() {
		return $this->tmp_id;
	}


	/**
	 * @param String $tmp_id
	 */
	public function setTmpId($tmp_id) {
		$this->tmp_id = $tmp_id;
	}


    /**
     * @return int
     */
    public function getRefId() : int
    {
        return $this->ref_id;
    }


    /**
     * @param int $ref_id
     */
    public function setRefId(int $ref_id)
    {
        $this->ref_id = $ref_id;
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
	 * @return int
	 */
	public function getEmail() {
		return $this->email;
	}


	/**
	 * @param int $email
	 */
	public function setEmail($email) {
		$this->email = $email;
	}

	/**
	 * @return int
	 */
	public function getNotification() {
		return $this->notification;
	}


	/**
	 * @param int $notification
	 */
	public function setNotification($notification) {
		$this->notification = $notification;
	}

}