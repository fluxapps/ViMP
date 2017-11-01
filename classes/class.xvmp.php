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


	public static function getToken() {
//		if (!$token = xvmpConf::getConfig(xvmpConf::F_TOKEN)) {
			$token = self::loadToken();
//		}
		return $token;
	}

	public static function loadToken() {
		$response = xvmpRequest::loginUser(xvmpConf::getConfig(xvmpConf::F_API_USER),xvmpConf::getConfig(xvmpConf::F_API_PASSWORD))->getResponseArray();
//		xvmpConf::set(xvmpConf::F_TOKEN, $response['token']);
		return $response['token'];
	}

	public static function resetToken() {
		xvmpConf::set(xvmpConf::F_TOKEN, '');
	}
}