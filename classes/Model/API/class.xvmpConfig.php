<?php

/**
 * Class xvmpConfig
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class xvmpConfig extends xvmpObject {

	/**
	 * @param $id
	 *
	 * @return array
	 */
	public static function getObjectAsArray($id) {
		$key = self::class . '-' . $id;
		$existing = xvmpCacheFactory::getInstance()->get($key);
		if ($existing) {
			xvmpCurlLog::getInstance()->write('CACHE: used cached: ' . $key, xvmpCurlLog::DEBUG_LEVEL_2);
			return $existing;
		}

		$array = xvmpRequest::config($id)->getResponseArray()['config'];
		$array['id'] = $id;

		self::cache($key, $array);

		return $array;
	}


	/**
	 * @param       $identifier
	 * @param array $object
	 * @param null  $ttl
	 */
	public static function cache($identifier, $object, $ttl = null) {
		parent::cache($identifier, $object, xvmpConf::getConfig(xvmpConf::F_CACHE_TTL_CONFIG));
	}


	/**
	 * @var string
	 */
	protected $name;
	/**
	 * @var string
	 */
	protected $value;


	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}


	/**
	 * @return string
	 */
	public function getValue() {
		return $this->value;
	}

}