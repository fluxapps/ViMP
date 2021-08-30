<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use srag\Plugins\ViMP\Database\Config\ConfigAR;
use srag\Plugins\ViMP\Database\EventLog\EventLogAR;
use srag\Plugins\ViMP\Database\SelectedMedia\SelectedMediaAR;
use srag\Plugins\ViMP\Database\Settings\SettingsAR;
use srag\Plugins\ViMP\Database\UploadedMedia\UploadedMediaAR;
use srag\Plugins\ViMP\Database\UserLPStatus\UserLPStatusAR;
use srag\Plugins\ViMP\Database\UserProgress\UserProgressAR;

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
     * @param string $relative_path path after [PLUGIN_PATH]/templates/
     * @param bool   $versioned
     * @return string
     */
	public function getAssetURL(string $relative_path, bool $versioned = true) : string
    {
        $version_suffix = $versioned ? '?version=' . str_replace('.', '-', $this->getVersion()) : '';
        return $this->getDirectory() . '/templates/' . ltrim($relative_path, '/') . $version_suffix;
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
		$DIC->database()->dropTable(ConfigAR::returnDbTableName());
		$DIC->database()->dropTable(EventlogAR::returnDbTableName());
		$DIC->database()->dropTable(SelectedMediaAR::returnDbTableName());
		$DIC->database()->dropTable(SettingsAR::returnDbTableName());
		$DIC->database()->dropTable(UploadedMediaAR::returnDbTableName());
		$DIC->database()->dropTable(UserLPStatusAR::returnDbTableName());
		$DIC->database()->dropTable(UserProgressAR::returnDbTableName());
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
        $ops = array_map(function (array $operation) {
            return $operation["ops_id"];
        }, $DIC->database()->fetchAll($DIC->database()->query("SELECT ops_id FROM rbac_operations WHERE " . $DIC->database()->in("operation", ["read_learning_progress", "edit_learning_progress"], false, ilDBConstants::T_TEXT))));
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
