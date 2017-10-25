<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class xvmp
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xvmp {

	public static function getToken() {
		if (!$token = xvmpConf::getConfig(xvmpConf::F_TOKEN)) {
			$token = self::loadToken();
		}
		return $token;
	}

	public static function loadToken() {
		$response = xvmpRequest::loginUser(xvmpConf::getConfig(xvmpConf::F_API_USER),xvmpConf::getConfig(xvmpConf::F_API_PASSWORD))->getResponseArray();
		xvmpConf::set(xvmpConf::F_TOKEN, $response['token']);
		return $response['token'];
	}

	public static function resetToken() {
		xvmpConf::set(xvmpConf::F_TOKEN, '');
	}
}