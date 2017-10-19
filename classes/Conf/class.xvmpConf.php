<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once('./Services/ActiveRecord/class.ActiveRecord.php');

/**
 * Class xvmpConf
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xvmpConf extends ActiveRecord {

	const DB_TABLE_NAME = 'xvmp_config';

	const F_OBJECT_TITLE = 'object_title';
	const F_API_USER = 'api_user';
	const F_API_PASSWORD = 'api_password';
	const F_API_URL = 'api_url';
	const F_USER_MAPPING_EXTERNAL = 'user_mapping_ext';
	const F_USER_MAPPING_LOCAL = 'user_mapping_local';
	const F_ALLOW_PUBLIC_UPLOAD = 'allow_public_upload';
	const F_REQUIRED_METADATA = 'required_metadata';
	const F_MEDIA_PERMISSIONS = 'media_permissions';

	/**
	 * @var array
	 */
	protected static $cache = array();
	/**
	 * @var array
	 */
	protected static $cache_loaded = array();

	/**
	 * @var string
	 *
	 * @db_has_field        true
	 * @db_is_unique        true
	 * @db_is_primary       true
	 * @db_is_notnull       true
	 * @db_fieldtype        text
	 * @db_length           250
	 */
	protected $name;
	/**
	 * @var string
	 *
	 * @db_has_field        true
	 * @db_fieldtype        text
	 * @db_length           4000
	 */
	protected $value;


	public static function returnDbTableName() {
		return self::DB_TABLE_NAME;
	}

	/**
	 * @param $name
	 *
	 * @return mixed
	 */
	public static function getConfig($name) {
		if (!self::$cache_loaded[$name]) {
			$obj = new self($name);
			self::$cache[$name] = json_decode($obj->getValue());
			self::$cache_loaded[$name] = true;
		}

		return self::$cache[$name];
	}


	/**
	 * @param $name
	 * @param $value
	 */
	public static function set($name, $value) {
		$obj = new self($name);
		$obj->setValue(json_encode($value));

		if (self::where(array( 'name' => $name ))->hasSets()) {
			$obj->update();
		} else {
			$obj->create();
		}
	}


	/**
	 * @param string $name
	 */
	public function setName($name) {
		$this->name = $name;
	}


	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}


	/**
	 * @param string $value
	 */
	public function setValue($value) {
		$this->value = $value;
	}


	/**
	 * @return string
	 */
	public function getValue() {
		return $this->value;
	}
}