<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Class ilViMPPlugin
 *
 * @ilCtrl_isCalledBy ilViMPPlugin: ilUIPluginRouterGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ilViMPPlugin extends ilRepositoryObjectPlugin {

	const PLUGIN_NAME = 'ViMP';
	const XVMP = 'xvmp';

	const DEV = true;

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

	/**
	 *
	 */
	public function executeCommand() {
		global $ilCtrl;
		$cmd = $ilCtrl->getCmd();
		switch($cmd) {
			default:
				$this->{$cmd}();
				break;
		}
	}


	/**
	 * @param $lang_var
	 *
	 * @return string
	 */
	public function confTxt($lang_var) {
		return $this->txt('conf_' . $lang_var);
	}


	/**
	 * @return bool
	 */
	public function hasConnection() {
		try {
			$version = xvmpRequest::version();
			return ($version->getResponseStatus() == 200);
		} catch (xvmpException $e) {
			return false;
		}
	}


	/**
	 * @return string
	 */
	function getPluginName() {
		return self::PLUGIN_NAME;
	}


	/**
	 *
	 */
	protected function uninstallCustom() {
		global $DIC;
		$DIC->database()->dropTable(xvmpConf::returnDbTableName());
		$DIC->database()->dropTable(xvmpEventLog::returnDbTableName());
		$DIC->database()->dropTable(xvmpSelectedMedia::returnDbTableName());
		$DIC->database()->dropTable(xvmpSettings::returnDbTableName());
		$DIC->database()->dropTable(xvmpUploadedMedia::returnDbTableName());
		$DIC->database()->dropTable(xvmpUserLPStatus::returnDbTableName());
		$DIC->database()->dropTable(xvmpUserProgress::returnDbTableName());
	}

	/**
	 * async auto complete method for user filter in search table
	 */
	public function addUserAutoComplete() {
		include_once './Services/User/classes/class.ilUserAutoComplete.php';
		$auto = new ilUserAutoComplete();
		$auto->setSearchFields(array('login','firstname','lastname'));
		$auto->setResultField('login');
		$auto->enableFieldSearchableCheck(false);
		$auto->setMoreLinkAvailable(true);


		if(($_REQUEST['fetchall']))
		{
			$auto->setLimit(ilUserAutoComplete::MAX_ENTRIES);
		}

		$list = $auto->getList($_REQUEST['term']);

		echo $list;
		exit();
	}
}