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
		if (!isset(self::$cache[$class_name][$id])) {
			if (!isset(self::$cache[$class_name])) {
				self::$cache[$class_name] = array();
			}
			/** @var xvmpObject $xvmpObject */
			$xvmpObject = new $class_name();
			$xvmpObject->buildObjectFromArray($class_name::getObjectAsArray($id));
			self::$cache[$class_name][$id] = $xvmpObject;
		}
		return self::$cache[$class_name][$id];
	}


	/**
	 * returns array with all objects
	 *
	 * @return self[]
	 */
	public static function getAll() {
		$class_name = get_called_class();
		if (!self::$cache_initialized[$class_name]) {
			self::buildAllFromArray($class_name::getAllAsArray());
		}
		return self::$cache[$class_name];
	}

	/**
	 * @param array $filter
	 *
	 * @return array
	 */
	public static function getFiltered(array $filter) {
		$class_name = get_called_class();
		if (!self::$cache_initialized[$class_name]) {
			self::buildFromArray($class_name::getFilteredAsArray($filter));
		}
		return self::$cache[$class_name];
	}

	/**
	 * build object from data array
	 *
	 * @param array $array
	 */
	public function buildObjectFromArray(array $array) {
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
		self::buildFromArray($array);
		$class_name = get_called_class();
		self::$cache_initialized[$class_name] = true;
	}

	/**
	 * build all objects from data array
	 *
	 * @param array $array
	 */
	public static function buildFromArray(array $array) {
		$class_name = get_called_class();
		self::$cache[$class_name] = array();
		foreach ($array as $item) {
			/** @var xvmpObject $xvmpObject */
			$xvmpObject = new $class_name();
			$xvmpObject->buildObjectFromArray($item);
			$key = $class_name . '-' . $xvmpObject->getId();
			self::$cache[$class_name][$key] = $xvmpObject;
		}
	}

	/**
	 * @return array
	 */
	public function __toArray() {
		$data = $this->__toStdClass();
		$array = (array)$data;

		return $array;
	}

	/**
	 * @return stdClass
	 */
	public function __toStdClass() {
		$r = new ReflectionClass($this);
		$stdClass = new stdClass();
		foreach ($r->getProperties() as $name) {
			$key = utf8_encode($name->getName());

			if ($key == 'cache') {
				continue;
			}

			$value = $this->sleep($key, $this->{$key});
			switch (true) {
				case ($value instanceof xoctObject):
					$stdClass->{$key} = $value->__toStdClass();
					break;
				case (is_array($value)):
					$a = array();
					foreach ($value as $k => $v) {
						if (is_array($v)) {
							$a[$k] = array();
							foreach ($v as $sk => $sv) {
								$a[$k][$sk] = $sv;
							}
						} else {
							$a[$k] = self::convertToUtf8($v);
						}
					}
					$stdClass->{$key} = $a;
					break;
				case (is_bool($value)):
					$stdClass->{$key} = $value;
					break;
				case ($value instanceof DateTime):
					$stdClass->{$key} = $value->getTimestamp();
					break;
				case ($value instanceof stdClass):
					$a = array();
					$value = (array)$value;
					foreach ($value as $k => $v) {
						if ($v instanceof xoctObject) {
							$a[$k] = $v->__toStdClass();
						} else {
							$a[$k] = self::convertToUtf8($v);
						}
					}
					$stdClass->{$key} = $a;
					break;
				default:
					$stdClass->{$key} = self::convertToUtf8($value);
					break;
			}
		}

		return $stdClass;
	}


	/**
	 * @param       $identifier
	 * @param array $object
	 * @param null  $ttl
	 */
	public static function cache($identifier, $object, $ttl = null) {
//		self::$cache[$key] = $object;
		xvmpCurlLog::getInstance()->write('CACHE: added to cache: ' . $identifier, xvmpCurlLog::DEBUG_LEVEL_1);
		xvmpCacheFactory::getInstance()->set($identifier, $object, (int) $ttl);
	}

	/**
	 * @param $string
	 *
	 * @return string
	 */
	public static function convertToUtf8($string) {
		if (is_object($string) || ilStr::isUtf8($string)) {
			return $string;
		}

		return utf8_encode($string);
	}

	/**
	 * @param $fieldname
	 * @param $value
	 *
	 * @return mixed
	 */
	protected function sleep($fieldname, $value) {
		return $value;
	}


	/**
	 * @param array $filter
	 *
	 * @return array
	 */
	public static function getFilteredAsArray(array $filter) {
		return array();
	}


	/**
	 * fetch all data from api and return as an array
	 *
	 * @return array()
	 */
	public static function getAllAsArray() {
		return array();
	}


	/**
	 * fetch single object from api and return as an array
	 *
	 * @param $id
	 *
	 * @return array
	 */
	public static function getObjectAsArray($id) {
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


	/**
	 * used for custom fields
	 *
	 * @param $field_name
	 *
	 * @return mixed
	 */
	public function getField($field_name) {
		return $field_name ? $this->$field_name : '';
	}
}