<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class xvmpUserRoles
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xvmpUserRoles extends xvmpObject {

	/**
	 * @inheritdoc
	 */
	public static function find($id) {
		$key = self::class . '-' . $id;
		if (!isset(xvmpObject::$cache[$key])) {
			xvmpObject::buildAllFromArray(self::fetchAll());
		}
		return xvmpObject::$cache[$id];
	}

	/**
	 * @inheritdoc
	 */
	protected static function fetchAll() {
		$response = xvmpRequest::getUserRoles()->getResponseArray();
		return $response['roles']['role'];
	}

	/**
	 * @var int
	 */
	protected $id;
	/**
	 * @var String
	 */
	protected $status;
	/**
	 * @var String
	 */
	protected $name;
	/**
	 * @var String
	 */
	protected $description;
	/**
	 * @var bool
	 */
	protected $visible;
	/**
	 * @var bool
	 */
	protected $default;
	/**
	 * @var String
	 */
	protected $created_at;
	/**
	 * @var String
	 */
	protected $updated_at;


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
	public function getName() {
		return $this->name;
	}


	/**
	 * @param String $name
	 */
	public function setName($name) {
		$this->name = $name;
	}


	/**
	 * @return String
	 */
	public function getDescription() {
		return $this->description;
	}


	/**
	 * @param String $description
	 */
	public function setDescription($description) {
		$this->description = $description;
	}


	/**
	 * @return bool
	 */
	public function isVisible() {
		return $this->visible;
	}


	/**
	 * @param bool $visible
	 */
	public function setVisible($visible) {
		$this->visible = $visible;
	}


	/**
	 * @return bool
	 */
	public function isDefault() {
		return $this->default;
	}


	/**
	 * @param bool $default
	 */
	public function setDefault($default) {
		$this->default = $default;
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

}