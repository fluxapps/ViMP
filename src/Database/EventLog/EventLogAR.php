<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace srag\Plugins\ViMP\Database\EventLog;

use ActiveRecord;

/**
 * Class xvmpEventLog
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class EventLogAR extends ActiveRecord {

	const DB_TABLE_NAME = 'xvmp_event_log';

	protected static $logged_media_fields = array(
		'title', 'description', 'published', 'mediapermissions', 'categories', 'tags'
	);


	public static function returnDbTableName() {
		return self::DB_TABLE_NAME;
	}

	const ACTION_UPLOAD = 1;
	const ACTION_EDIT = 2;
	const ACTION_DELETE = 3;
	const ACTION_ADD = 4;
	const ACTION_REMOVE = 5;
	const ACTION_CHANGE_OWNER = 6;

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
	 * @var String
	 *
	 * @db_has_field        true
	 * @db_fieldtype        text
	 * @db_length           256
	 */
	protected $login;
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
	 * @var String
	 *
	 * @db_has_field        true
	 * @db_fieldtype        text
	 * @db_length           4000
	 */
	protected $data;
	/**
	 * @var int
	 *
	 * @db_has_field        true
	 * @db_fieldtype        integer
	 * @db_length           8
	 */
	protected $timestamp;


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
	 * @return String
	 */
	public function getLogin() {
		return $this->login;
	}


	/**
	 * @param String $login
	 */
	public function setLogin($login) {
		$this->login = $login;
	}


	/**
	 * @return int
	 */
	public function getAction() {
		return $this->action;
	}


	/**
	 * @param int $action
	 */
	public function setAction($action) {
		$this->action = $action;
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
	 * @return String
	 */
	public function getTitle() {
		return $this->title;
	}


	/**
	 * @param String $title
	 */
	public function setTitle($title) {
		$this->title = $title;
	}


	/**
	 * @return array
	 */
	public function getData() {
		return $this->data;
	}


	/**
	 * @param array $data
	 */
	public function setData($data) {
		$this->data = $data;
	}


	/**
	 * @return int
	 */
	public function getTimestamp() {
		return $this->timestamp;
	}


	/**
	 * @param int $timestamp
	 */
	public function setTimestamp($timestamp) {
		$this->timestamp = $timestamp;
	}

	public static function logEvent($action, $obj_id, $data, $old_data = null) {
		$eventlog_data = array();

		switch ($action) {
			case self::ACTION_EDIT:
				foreach (self::$logged_media_fields as $field) {
					if ($old_data[$field] != $data[$field]) {
						$eventlog_data[$field] = array($old_data[$field], $data[$field]);
					}
				}
				break;
			case self::ACTION_CHANGE_OWNER:
				$eventlog_data['owner'] = $data['owner'];
				break;
			default:
				foreach (self::$logged_media_fields as $field) {
					$eventlog_data[$field] = $data[$field];
				}
				break;
		}

		if (empty($eventlog_data)) {
			return false;
		}

		$eventLog = new self();
		$eventLog->setMid($data['mid']);
		$eventLog->setObjId($obj_id);
		$eventLog->setAction($action);
		$eventLog->setTitle($data['title']);
		$eventLog->setData($eventlog_data);
		$eventLog->create();
	}

	public function create() {
		global $DIC;
		$ilUser = $DIC['ilUser'];
		$this->setTimestamp(time());
		$this->setLogin($ilUser->getLogin());
		parent::create();
	}


	public function sleep($field_name) {
		switch ($field_name) {
			case 'data':
				return json_encode($this->data);
			default:
				return parent::sleep($field_name);
		}
	}


	public function wakeUp($field_name, $field_value) {
		switch ($field_name) {
			case 'data':
				return json_decode($field_value, true);
			default:
				return parent::wakeUp($field_name, $field_value);
		}
	}
}