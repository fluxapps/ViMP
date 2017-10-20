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

	public function performCommand($cmd) {
		switch ($cmd) {
			default:
				$this->$cmd();
				break;
		}
	}


	/**
	 * called by the button to test connection inside the plugin config
	 */
	public function testConnectionAjax() {
		$apikey = $_GET['apikey'];
		$apiurl = $_GET['apiurl'];

		$xvmpCurl = new xvmpCurl(rtrim($apiurl, '/') . '/' . ltrim(xvmpRequest::VERSION, '/'));
		$xvmpCurl->addPostField('apikey', $apikey);
		try {
			$xvmpCurl->post();
			echo "Connection OK";
			exit;
		} catch (Exception $e) {
			$message = 'No Connection, Status Code ' . $e->getCode();
			switch ($e->getCode()) {
				case 401:
					$message .= ' - No Authorization, possibly wrong API-Key';
					break;
				case 404:
					$message .= ' - Not Found, possibly wrong relative URL';
					break;
				case 500:
					$message .= ' - Internal Server Error, possibly wrong URL';
					break;
			}
			echo $message;
			exit;
		}
	}

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