<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class xvmpRequest
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xvmpRequest {

	// API ENDPOINTS
	const VERSION = 'version';
	const GET_USER_ROLES = 'getUserRoles';
	const GET_CATEGORIES = 'getCategories';
	const GET_CATEGORY = 'getCategory';
	const GET_MEDIA = 'getMedia';
	const GET_MEDIUM = 'getMedium';
	const EDIT_MEDIUM = 'editMedium';
	const DELETE_MEDIUM = 'deleteMedium';
	const UPLOAD_MEDIUM = 'uploadMedium';
	const LOGIN_USER = 'loginUser';
	const GET_USERS = 'getUsers';
	const GET_USER = 'getUser';
	const REGISTER_USER = 'registerUser';
	const GET_USER_MEDIA = 'getUserMedia';
	const EXTENDED_SEARCH = 'extendedSearch';


	/**
	 * @return xvmpCurl
	 */
	public static function version() {
		$xvmpCurl = new xvmpCurl(self::VERSION);
		$xvmpCurl->setTimeoutMS(10000);
		$xvmpCurl->post();
		return $xvmpCurl;
	}

	public static function getUserRoles() {
		$xvmpCurl = new xvmpCurl(self::GET_USER_ROLES);
		$xvmpCurl->post();
		return $xvmpCurl;
	}

	public static function getCategories($params = array()) {
		$xvmpCurl = new xvmpCurl(self::GET_CATEGORIES);
		$xvmpCurl->post();
		return $xvmpCurl;
	}

	public static function getCategory($categoryid, $params = array()) {
		$xvmpCurl = new xvmpCurl(self::GET_CATEGORY);
		$xvmpCurl->addPostField('categoryid', $categoryid);
		$xvmpCurl->post();
		return $xvmpCurl;
	}


	/**
	 * possible parameters:
	 *
	 * $filterbyname
	 * $filterbytype
	 * $filterbycategory
	 * $filterbyfilter
	 * $offset
	 * $limit
	 * $thumbsize
	 * $hidden
	 * $chapters
	 * $responsive
	 * $language
	 *
	 * @param array $params
	 *
	 * @return xvmpCurl
	 */
	public static function getMedia($params = array()) {
		$xvmpCurl = new xvmpCurl(self::GET_MEDIA);
		$params['filterbytype'] = 'video';         // only fetch videos
		foreach ($params as $name => $value) {
			$xvmpCurl->addPostField($name, $value);
		}
		$xvmpCurl->post();
		return $xvmpCurl;
	}


	/**
	 * possible parameters:
	 *
	 * $thumbsize
	 * $chapters
	 * $responsive
	 * $language
	 *
	 * @param       $mediumid
	 * @param array $params
	 *
	 * @return xvmpCurl
	 */
	public static function getMedium($mediumid, $params = array()) {
		$xvmpCurl = new xvmpCurl(self::GET_MEDIUM);
		$params['mediumid'] = $mediumid;
		$params['token'] = xvmp::getToken();
		foreach ($params as $name => $value) {
			$xvmpCurl->addPostField($name, $value);
		}
		$xvmpCurl->post();
		return $xvmpCurl;
	}

	/**
	 * possible parameters:
	 *
	 * $title
	 * $description
	 * $tags
	 * $categories
	 * $mediapermissions
	 *
	 * @param       $mediumid
	 * @param array $params
	 *
	 * @return xvmpCurl
	 */
	public static function editMedium($mediumid, $params) {
		$xvmpCurl = new xvmpCurl(self::EDIT_MEDIUM);
		$params['mediumid'] = $mediumid;
		$params['token'] = xvmp::getToken();
		foreach ($params as $name => $value) {
			$xvmpCurl->addPostField($name, $value);
		}
		$xvmpCurl->post();
		return $xvmpCurl;
	}


	/**
	 * possible parameters:
	 *
	 * $title
	 * $description
	 * $tags
	 * $categories
	 * $mediapermissions
	 *
	 * @param array $params
	 *
	 * @return xvmpCurl
	 * @internal param $mediumid
	 */
	public static function uploadMedium($params) {
		$xvmpCurl = new xvmpCurl(self::UPLOAD_MEDIUM);
		$params['token'] = xvmp::getToken();
		foreach ($params as $name => $value) {
			$xvmpCurl->addPostField($name, $value);
		}

		$xvmpCurl->post();

		return $xvmpCurl;
	}


	/**
	 * @param $mediumid
	 *
	 * @return xvmpCurl
	 */
	public static function deleteMedium($mediumid) {
		$xvmpCurl = new xvmpCurl(self::DELETE_MEDIUM);
		$xvmpCurl->addPostField('token', xvmp::getToken());
		$xvmpCurl->addPostField('mediumid', $mediumid);
		$xvmpCurl->post();
		return $xvmpCurl;
	}

	/**
	 * @param $username
	 * @param $password
	 *
	 * @return xvmpCurl
	 */
	public static function loginUser($username, $password) {
		$xvmpCurl = new xvmpCurl(self::LOGIN_USER);
		$xvmpCurl->addPostField('username', $username);
		$xvmpCurl->addPostField('password', $password);
		$xvmpCurl->post();
		return $xvmpCurl;
	}


	/**
	 * possible parameters:
	 * filterbyname
	 * offset
	 * limit
	 * thumbsize
	 *      width x height (ex. 210x108)
	 * format
	 *      one of "xml" or "json"
	 *
	 * @param array $params
	 *
	 * @return xvmpCurl
	 */
	public static function getUsers($params = array()) {
		$xvmpCurl = new xvmpCurl(self::GET_USERS);
		foreach ($params as $name => $value) {
			$xvmpCurl->addPostField($name, $value);
		}
		$xvmpCurl->post();

		return $xvmpCurl;
	}


	/**
	 * @param       $userid
	 * @param array $params
	 *
	 * @return xvmpCurl
	 */
	public static function getUser($userid, $params = array()) {
		$xvmpCurl = new xvmpCurl(self::GET_USER);

		$params['userid'] = $userid;
		foreach ($params as $name => $value) {
			$xvmpCurl->addPostField($name, $value);
		}
		$xvmpCurl->post();

		return $xvmpCurl;
	}


	/**
	 * @param $params
	 *
	 * @return xvmpCurl
	 */
	public static function registerUser($params) {
		$xvmpCurl = new xvmpCurl(self::REGISTER_USER);
		foreach ($params as $name => $value) {
			$xvmpCurl->addPostField($name, $value);
		}
		$xvmpCurl->post();

		return $xvmpCurl;
	}


	/**
	 * @param       $user_id
	 * @param array $params
	 *
	 * @return xvmpCurl
	 */
	public static function getUserMedia($user_id, $params = array()) {
		$xvmpCurl = new xvmpCurl(self::GET_USER_MEDIA);
		$xvmpCurl->addPostField('userid', $user_id);
		foreach ($params as $name => $value) {
			$xvmpCurl->addPostField($name, $value);
		}
		$xvmpCurl->post();

		return $xvmpCurl;
	}


	/**
	 * @param $params
	 *
	 * @return xvmpCurl
	 */
	public static function extendedSearch($params) {
		$xvmpCurl = new xvmpCurl(self::EXTENDED_SEARCH);
		if (!isset($params['searchrange'])) {
			$xvmpCurl->addPostField('searchrange', 'video');
		}
		$xvmpCurl->addPostField('token', xvmp::getToken());
		$xvmpCurl->addPostField('hidden', 'true');
		foreach ($params as $name => $value) {
			$xvmpCurl->addPostField($name, $value);
		}
		$xvmpCurl->post();

		return $xvmpCurl;
	}

}