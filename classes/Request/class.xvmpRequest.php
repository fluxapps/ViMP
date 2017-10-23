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


	/**
	 * @return xvmpCurl
	 */
	public static function version() {
		$xvmpCurl = new xvmpCurl(self::VERSION);
		$xvmpCurl->post();
		return $xvmpCurl;
	}

	public static function getUserRoles() {
		$xvmpCurl = new xvmpCurl(self::GET_USER_ROLES);
		$xvmpCurl->post();
		return $xvmpCurl;
	}

	public static function getCategories($parent_id = '', $filterbyname = '', $offset = '', $limit = '', $thumbsize = '', $language = '') {
		$xvmpCurl = new xvmpCurl(self::GET_CATEGORIES);
		$xvmpCurl->post();
		return $xvmpCurl;
	}

	public static function getCategory($categoryid, $thumbsize = '', $language = '') {
		$xvmpCurl = new xvmpCurl(self::GET_CATEGORY);
		$xvmpCurl->addPostField('categoryid', $categoryid);
		$xvmpCurl->post();
		return $xvmpCurl;
	}
}