<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class xvmpUser
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xvmpUser extends xvmpObject {

	/**
	 * @param ilObjUser $ilObjUser
	 *
	 * @return bool|xvmpUser
	 */
	public static function getVimpUser(ilObjUser $ilObjUser) {
		$key = self::class . '-' . $ilObjUser->getEmail();
		$existing = xvmpCacheFactory::getInstance()->get($key);

		if ($existing) {
			xvmpCurlLog::getInstance()->write('CACHE: used cached: ' . $key, xvmpCurlLog::DEBUG_LEVEL_2);
			return $existing;
		}

		xvmpCurlLog::getInstance()->write('CACHE: cache not used: ' . $key, xvmpCurlLog::DEBUG_LEVEL_2);


		$response = xvmpRequest::extendedSearch(array(
			'searchrange' => 'user',
			'title' => $ilObjUser->getEmail(),
		))->getResponseArray();

		$users = $response['users'];
		if (!$users) {
			return false;
		}

		if ($uid = $users['user']['uid']) {

			$xvmpUser = self::getVimpUserWithId($uid);
			self::cache($key, $xvmpUser, xvmpConf::getConfig(xvmpConf::F_CACHE_TTL_USERS));
			return $xvmpUser;
		}

		foreach ($users['user'] as $user) {
			if ($user['email'] == $ilObjUser->getEmail()) {
				$xvmpUser = self::getVimpUserWithId($user['uid']);
				self::cache($key, $xvmpUser, xvmpConf::getConfig(xvmpConf::F_CACHE_TTL_USERS));
				return $xvmpUser;
			}
		}

		return false;
	}

	public static function getVimpUserWithId($uid) {
		$response = xvmpRequest::getUser($uid, array(
			'roles' => 'true'
		))->getResponseArray();
		$xvmpUser = new self();
		$xvmpUser->buildObjectFromArray($response['user']);
		return $xvmpUser;
	}


	/**
	 * @param ilObjUser $ilObjUser
	 *
	 * @return xvmpUser
	 */
	public static function getOrCreateVimpUser(ilObjUser $ilObjUser) {
		$xvmpUser = self::getVimpUser($ilObjUser);
		if (!$xvmpUser) {
			self::createShadowUser($ilObjUser);
			$xvmpUser = self::getVimpUser($ilObjUser);
		}
		return $xvmpUser;
	}


	public function buildObjectFromArray(array $array) {
		if (isset($array['roles']['role']['id'])) {
			$array['roles'] = array($array['roles']['role']['id'] => $array['roles']['role']['name']);
		} else {
			foreach ($array['roles']['role'] as $key => $value) {
				$array['roles'][$value['id']] = $value['name'];
			}
			unset($array['roles']['role']);
		}
		foreach ($array as $key => $value) {
			$this->{$key} = $value;
		}
	}


	/**
	 * @param ilObjUser $ilObjUser
	 *
	 */
	public static function createShadowUser(ilObjUser $ilObjUser) {
		$params = array(
			'username' => self::getMappedUsername($ilObjUser),
			'email' => $ilObjUser->getEmail(),
			'password' => substr(ilUtil::randomhash(),0, 10),
		);
		if ($firstname = $ilObjUser->getFirstname()) {
			$params['firstname'] = $firstname;
		}
		if ($lastname = $ilObjUser->getLastname()) {
			$params['lastname'] = $lastname;
		}

		xvmpRequest::registerUser($params);

	}



	/**
	 * @param ilObjUser $ilObjUser
	 *
	 * @return mixed
	 */
	protected static function getMappedUsername(ilObjUser $ilObjUser) {
		static $mapping;
		if ($mapping) {
			return $mapping;
		}

		if ($ilObjUser->getAuthMode(true) != AUTH_LOCAL) {
			$mapping = xvmpConf::getConfig(xvmpConf::F_USER_MAPPING_EXTERNAL);
		} else {
			$mapping = xvmpConf::getConfig(xvmpConf::F_USER_MAPPING_LOCAL);
		}

		$mapping = str_replace('{EXT_ID}', $ilObjUser->getExternalAccount(), $mapping);
		$mapping = str_replace('{UNIFR}', str_replace($ilObjUser->getExternalAccount()),0,-12), $mapping); //jh
		$mapping = str_replace('{LOGIN}', $ilObjUser->getLogin(), $mapping);
		$mapping = str_replace('{EMAIL}', $ilObjUser->getEmail(), $mapping);
		$mapping = str_replace('{CLIENT_ID}', CLIENT_ID, $mapping);


//		preg_match('/[.]*/')

		return $mapping;
	}



	/**
	 * @var int
	 */
	protected $uid;
	/**
	 * @var String
	 */
	protected $status;
	/**
	 * @var String
	 */
	protected $username;
	/**
	 * @var String
	 */
	protected $email;
	/**
	 * @var String
	 */
	protected $avatar;
	/**
	 * @var String
	 */
	protected $cover;
	/**
	 * @var String
	 */
	protected $last_login_at;
	/**
	 * @var String
	 */
	protected $last_access_at;
	/**
	 * @var String
	 */
	protected $created_at;
	/**
	 * @var String
	 */
	protected $updated_at;
	/**
	 * @var array
	 */
	protected $roles;


	/**
	 * @return int
	 */
	public function getUid() {
		return $this->uid;
	}


	/**
	 * @param int $uid
	 */
	public function setUid($uid) {
		$this->uid = $uid;
	}


	/**
	 * @return String
	 */
	public function getStatus() {
		return $this->status;
	}


	/**
	 * @param String $status
	 */
	public function setStatus($status) {
		$this->status = $status;
	}


	/**
	 * @return String
	 */
	public function getUsername() {
		return $this->username;
	}


	/**
	 * @param String $username
	 */
	public function setUsername($username) {
		$this->username = $username;
	}


	/**
	 * @return String
	 */
	public function getEmail() {
		return $this->email;
	}


	/**
	 * @param String $email
	 */
	public function setEmail($email) {
		$this->email = $email;
	}


	/**
	 * @return String
	 */
	public function getAvatar() {
		return $this->avatar;
	}


	/**
	 * @param String $avatar
	 */
	public function setAvatar($avatar) {
		$this->avatar = $avatar;
	}


	/**
	 * @return String
	 */
	public function getCover() {
		return $this->cover;
	}


	/**
	 * @param String $cover
	 */
	public function setCover($cover) {
		$this->cover = $cover;
	}


	/**
	 * @return String
	 */
	public function getLastLoginAt() {
		return $this->last_login_at;
	}


	/**
	 * @param String $last_login_at
	 */
	public function setLastLoginAt($last_login_at) {
		$this->last_login_at = $last_login_at;
	}


	/**
	 * @return String
	 */
	public function getLastAccessAt() {
		return $this->last_access_at;
	}


	/**
	 * @param String $last_access_at
	 */
	public function setLastAccessAt($last_access_at) {
		$this->last_access_at = $last_access_at;
	}


	/**
	 * @return String
	 */
	public function getCreatedAt() {
		return $this->created_at;
	}


	/**
	 * @param String $created_at
	 */
	public function setCreatedAt($created_at) {
		$this->created_at = $created_at;
	}


	/**
	 * @return String
	 */
	public function getUpdatedAt() {
		return $this->updated_at;
	}


	/**
	 * @param String $updated_at
	 */
	public function setUpdatedAt($updated_at) {
		$this->updated_at = $updated_at;
	}


	/**
	 * @return int
	 */
	public function getId() {
		return $this->getUid();
	}


	/**
	 * @param int $id
	 */
	public function setId($id) {
		$this->setUid($id);
	}


	/**
	 * @return array
	 */
	public function getRoles() {
		return $this->roles;
	}


	/**
	 * @param array $roles
	 */
	public function setRoles($roles) {
		$this->roles = $roles;
	}
}
