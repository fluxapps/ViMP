<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once __DIR__ . '/../vendor/autoload.php';
require_once 'Services/Repository/classes/class.ilRepositoryObjectPlugin.php';

/**
 * Class ilViMPPlugin
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ilViMPPlugin extends ilRepositoryObjectPlugin {

	const PLUGIN_NAME = 'ViMP';
	const XVMP = 'xvmp';

	/**
	 * @var ilViMPPlugin
	 */
	protected static $instance;


	/**
	 * @return ilViMPPlugin
	 */
	public static function getInstance() {
		if (!isset(self::$instance)) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function confTxt($lang_var) {
		return $this->txt('conf_' . $lang_var);
	}

	public function hasConnection() {
		try {
			$version = xvmpRequest::version();
			return ($version->getResponseStatus() == 200);
		} catch (xvmpException $e) {
			return false;
		}
	}



	function getPluginName() {
		return self::PLUGIN_NAME;
	}


	protected function uninstallCustom() {
		// TODO: Implement uninstallCustom() method.
	}
}