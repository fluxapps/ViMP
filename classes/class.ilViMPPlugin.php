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

	const CMD_ADD_USER_AUTO_COMPLETE = 'addUserAutoComplete';

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
		$auto->setSearchFields(array('login','firstname','lastname', 'email'));
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


    /**
     * Before activation processing
     */
    protected function beforeActivation()
    {
        global $DIC;
        parent::beforeActivation();

        // check whether type exists in object data, if not, create the type
        $set = $DIC->database()->query("SELECT * FROM object_data " .
            " WHERE type = " . $DIC->database()->quote("typ", ilDBConstants::T_TEXT) .
            " AND title = " . $DIC->database()->quote(self::XVMP, ilDBConstants::T_TEXT)
        );
        if ($rec = $DIC->database()->fetchAssoc($set)) {
            $t_id = $rec["obj_id"];
        }

        // add rbac operations
        // 1: edit_permissions, 2: visible, 3: read, 4:write, 6:delete
        $ops = array(55, 95);
        foreach ($ops as $op) {
            // check whether type exists in object data, if not, create the type
            $set = $DIC->database()->query("SELECT * FROM rbac_ta " .
                " WHERE typ_id = " . $DIC->database()->quote($t_id, ilDBConstants::T_INTEGER) .
                " AND ops_id = " . $DIC->database()->quote($op, ilDBConstants::T_INTEGER)
            );
            if (!$DIC->database()->fetchAssoc($set)) {
                $DIC->database()->manipulate("INSERT INTO rbac_ta " .
                    "(typ_id, ops_id) VALUES (" .
                    $DIC->database()->quote($t_id, ilDBConstants::T_INTEGER) . "," .
                    $DIC->database()->quote($op, ilDBConstants::T_INTEGER) .
                    ")");
            }
        }

        return true;
    }
}