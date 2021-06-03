<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class xvmpRequest
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xvmpRequest
{

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
    const GET_PICTURE = 'getPicture';
    const GET_VIDEOSOURCES = '../media/ajax';
    const GET_CHAPTERS = '../webplayer/getchapters/key/';
    const GET_MEDIUM_TRANSCODING_PROGRESS = 'getMediumTranscodingProgress';
    const ADD_SUBTITLE = 'addSubtitle';
    const REMOVE_SUBTITLES = 'removeSubtitles';
    const REMOVE_SUBTITLE = 'removeSubtitle';
    const CONFIG = 'config';
    const ADD_MEDIUM_COUNT = 'addMediumCount';

    /**
     * @return xvmpCurl
     * @throws xvmpException
     */
    public static function version()
    {
        $xvmpCurl = new xvmpCurl(self::VERSION);
        $xvmpCurl->setTimeoutMS(10000);
        $xvmpCurl->post();
        return $xvmpCurl;
    }

    /**
     * @return xvmpCurl
     * @throws xvmpException
     */
    public static function getUserRoles()
    {
        $xvmpCurl = new xvmpCurl(self::GET_USER_ROLES);
        $xvmpCurl->post();
        return $xvmpCurl;
    }

    /**
     * @param array $params
     * @return xvmpCurl
     * @throws xvmpException
     */
    public static function getCategories($params = array())
    {
        $xvmpCurl = new xvmpCurl(self::GET_CATEGORIES);
        $xvmpCurl->post();
        return $xvmpCurl;
    }

    /**
     * @param int   $categoryid
     * @param array $params
     * @return xvmpCurl
     * @throws xvmpException
     */
    public static function getCategory($categoryid, $params = array())
    {
        $xvmpCurl = new xvmpCurl(self::GET_CATEGORY);
        $xvmpCurl->addPostField('categoryid', $categoryid);
        $xvmpCurl->post();
        return $xvmpCurl;
    }

    /**
     * possible parameters:
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
     * @param array $params
     * @return xvmpCurl
     * @throws xvmpException
     */
    public static function getMedia($params = array())
    {
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
     * $thumbsize
     * $chapters
     * $responsive
     * $language
     * @param int   $mediumid
     * @param array $params
     * @return xvmpCurl
     * @throws xvmpException
     */
    public static function getMedium($mediumid, $params = array())
    {
        $xvmpCurl = new xvmpCurl(self::GET_MEDIUM);
        $params['mediumid'] = $mediumid;
        $params['token'] = xvmp::getToken();
//		$params['reponsive'] = 'true';
        foreach ($params as $name => $value) {
            $xvmpCurl->addPostField($name, $value);
        }
        $xvmpCurl->post();
        return $xvmpCurl;
    }

    /**
     * @param int $mediumid
     * @return xvmpCurl
     * @throws xvmpException
     */
    public static function addMediumCount(int $mediumid) : xvmpCurl
    {
        $xvmpCurl = new xvmpCurl(self::ADD_MEDIUM_COUNT);
        $xvmpCurl->addPostField('mediumid', $mediumid);
        $xvmpCurl->addPostField('token', xvmp::getToken());
        $xvmpCurl->post();
        return $xvmpCurl;
    }

    /**
     * possible parameters:
     * $title
     * $description
     * $tags
     * $categories
     * $mediapermissions
     * @param int   $mediumid
     * @param array $params
     * @return xvmpCurl
     * @throws xvmpException
     */
    public static function editMedium($mediumid, $params)
    {
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
     * $title
     * $description
     * $tags
     * $categories
     * $mediapermissions
     * @param array $params
     * @return xvmpCurl
     * @throws xvmpException
     * @internal param $mediumid
     */
    public static function uploadMedium($params)
    {
        $xvmpCurl = new xvmpCurl(self::UPLOAD_MEDIUM);
        $params['token'] = xvmp::getToken();
        foreach ($params as $name => $value) {
            $xvmpCurl->addPostField($name, $value);
        }

        $xvmpCurl->post();

        return $xvmpCurl;
    }

    /**
     * @param int $mediumid
     * @return xvmpCurl
     * @throws xvmpException
     */
    public static function deleteMedium($mediumid)
    {
        $xvmpCurl = new xvmpCurl(self::DELETE_MEDIUM);
        $xvmpCurl->addPostField('token', xvmp::getToken());
        $xvmpCurl->addPostField('mediumid', $mediumid);
        $xvmpCurl->post();
        return $xvmpCurl;
    }

    /**
     * @param String $username
     * @param String $password
     * @return xvmpCurl
     * @throws xvmpException
     */
    public static function loginUser($username, $password)
    {
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
     * @param array $params
     * @return xvmpCurl
     * @throws xvmpException
     */
    public static function getUsers($params = array())
    {
        $xvmpCurl = new xvmpCurl(self::GET_USERS);
        foreach ($params as $name => $value) {
            $xvmpCurl->addPostField($name, $value);
        }
        $xvmpCurl->post();

        return $xvmpCurl;
    }

    /**
     * @param int   $userid
     * @param array $params
     * @return xvmpCurl
     * @throws xvmpException
     */
    public static function getUser($userid, $params = array())
    {
        $xvmpCurl = new xvmpCurl(self::GET_USER);

        $params['userid'] = $userid;
        foreach ($params as $name => $value) {
            $xvmpCurl->addPostField($name, $value);
        }
        $xvmpCurl->post();

        return $xvmpCurl;
    }

    /**
     * @param array $params
     * @return xvmpCurl
     * @throws xvmpException
     */
    public static function registerUser($params)
    {
        $xvmpCurl = new xvmpCurl(self::REGISTER_USER);
        foreach ($params as $name => $value) {
            $xvmpCurl->addPostField($name, $value);
        }
        $xvmpCurl->post();

        return $xvmpCurl;
    }

    /**
     * @param int   $user_id
     * @param array $params
     * @return xvmpCurl
     */
    public static function getUserMedia($user_id, $params = array())
    {
        $xvmpCurl = new xvmpCurl(self::GET_USER_MEDIA);
        $xvmpCurl->addPostField('userid', $user_id);
        $xvmpCurl->addPostField('filterbytype', 'video');
        foreach ($params as $name => $value) {
            $xvmpCurl->addPostField($name, $value);
        }
        $xvmpCurl->post();

        return $xvmpCurl;
    }

    /**
     * @param array $params
     * @return xvmpCurl
     * @throws xvmpException
     */
    public static function extendedSearch($params)
    {
        $xvmpCurl = new xvmpCurl(self::EXTENDED_SEARCH);

        if (!isset($params['userid'])) {
            $xvmpCurl->addPostField('token', xvmp::getToken()); // with a token, the field userid is ignored by ViMP
        } else {
            // vimp sets a session id when logging in the api user - this session expires though, that's why we need to reload the token/session here
            xvmpCacheFactory::getInstance()->delete(xvmp::TOKEN);
            xvmp::getToken();
        }
        $xvmpCurl->addPostField('hidden', 'true');
        foreach ($params as $name => $value) {
            $xvmpCurl->addPostField($name, $value);
        }
        $xvmpCurl->post();

        return $xvmpCurl;
    }

    /**
     * @param $key
     * @return xvmpCurl
     * @throws xvmpException
     */
    public static function getPicture($key)
    {
        $xvmpCurl = new xvmpCurl(self::GET_PICTURE);

        $xvmpCurl->addPostField('token', xvmp::getToken());
        $xvmpCurl->addPostField('key', $key);

        $xvmpCurl->post();

        return $xvmpCurl;
    }

    // Non-API request

    /**
     * @param        $key
     * @param String $url
     * @return xvmpCurl
     * @throws xvmpException
     */
    public static function getVideoSources($key, $url)
    {
        $xvmpCurl = new xvmpCurl(self::GET_VIDEOSOURCES);

        $xvmpCurl->addPostField('mediakey', $key);
        if (xvmp::ViMPVersionEquals('4.0.4')) {
            $xvmpCurl->addPostField('action', 'fetchMediaSources');
            $xvmpCurl->addPostField('sign', 'true');
            $xvmpCurl->addPostField('format', '');
        } else {
            $xvmpCurl->addPostField('action', 'embedMedia');
            $xvmpCurl->addPostField('url', $url);
        }

        $xvmpCurl->post();
        return $xvmpCurl;
    }

    // Non-API request

    /**
     * @param $key
     * @return xvmpCurl
     * @throws xvmpException
     */
     public static function getChapters($key) {
        $xvmpCurl = new xvmpCurl(self::GET_CHAPTERS . $key . '?token=' . xvmp::getToken());
        $xvmpCurl->get();
        return $xvmpCurl;
    }

    // Non-API request

    /**
     * @param $url
     * @return xvmpCurl
     * @throws xvmpException
     */
     public static function get($url) {
        $xvmpCurl = new xvmpCurl($url);
        $xvmpCurl->get();
        return $xvmpCurl;
    }

    /**
     * @param String $name
     * @return xvmpCurl
     * @throws xvmpException
     */
    public static function config($name) : xvmpCurl
    {
        $xvmpCurl = new xvmpCurl(self::CONFIG);

        $xvmpCurl->addPostField('name', $name);

        if (xvmp::ViMPVersionGreaterEquals("4.2.9")) {
            $xvmpCurl->addPostField('token', xvmp::getToken());
        }

        $xvmpCurl->post();
        return $xvmpCurl;
    }

    public static function addSubtitle(int $mediumid, array $params) : xvmpCurl
    {
        $xvmpCurl = new xvmpCurl(self::ADD_SUBTITLE);
        $params['token'] = xvmp::getToken();
        $params['mediumid'] = $mediumid;
        foreach ($params as $name => $value) {
            $xvmpCurl->addPostField($name, $value);
        }

        $xvmpCurl->post();

        return $xvmpCurl;
    }

    public static function removeSubtitles(int $mediumid) : xvmpCurl
    {
        $xvmpCurl = new xvmpCurl(self::REMOVE_SUBTITLES);
        $xvmpCurl->addPostField('token', xvmp::getToken());
        $xvmpCurl->addPostField('mediumid', $mediumid);

        $xvmpCurl->post();

        return $xvmpCurl;
    }

    public static function removeSubtitle(int $mediumid, string $lang_code, string $lang_file) : xvmpCurl
    {
        $xvmpCurl = new xvmpCurl(self::REMOVE_SUBTITLE);
        $params['token'] = xvmp::getToken();
        $params['mediumid'] = $mediumid;
        $params['subtitlelanguage'] = $lang_code;
        $params['subtitlefile'] = $lang_file;
        foreach ($params as $name => $value) {
            $xvmpCurl->addPostField($name, $value);
        }

        $xvmpCurl->post();

        return $xvmpCurl;
    }

    /**
     * @param int $mid
     * @param int $decimals
     * @return string
     * @throws xvmpException
     */
    public static function getTranscodingProgress(int $mid, int $decimals = 0) : float
    {
        $xvmpCurl = new xvmpCurl(self::GET_MEDIUM_TRANSCODING_PROGRESS . '?token=' . xvmp::getToken());
        $xvmpCurl->addPostField('mediumid', $mid);
        $xvmpCurl->post();
        $response = $xvmpCurl->getResponseArray();
        if (!isset($response['progress']) || !preg_match('/^\d+(\.\d+)?%$/', $response['progress'])) {
            throw new xvmpException('could not read progress of medium with id ' . $mid .
                ' - ' . (isset($response['progress']) ? "wrong format: {$response['progress']}" : "no response from server"));
        }
        return round((float) $response['progress'], $decimals);
    }
}
