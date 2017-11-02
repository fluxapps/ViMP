<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Class ilObjViMPAccess
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ilObjViMPAccess extends ilObjectPluginAccess {

	/**
	 * @param string $a_cmd
	 * @param string $a_permission
	 * @param int $a_ref_id
	 * @param int $a_obj_id
	 * @param string $a_user_id
	 *
	 * @return bool
	 */
	public function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id = NULL, $a_user_id = '') {
		global $ilUser, $ilAccess;
		/**
		 * @var $ilAccess ilAccessHandler
		 */
		if ($a_user_id == '') {
			$a_user_id = $ilUser->getId();
		}
		if ($a_obj_id === NULL) {
			$a_obj_id = ilObject2::_lookupObjId($a_ref_id);
		}

		switch ($a_permission) {
			case 'read':
				if (!self::checkOnline($a_obj_id) AND !$ilAccess->checkAccessOfUser($a_user_id, 'write', '', $a_ref_id)) {
					return false;
				}
				break;
			case 'visible':
				if (!self::checkOnline($a_obj_id) AND !$ilAccess->checkAccessOfUser($a_user_id, 'write', '', $a_ref_id)) {
					return false;
				}
				break;
		}

		return true;
	}

	/**
	 * @param $ref_id
	 *
	 * @return bool
	 */
	public static function hasWriteAccess($ref_id = NULL) {
		if ($ref_id === NULL) {
			$ref_id = $_GET['ref_id'];
		}
		global $ilAccess;

		/**
		 * @var $ilAccess ilAccesshandler
		 */

		return $ilAccess->checkAccess('write', '', $ref_id);
	}

	/**
	 * @param $ref_id
	 *
	 * @return bool
	 */
	public static function hasReadAccess($ref_id = NULL) {
		if ($ref_id === NULL) {
			$ref_id = $_GET['ref_id'];
		}
		global $ilAccess;

		/**
		 * @var $ilAccess ilAccesshandler
		 */

		return $ilAccess->checkAccess('read', '', $ref_id);
	}

	/**
	 * @param $ref_id
	 *
	 * @return bool
	 */
	public static function hasUploadPermission($ref_id = NULL) {
		if ($ref_id === NULL) {
			$ref_id = $_GET['ref_id'];
		}
		global $ilAccess;

		/**
		 * @var $ilAccess ilAccesshandler
		 */

		return $ilAccess->checkAccess('rep_robj_xvmp_perm_upload', '', $ref_id);
	}




	/**
	 * @param $obj_id
	 *
	 * @return mixed
	 */
	public function checkOnline($obj_id) {
		return xvmpSettings::find($obj_id)->getIsOnline();
	}
}