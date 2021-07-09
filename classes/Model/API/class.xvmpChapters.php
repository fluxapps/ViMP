<?php

/**
 * Class xvmpChapters
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class xvmpChapters extends xvmpObject {

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

		try {
            $array = xvmpRequest::getChapters($id)->getResponseArray();
        } catch (xvmpException $e) {
		    xvmpCurlLog::getInstance()->writeWarning('chapters could not be loaded');
		    xvmpCurlLog::getInstance()->writeWarning($e->getCode() . ': ' . $e->getMessage());
		    $array = [];
        }

		self::cache($key, $array);

		return $array;
	}


	/**
	 * @param       $identifier
	 * @param array $object
	 * @param null  $ttl
	 */
	public static function cache($identifier, $object, $ttl = null) {
		parent::cache($identifier, $object, xvmpConf::getConfig(xvmpConf::F_CACHE_TTL_VIDEOS));
	}


	/**
	 * @var string
	 */
	protected $lang;
	/**
	 * @var array
	 */
	protected $chapters;


	/**
	 * @return string
	 */
	public function getLang() {
		return $this->lang;
	}


	/**
	 * @return array
	 */
	public function getChapters() {
		return $this->chapters;
	}

}