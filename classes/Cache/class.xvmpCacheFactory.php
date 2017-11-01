<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
/**
 * Class xvmpCacheFactory
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xvmpCacheFactory {
	private static $cache_instance = null;

	/**
	 * Generates an new instance of the live voting service.
	 *
	 * @return xvmpCache
	 */
	public static function getInstance() {

		if(self::$cache_instance === null)
		{
			if (xvmp::is52()) {
				self::$cache_instance = xvmpCache::getInstance('');
				self::$cache_instance->init();
			} else {
				self::$cache_instance = xvmpCache::getCacheInstance();
			}


			/*
			 * caching adapter of the xlvoConf will call getInstance again,
			 * due to that we need to call the init logic after we created the
			 * cache in an deactivated state.
			 *
			 * The xlvoConf call gets the deactivated cache and query the value
			 * out of the database. afterwards the cache is turned on with this init() call.
			 *
			 * This must be considered as workaround and should be probably fixed in the next major release.
			 */
		}

		return self::$cache_instance;

	}
}