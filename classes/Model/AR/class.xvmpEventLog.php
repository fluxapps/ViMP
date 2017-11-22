<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class xvmpEventLog
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xvmpEventLog extends ActiveRecord {

	const DB_TABLE_NAME = 'xvmp_event_log';


	public static function returnDbTableName() {
		return self::DB_TABLE_NAME;
	}

	const ACTION_UPLOAD = 1;
	const ACTION_EDIT = 2;
	const ACTION_CHANGE_OWNER = 3;

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
	protected $action;
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
	 * @var String
	 *
	 * @db_has_field        true
	 * @db_fieldtype        text
	 * @db_length           256
	 */
	protected $title;
	/**
	 * @var string
	 *
	 * @db_has_field        true
	 * @db_fieldtype        timestamp
	 */
	protected $timestamp;
	/**
	 * @var String
	 *
	 * @db_has_field        true
	 * @db_fieldtype        text
	 * @db_length           256
	 */
	protected $login;

}