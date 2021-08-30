<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace srag\Plugins\ViMP\Database\Config;

use ActiveRecord;
use Matrix\Exception; // ToDo: Is that right?

/**
 * Class xvmpConf
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ConfigAR extends ActiveRecord {

	const DB_TABLE_NAME = 'xvmp_config';

	const CONFIG_VERSION = 1;

	const F_CONFIG_VERSION = 'config_version';

	const F_OBJECT_TITLE = 'object_title';
	const F_API_KEY = 'api_key';
	const F_API_USER = 'api_user';
	const F_API_PASSWORD = 'api_password';
	const F_API_URL = 'api_url';
	const F_DISABLE_VERIFY_PEER = 'disable_verify_peer';
	const F_USER_MAPPING_EXTERNAL = 'user_mapping_ext';
	const F_USER_MAPPING_LOCAL = 'user_mapping_local';

	const F_MAPPING_PRIORITY = 'mapping_priority';
	const PRIORITIZE_EMAIL = 0;
	const PRIORITIZE_MAPPING = 1;

	const F_ALLOW_PUBLIC = 'allow_public';
	const F_ALLOW_PUBLIC_UPLOAD = 'allow_public_upload';
	const F_DEFAULT_PUBLICATION = 'default_publication';
	const F_MEDIA_PERMISSIONS = 'media_permissions';
	const F_MEDIA_PERMISSIONS_SELECTION = 'media_permissions_selection';
	const F_MEDIA_PERMISSIONS_PRESELECTED = 'media_permissions_preselected';
	const F_NOTIFICATION_SUBJECT_SUCCESSFULL = 'notification_subject';
	const F_NOTIFICATION_BODY_SUCCESSFULL = 'notification_body';
	const F_NOTIFICATION_SUBJECT_FAILED = 'notification_subject_failed';
	const F_NOTIFICATION_BODY_FAILED = 'notification_body_failed';
	const F_CACHE_TTL_VIDEOS = 'cache_ttl_videos';
	const F_CACHE_TTL_USERS = 'cache_ttl_users';
	const F_CACHE_TTL_CATEGORIES = 'cache_ttl_categories';
	const F_CACHE_TTL_TOKEN = 'cache_ttl_token';
	const F_CACHE_TTL_CONFIG = 'cache_ttl_config';
	const F_FILTER_FIELDS = 'filter_fields';
	const F_FILTER_FIELD_ID = 'filter_id';
	const F_FILTER_FIELD_TITLE = 'filter_title';
	const F_FORM_FIELDS = 'form_fields';
	const F_FORM_FIELD_ID = 'field_id';
	const F_FORM_FIELD_TITLE = 'field_title';
	const F_FORM_FIELD_REQUIRED = 'required';
	const F_FORM_FIELD_FILL_USER_DATA = 'fill_user_data';
    const F_FORM_FIELD_SHOW_IN_PLAYER = 'show_in_player';
    const F_FORM_FIELD_TYPE = 'field_type';
    const F_FORM_FIELD_TYPE_TEXT = 0;

    const F_FORM_FIELD_TYPE_CHECKBOX = 1;
    const F_UPLOAD_LIMIT = 'upload_limit';
    const F_TOKEN = 'token';

    const F_EMBED_PLAYER = 'embed_player';
    const MEDIA_PERMISSION_OFF = 0;
    const MEDIA_PERMISSION_ON = 1;
    const MEDIA_PERMISSION_SELECTION = 2;
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
			try {
				$obj = new self($name);
			} catch (Exception $e) {
				$obj = new self();
				$obj->setName($name);
			}
			self::$cache[$name] = json_decode($obj->getValue(), true);
			self::$cache_loaded[$name] = true;
		}

		return self::$cache[$name];
	}


	/**
	 * @param $name
	 * @param $value
	 */
	public static function set($name, $value) {
		try {
			$obj = new self($name);
		} catch (Exception $e) {
			$obj = new self();
			$obj->setName($name);
		}
		$obj->setValue(json_encode($value));
		$obj->store();
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
