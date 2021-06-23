<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class xvmp
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xvmp {

    const ILIAS_54 = 54;
    const ILIAS_6 = 6;
    const TOKEN = 'token';


    /**
	 * @return int
	 */
	public static function getILIASVersion() {
		if (ilComponent::isVersionGreaterString(ILIAS_VERSION_NUMERIC, '5.4.999')) {
			return self::ILIAS_6;
		}
		if (ilComponent::isVersionGreaterString(ILIAS_VERSION_NUMERIC, '5.3.999')) {
			return self::ILIAS_54;
		}

		return 0;
	}

	/**
	 * @return bool
	 */
	public static function is6() {
		return self::getILIASVersion() >= self::ILIAS_6;
	}


	/**
	 * @return bool
	 */
	public static function is54() {
		return self::getILIASVersion() >= self::ILIAS_54;
	}


	/**
	 * @return bool|mixed|string|null
	 */
	public static function getViMPVersion() {
		$key = 'version';
		$existing = xvmpCacheFactory::getInstance()->get($key);
		if ($existing) {
			xvmpCurlLog::getInstance()->write('CACHE: used cached: ' . $key, xvmpCurlLog::DEBUG_LEVEL_2);
			return $existing;
		}

		$response = xvmpRequest::version()->getResponseArray()['info']['version'];
		$vimp_version = substr($response, 0, strpos($response, ' '));

		xvmpCurlLog::getInstance()->write('CACHE: added to cache: ' . $key, xvmpCurlLog::DEBUG_LEVEL_1);
		xvmpCacheFactory::getInstance()->set($key, $vimp_version, 0);

		return $vimp_version;
	}


	/**
	 * @param $version
	 *
	 * @return mixed
	 */
	public static function ViMPVersionEquals($version) {
		$vimp_version = self::getViMPVersion();

		return version_compare($vimp_version, $version, '=');
	}


	/**
	 *
	 * @param $version
	 *
	 * @return mixed
	 */
	public static function ViMPVersionGreaterEquals($version) {
		$vimp_version = self::getViMPVersion();

		return version_compare($vimp_version, $version, '>=');
	}


	/**
	 * @return mixed
	 */
	public static function getToken() {
		$token = xvmpCacheFactory::getInstance()->get(self::TOKEN);
		if ($token) {
			xvmpCurlLog::getInstance()->write('CACHE: used cached: ' . self::TOKEN, xvmpCurlLog::DEBUG_LEVEL_2);

			return $token;
		}

		xvmpCurlLog::getInstance()->write('CACHE: cached not used: ' . self::TOKEN, xvmpCurlLog::DEBUG_LEVEL_2);

		$response = xvmpRequest::loginUser(xvmpConf::getConfig(xvmpConf::F_API_USER), xvmpConf::getConfig(xvmpConf::F_API_PASSWORD))
			->getResponseArray();
		$token = $response[self::TOKEN];
		xvmpCacheFactory::getInstance()->set(self::TOKEN, $token, xvmpConf::getConfig(xvmpConf::F_CACHE_TTL_TOKEN));

		return $token;
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
	public static function isLearningProgressPossible($obj_id) {
		$ref_id = self::lookupRefId($obj_id);

		return (ilObjUserTracking::_enabledLearningProgress() && self::getParentCourseRefId($ref_id));
	}

    /**
     * @return bool
     */
    public static function isAllowedToSetPublic()
    {
        global $DIC;
        $is_admin = $DIC->rbac()->review()->isAssigned($DIC->user()->getId(), 2);
        return $is_admin || xvmpConf::getConfig(xvmpConf::F_ALLOW_PUBLIC) &&
            (ilObjViMPAccess::hasWriteAccess() || (ilObjViMPAccess::hasUploadPermission() && xvmpConf::getConfig(xvmpConf::F_ALLOW_PUBLIC_UPLOAD)));
    }


	/**
	 * @param $obj_id
	 * @param $video array|xvmpMedium
	 *
	 * @return bool
	 * @throws xvmpException
	 */
	public static function useEmbeddedPlayer($obj_id, $video) {
		return (!xvmpSettings::find($obj_id)->getLpActive() && xvmpConf::getConfig(xvmpConf::F_EMBED_PLAYER))
			|| xvmpMedium::isVimeoOrYoutube($video);
	}


	/**
	 * @param $obj_id
	 *
	 * @param $video
	 *
	 * @return bool
	 * @throws xvmpException
	 */
	public static function showWatched($obj_id, $video) {
		return !self::useEmbeddedPlayer($obj_id, $video);
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
