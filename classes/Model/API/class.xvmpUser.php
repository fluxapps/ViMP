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
	 * @param bool      $omit_creation
	 *
	 * @return null|xvmpUser
	 *
	 */
	public static function getVimpUser(ilObjUser $ilObjUser, $omit_creation = false) {
		$mapping = self::getMappedUsername($ilObjUser);

		$response = xvmpRequest::getUsers(array('filterbyname' => $mapping))->getResponseArray();
		$count = $response['users']['count'];
		switch ($count) {
			case 0:
				if ($omit_creation) {
					return null;
				}
				self::createShadowUser($ilObjUser);
				break;
			case 1:
				$xvmpUser = new self();
				$xvmpUser->buildObjectFromArray($response['users']['user']);
				return $xvmpUser;
			default:
				foreach ($response['users']['user'] as $user) {
					if ($user['username'] == $mapping) {
						$xvmpUser = new self();
						$xvmpUser->buildObjectFromArray($user);
						return $xvmpUser;
					}
				}
				if ($omit_creation) {
					return null;
				}
				self::createShadowUser($ilObjUser);
		}
	}


	/**
	 * @param ilObjUser $ilObjUser
	 * @return self
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
		$mapping = str_replace('{LOGIN}', $ilObjUser->getLogin(), $mapping);
		$mapping = str_replace('{CLIENT_ID}', CLIENT_ID, $mapping);

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
}