<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class xvmpCron
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xvmpCron {

	const DEBUG = false;
	/**
	 * @var Ilias
	 */
	protected $ilias;
	/**
	 * @var ilViMPPlugin
	 */
	protected $pl;


	/**
	 * @param array $data
	 */
	function __construct($data) {
        $_COOKIE['ilClientId'] = $data[3];
		$_POST['username'] = $data[1];
		$_POST['password'] = $data[2];
		$this->initILIAS();

		global $DIC;
		$ilDB = $DIC['ilDB'];
		$ilCtrl = $DIC['ilCtrl'];
		$ilLog = $DIC['ilLog'];
		$ilias = $DIC['ilias'];
		if (self::DEBUG) {
			$ilLog->write('Auth passed for async ViMP');
		}
		/**
		 * @var $ilDB   ilDB
		 * @var $ilUser ilObjUser
		 * @var $ilCtrl ilCtrl
		 */
		$this->db = $ilDB;
		$this->ctrl = $ilCtrl;
		$this->ilias = $ilias;
		$this->pl = ilViMPPlugin::getInstance();
	}


	public function initILIAS() {
		chdir(substr($_SERVER['SCRIPT_FILENAME'], 0, strpos($_SERVER['SCRIPT_FILENAME'], '/Customizing')));
		require_once 'include/inc.ilias_version.php';
		require_once 'Services/Component/classes/class.ilComponent.php';
		require_once './Services/Cron/classes/class.ilCronStartUp.php';
		require_once './Customizing/global/plugins/Services/Repository/RepositoryObject/ViMP/classes/class.xvmp.php';

		$ilCronStartup = new ilCronStartUp($_SERVER['argv'][3], $_SERVER['argv'][1], $_SERVER['argv'][2]);
		if (!xvmp::is54()) {
		    $ilCronStartup->initIlias();
        }
		$ilCronStartup->authenticate();

		$this->buildHTTPPath();

		require_once './Services/Mail/classes/class.ilMimeMail.php';
		require_once './Services/Mail/classes/class.ilMail.php';

		// fix for some stupid ilias init....
		global $DIC;
		$ilSetting = $DIC['ilSetting'];
		if (!$ilSetting) {
			$ilSetting = new ilSessionMock();
		}
	}

    /**
     * builds http path
     */
    protected function buildHTTPPath()
    {
        global $DIC;
        define('ILIAS_HTTP_PATH', $DIC['ilIliasIniFile']->readVariable('server', 'http_path'));
    }


	/**
	 *
	 */
	public function run() {
	    // notifications
		/** @var xvmpUploadedMedia $uploaded_medium */
		foreach (xvmpUploadedMedia::get() as $uploaded_medium) {
			try {
				$medium = xvmpMedium::find($uploaded_medium->getMid());
                    switch($medium->getStatus()) {
                        case "legal":
                            if($uploaded_medium->getNotification()) {
                                    $this->sendNotification($medium, $uploaded_medium, true);
                            }
                            foreach (xvmpSelectedMedia::where(array('mid' => $medium->getId()))->get() as $selected) {
                                   $selected->setVisible(1);
                                   $selected->update();
                            }
                            $uploaded_medium->delete();
                            break;
                        case "error":
                            if($uploaded_medium->getNotification()) {
                                    $this->sendNotification($medium, $uploaded_medium, false);
                            }

                            $uploaded_medium->delete();
                            break;
                        case "uploaded":
                            break;
                        case "converting":
                            break;
                        default:
                            $uploaded_medium->delete();
                    }

			} catch (xvmpException $e) {
				if ($e->getCode() == 404 && strpos($e->getMessage(), "Medium not exist") !== false) {
					$uploaded_medium->delete();
				}
				continue;
			}
		}

		// delete abandoned uploads (older than 24 hours)
        $path = ilUtil::getWebspaceDir() . '/vimp';
        if (is_dir($path)) {
            foreach (new DirectoryIterator($path) as $directory) {
                if (!$directory->isDot()) {
                    if ((time() - $directory->getCTime()) > (24 * 60 * 60)) {
                        ilUtil::delDir($directory->getPathname());
                    }
                }
            }
        }
	}


	/**
	 * @param xvmpMedium        $medium
	 * @param xvmpUploadedMedia $uploaded_medium
	 */
	protected function sendNotification(xvmpMedium $medium, xvmpUploadedMedia $uploaded_medium, $transcoding_succeeded) {
		$subject = xvmpConf::getConfig($transcoding_succeeded ? xvmpConf::F_NOTIFICATION_SUBJECT_SUCCESSFULL : xvmpConf::F_NOTIFICATION_SUBJECT_FAILED);
		$body = xvmpConf::getConfig($transcoding_succeeded ? xvmpConf::F_NOTIFICATION_BODY_SUCCESSFULL : xvmpConf::F_NOTIFICATION_BODY_FAILED);

		// replace placeholders
		$ilObjUser = new ilObjUser($uploaded_medium->getUserId());
		$body = str_replace('{FIRSTNAME}', $ilObjUser->getFirstname(), $body);
		$body = str_replace('{LASTNAME}', $ilObjUser->getLastname(), $body);

		$body = str_replace('{TITLE}', $medium->getTitle(), $body);
		$body = str_replace('{DESCRIPTION}', $medium->getDescription(), $body);


        $deep_link = ilLink::_getStaticLink(
            $uploaded_medium->getRefId(),
            'xvmp',
            true,
            '_' . $uploaded_medium->getMid()
        );
        $body = str_replace('{VIDEO_LINK}', $deep_link, $body);

		// send mail
		$notification = new ilMail(ANONYMOUS_USER_ID);
		$notification->sendMail(
			$ilObjUser->getLogin(),
			'',
			'',
			$subject,
			$body,
			array(),
			array('normal'),
			1
		);

//		xvmpLog::getInstance()->write('Notification sent to user: ' . ilObjUser::_lookupLogin($uploaded_medium->getUserId()) . ' (' . $uploaded_medium->getUserId() . ')');
	}

	/**
	 *
	 */
	public function logout() {
		global $DIC;
		$ilAuth = $DIC["ilAuthSession"];
		$ilAuth->logout();
	}

}

class ilSessionMock {
	public function get($what, $default) {
		return $default;
	}

}
