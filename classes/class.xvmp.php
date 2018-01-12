<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class xvmp
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xvmp {

	const ILIAS_50 = 50;
	const ILIAS_51 = 51;
	const ILIAS_52 = 52;
	const ILIAS_53 = 53;
	const MIN_ILIAS_VERSION = self::ILIAS_50;

	/**
	 * @return int
	 */
	public static function getILIASVersion() {
		if (strpos(ILIAS_VERSION_NUMERIC, 'alpha') || strpos(ILIAS_VERSION_NUMERIC, 'beta')
			|| ilComponent::isVersionGreaterString(ILIAS_VERSION_NUMERIC, '5.2.999')) {
			return self::ILIAS_53;
		}
		if (ilComponent::isVersionGreaterString(ILIAS_VERSION_NUMERIC, '5.1.999')) {
			return self::ILIAS_52;
		}
		if (ilComponent::isVersionGreaterString(ILIAS_VERSION_NUMERIC, '5.0.999')) {
			return self::ILIAS_51;
		}
		if (ilComponent::isVersionGreaterString(ILIAS_VERSION_NUMERIC, '4.9.999')) {
			return self::ILIAS_50;
		}

		return 0;
	}

	/**
	 * @return bool
	 */
	public static function is50() {
		return self::getILIASVersion() >= self::ILIAS_50;
	}

	/**
	 * @return bool
	 */
	public static function is51() {
		return self::getILIASVersion() >= self::ILIAS_51;
	}

	/**
	 * @return bool
	 */
	public static function is52() {
		return self::getILIASVersion() >= self::ILIAS_52;
	}


	/**
	 * @return mixed
	 */
	public static function getToken() {
//		if (!$token = xvmpConf::getConfig(xvmpConf::F_TOKEN)) {
			$token = self::loadToken();
//		}
		return $token;
	}


	/**
	 * @return mixed
	 */
	public static function loadToken() {
		static $token;
		if (!$token) {
			$response = xvmpRequest::loginUser(xvmpConf::getConfig(xvmpConf::F_API_USER),xvmpConf::getConfig(xvmpConf::F_API_PASSWORD))->getResponseArray();
			$token = $response['token'];
		}
		return $token;
	}


	/**
	 *
	 */
	public static function resetToken() {
		xvmpConf::set(xvmpConf::F_TOKEN, '');
	}


	/**
	 * @param $obj_id
	 *
	 * @return mixed
	 */
	public static function lookupRefId($obj_id) {
		return array_shift(ilObject2::_getAllReferences($obj_id));
	}


	/**
	 * @param $obj_id
	 *
	 * @return bool
	 */
	public static function isLearningProgressActive($obj_id) {
		$ref_id = self::lookupRefId($obj_id);
		return (ilObjUserTracking::_enabledLearningProgress() && self::getParentCourseRefId($ref_id));
	}


	/**
	 * @param $ref_id
	 *
	 * @return bool|int
	 */
	public static function getParentCourseRefId($ref_id) {
		global $DIC;
		$tree = $DIC['tree'];
		/**
		 * @var $tree ilTree
		 */
		while (ilObject2::_lookupType($ref_id, true) != 'crs') {
			if ($ref_id == 1) {
				return false;
			}
			$ref_id = $tree->getParentId($ref_id);
		}

		return $ref_id;
	}


	/**
	 * @param $id
	 *
	 * @return array
	 */
	public static function getCourseMembers($id, $is_ref_id = true) {
		$members = array();
		$ref_id = self::getParentCourseRefId($is_ref_id ? $id : self::lookupRefId($id));
		if ($ref_id) {
			global $DIC;
			$rbacreview = $DIC['rbacreview'];
			$crs = new ilObjCourse($ref_id);
			$member_role = $crs->getDefaultMemberRole();
			$members = $rbacreview->assignedUsers($member_role);
		}
		return $members;
	}
}