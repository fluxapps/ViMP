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
	 * @internal param bool $omit_creation
	 *
	 */
	public static function getVimpUser(ilObjUser $ilObjUser) {
		$key = self::class . '-' . $ilObjUser->getId();
		$existing = xvmpCacheFactory::getInstance()->get($key);

		if ($existing) {
			xvmpCurlLog::getInstance()->write('CACHE: used cached: ' . $key, xvmpCurlLog::DEBUG_LEVEL_2);
			return $existing;
		}

		xvmpCurlLog::getInstance()->write('CACHE: cache not used: ' . $key, xvmpCurlLog::DEBUG_LEVEL_2);

		switch (xvmpConf::getConfig(xvmpConf::F_MAPPING_PRIORITY)) {
			case xvmpConf::PRIORITIZE_EMAIL:
				$xvmpUser = self::getVimpUserByEmail($ilObjUser);
				if (!$xvmpUser) {
					$xvmpUser = self::getVimpUserByMapping($ilObjUser);
				}
				break;
			case xvmpConf::PRIORITIZE_MAPPING:
				$xvmpUser = self::getVimpUserByMapping($ilObjUser);
				if (!$xvmpUser) {
					$xvmpUser = self::getVimpUserByEmail($ilObjUser);
				}
				break;
		}

		if ($xvmpUser) {
			self::cache($key, $xvmpUser, xvmpConf::getConfig(xvmpConf::F_CACHE_TTL_USERS));
		}

		return $xvmpUser;
	}

	/**
	 * @param ilObjUser $ilObjUser
	 *
	 * @return bool|xvmpUser
	 * @internal param bool $omit_creation
	 *
	 */
	public static function getVimpUserByMapping(ilObjUser $ilObjUser) {
		$mapping = self::getMappedUsername($ilObjUser);

		$response = xvmpRequest::getUsers(array('filterbyname' => $mapping))->getResponseArray();
		$count = $response['users']['count'];
		switch ($count) {
			case 0:
				return false;
			case 1:
				$xvmpUser = self::getVimpUserById($response['users']['user']['uid']);
				return $xvmpUser;
			default:
				foreach ($response['users']['user'] as $user) {
					if ($user['username'] == $mapping) {
						$xvmpUser = self::getVimpUserById($user['uid']);
						return $xvmpUser;
					}
				}
				return false;
		}
	}

	/**
	 * @param ilObjUser $ilObjUser
	 *
	 * @return bool|xvmpUser
	 */
	public static function getVimpUserByEmail(ilObjUser $ilObjUser) {
		$response = xvmpRequest::extendedSearch(array(
			'token' => xvmp::getToken(),
			'searchrange' => 'user',
			'title' => $ilObjUser->getEmail(),
		))->getResponseArray();

		$users = $response['users'];
		if (!$users) {
			return false;
		}

		if ($uid = $users['user']['uid']) {

			$xvmpUser = self::getVimpUserById($uid);
			return $xvmpUser;
		}

		foreach ($users['user'] as $user) {
			if ($user['email'] == $ilObjUser->getEmail()) {
				$xvmpUser = self::getVimpUserById($user['uid']);
				return $xvmpUser;
			}
		}

		return false;
	}

	public static function getVimpUserById($uid) {
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
			$uid = self::createShadowUser($ilObjUser);
			$xvmpUser = self::getVimpUserById($uid);
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
	 * @return integer $user_id
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

		$response = xvmpRequest::registerUser($params);
		return $response->getResponseArray()['user']['uid'];
	}



	/**
	 * @param ilObjUser $ilObjUser
	 *
	 * @return mixed
	 */
	protected static function getMappedUsername(ilObjUser $ilObjUser) {
		static $mapping;
		if (isset($mapping[$ilObjUser->getId()])) {
			return $mapping[$ilObjUser->getId()];
		}

		$mapping = is_array($mapping) ? $mapping : [];

		if ($ilObjUser->getAuthMode(true) != AUTH_LOCAL) {
			$mapping[$ilObjUser->getId()] = xvmpConf::getConfig(xvmpConf::F_USER_MAPPING_EXTERNAL);
		} else {
			$mapping[$ilObjUser->getId()] = xvmpConf::getConfig(xvmpConf::F_USER_MAPPING_LOCAL);
		}

		$mapping[$ilObjUser->getId()] = str_replace('{EXT_ID}', $ilObjUser->getExternalAccount(), $mapping[$ilObjUser->getId()]);
		$mapping[$ilObjUser->getId()] = str_replace('{UNIFR}', (substr($ilObjUser->getExternalAccount(),0,-13)), $mapping[$ilObjUser->getId()]); //jh
		$mapping[$ilObjUser->getId()] = str_replace('{LOGIN}', $ilObjUser->getLogin(), $mapping[$ilObjUser->getId()]);
		$mapping[$ilObjUser->getId()] = str_replace('{EMAIL}', $ilObjUser->getEmail(), $mapping[$ilObjUser->getId()]);
		$mapping[$ilObjUser->getId()] = str_replace('{CLIENT_ID}', CLIENT_ID, $mapping[$ilObjUser->getId()]);

		return $mapping[$ilObjUser->getId()];
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
