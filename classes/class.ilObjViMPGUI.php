<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once __DIR__ . '/../vendor/autoload.php';
require_once 'Services/Repository/classes/class.ilObjectPluginGUI.php';

/**
 * Class ilObjViMPGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_isCalledBy ilObjViMPGUI: ilRepositoryGUI, ilObjPluginDispatchGUI, ilAdministrationGUI
 * @ilCtrl_Calls      ilObjViMPGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI
 */
class ilObjViMPGUI extends ilObjectPluginGUI {

	function getType() {
		return ilViMPPlugin::XVMP;
	}


	function getAfterCreationCmd() {
		// TODO: Implement getAfterCreationCmd() method.
	}


	function getStandardCmd() {
		// TODO: Implement getStandardCmd() method.
	}
}