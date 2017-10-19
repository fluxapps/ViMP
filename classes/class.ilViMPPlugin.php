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

	function getPluginName() {
		return self::PLUGIN_NAME;
	}


	protected function uninstallCustom() {
		// TODO: Implement uninstallCustom() method.
	}
}