<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class xvmpObject
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xvmpObject {

	/**
	 * @var int
	 */
	protected $id;

	protected static $cache_initialized = array();
	protected static $cache = array();


	/**
	 * returns a single object via id
	 *
	 * @param $id
	 *
	 * @return static
	 */
	public static function find($id) {
		$class_name = get_called_class();
		$key = $class_name . '-' . $id;
		if (!isset(self::$cache[$key])) {
			/** @var xvmpObject $xvmpObject */
			$xvmpObject = new $class_name();
			$xvmpObject->buildFromArray($class_name::fetchObject($id));
		}
		return self::$cache[$id];
	}


	/**
	 * returns array with all objects
	 *
	 * @return self[]
	 */
	public static function getAll() {
		$class_name = get_called_class();
		if (!self::$cache_initialized[$class_name]) {
			self::buildAllFromArray($class_name::fetchAll());
		}
		return self::$cache;
	}


	/**
	 * build object from data array
	 *
	 * @param array $array
	 */
	public function buildFromArray(array $array) {
		foreach ($array as $key => $value) {
			$this->{$key} = $value;
		}
	}


	/**
	 * build all objects from data array
	 *
	 * @param array $array
	 */
	public static function buildAllFromArray(array $array) {
		$class_name = get_called_class();
		foreach ($array as $item) {
			/** @var xvmpObject $xvmpObject */
			$xvmpObject = new $class_name();
			$xvmpObject->buildFromArray($item);
			$key = $class_name . '-' . $xvmpObject->getId();
			self::$cache[$key] = $xvmpObject;
		}
		self::$cache_initialized[$class_name] = true;
	}


	/**
	 * fetch all data from api and return as an array
	 *
	 * @return array()
	 */
	protected static function fetchAll() {
		return array();
	}


	/**
	 * fetch single object from api and return as an array
	 *
	 * @param $id
	 *
	 * @return array
	 */
	protected static function fetchObject($id) {
		return array();
	}


	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}


	/**
	 * @param int $id
	 */
	public function setId($id) {
		$this->id = $id;
	}
}