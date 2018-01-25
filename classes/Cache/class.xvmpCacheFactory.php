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
			// 5.2 and 5.3 have the same cache methods
			// add switch statement if needed in further versions
			self::$cache_instance = xvmpCache::getInstance('');
			self::$cache_instance->init();
		}

		return self::$cache_instance;

	}
}