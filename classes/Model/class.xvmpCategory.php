<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class xvmpCategory
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xvmpCategory extends xvmpObject {

	protected static function fetchObject($id) {
		$response = xvmpRequest::getCategory($id)->getResponseArray();
		return $response['category'];
	}

	protected static function fetchAll() {
		$response = xvmpRequest::getCategories()->getResponseArray();
		return $response['categories']['category'];
	}


	/**
	 * @var int
	 */
	protected $cid;
	/**
	 * @var int
	 */
	protected $pid;
	/**
	 * @var String
	 */
	protected $culture;
	/**
	 * @var String
	 */
	protected $name;
	/**
	 * @var String
	 */
	protected $description;
	/**
	 * @var String
	 */
	protected $categorytype;
	/**
	 * @var String
	 */
	protected $status;
	/**
	 * @var String
	 */
	protected $picture;
	/**
	 * @var int
	 */
	protected $weight;
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
	public function getCid() {
		return $this->cid;
	}


	/**
	 * @param int $cid
	 */
	public function setCid($cid) {
		$this->cid = $cid;
	}


	/**
	 * @return int
	 */
	public function getPid() {
		return $this->pid;
	}


	/**
	 * @param int $pid
	 */
	public function setPid($pid) {
		$this->pid = $pid;
	}


	/**
	 * @return String
	 */
	public function getCulture() {
		return $this->culture;
	}


	/**
	 * @param String $culture
	 */
	public function setCulture($culture) {
		$this->culture = $culture;
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
	 * @return String
	 */
	public function getCategorytype() {
		return $this->categorytype;
	}


	/**
	 * @param String $categorytype
	 */
	public function setCategorytype($categorytype) {
		$this->categorytype = $categorytype;
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
	public function getPicture() {
		return $this->picture;
	}


	/**
	 * @param String $picture
	 */
	public function setPicture($picture) {
		$this->picture = $picture;
	}


	/**
	 * @return int
	 */
	public function getWeight() {
		return $this->weight;
	}


	/**
	 * @param int $weight
	 */
	public function setWeight($weight) {
		$this->weight = $weight;
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