<?php

/**
 * Class xvmpCache
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 * @version 1.0.0
 */
class xvmpCache extends ilGlobalCache {

	const COMP_PREFIX = 'xvmp';
	/**
	 * @var bool
	 */
	protected static $override_active = false;
	protected static array $active_components = array(
		self::COMP_PREFIX,
	);


	/**
	 * @return xvmpCache
	 */
	public static function getInstance($component): ilGlobalCache
	{
		$service_type = self::getSettings()->getService();
		$xvmpCache = new self($service_type);

		$xvmpCache->setActive(false);
		self::setOverrideActive(false);

		return $xvmpCache;
	}


//	/**
//	 * @param null $component
//	 *
//	 * @return ilGlobalCache|void
//	 * @throws ilException
//	 */
//	public static function getInstance($component) {
//		throw new ilException('xvmpCache::getInstance() should not be called. Please call xvmpCache::getCacheInstance() instead.');
//	}

	public function init() {
		$this->initCachingService();
		$this->setActive(true);
		self::setOverrideActive(true);
	}

	protected function initCachingService(): void
	{
		/**
		 * @var $ilGlobalCacheService ilGlobalCacheService
		 */
		if (!$this->getComponent()) {
			$this->setComponent('ViMP');
		}

		if($this->isVimpCacheEnabled())
		{
			$serviceName = self::lookupServiceClassName($this->getServiceType());
			$ilGlobalCacheService = new $serviceName(self::$unique_service_id, $this->getComponent());
			$ilGlobalCacheService->setServiceType($this->getServiceType());
		}
		else
		{
			$serviceName = self::lookupServiceClassName(self::TYPE_STATIC);
			$ilGlobalCacheService = new $serviceName(self::$unique_service_id, $this->getComponent());
			$ilGlobalCacheService->setServiceType(self::TYPE_STATIC);
		}

		$this->global_cache = $ilGlobalCacheService;
		$this->setActive(in_array($this->getComponent(), self::getActiveComponents()));
	}

	/**
	 * Checks if live voting is able to use the global cache.
	 *
	 * @return bool
	 */
	private function isVimpCacheEnabled()
	{
		return true;
		try
		{
			return (int)xvmpConf::getConfig(xvmpConf::F_ACTIVATE_CACHE);
		}
		catch (Exception $exceptione) //catch exception while dbupdate is running. (xlvoConf is not ready at that time).
		{
			return false;
		}
	}

	/**
	 * @param $service_type
	 *
	 * @return string
	 */
	public static function lookupServiceClassName($service_type): string
	{
		switch ($service_type) {
			case self::TYPE_APC:
				return 'ilApc';
				break;
			case self::TYPE_MEMCACHED:
				return 'ilMemcache';
				break;
			case self::TYPE_STATIC:
				return 'ilStaticCache';
				break;
			default:
				return 'ilStaticCache';
				break;
		}
	}


	/**
	 * @return array
	 */
	public static function getActiveComponents(): array
	{
		return self::$active_components;
	}


	/**
	 * @param bool $complete
	 *
	 * @return bool
	 * @throws RuntimeException
	 */
	public function flush($complete = false): bool
	{
		if (!$this->global_cache instanceof ilGlobalCacheService || !$this->isActive()) {
			return false;
		}

		return parent::flush(true);
	}


	/**
	 * @param $key
	 *
	 * @throws RuntimeException
	 * @return bool
	 */
	public function delete($key): bool
	{
		if (!$this->global_cache instanceof ilGlobalCacheService || !$this->isActive()) {
			return false;
		}

		return parent::delete($key);
	}


	/**
	 * @return bool
	 */
	public function isActive(): bool
	{
		return self::isOverrideActive();
	}


	/**
	 * @return boolean
	 */
	public static function isOverrideActive() {
		return self::$override_active;
	}


	/**
	 * @param boolean $override_active
	 */
	public static function setOverrideActive($override_active) {
		self::$override_active = $override_active;
	}


	/**
	 * @param      $key
	 * @param      $value
	 * @param null $ttl
	 *
	 * @return bool
	 */
	public function set($key, $value, $ttl = null): bool
	{
		//		$ttl = $ttl ? $ttl : 480;
		if (!$this->global_cache instanceof ilGlobalCacheService || !$this->isActive()) {
			return false;
		}

		$return = $this->global_cache->set($key, $this->global_cache->serialize($value), $ttl);
		return $return;
	}


	/**
	 * @param $key
	 *
	 * @return bool|mixed|null
	 */
	public function get($key) {
		if (!$this->global_cache instanceof ilGlobalCacheService || !$this->isActive()) {
			return false;
		}
		$unserialized_return = $this->global_cache->unserialize($this->global_cache->get($key));

		if ($unserialized_return) {
			return $unserialized_return;
		}

		return null;
	}
}

?>
