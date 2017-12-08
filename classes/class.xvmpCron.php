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

		global $ilDB, $ilUser, $ilCtrl, $ilLog, $ilias;
		if (self::DEBUG) {
			$ilLog->write('Auth passed for async ViMP');
		}
		/**
		 * @var $ilDB   ilDB
		 * @var $ilUser ilObjUser
		 * @var $ilCtrl ilCtrl
		 */
		$this->db = $ilDB;
		$this->user = $ilUser;
		$this->ctrl = $ilCtrl;
		$this->ilias = $ilias;
		$this->pl = ilViMPPlugin::getInstance();
	}


	public function initILIAS() {
		chdir(substr($_SERVER['SCRIPT_FILENAME'], 0, strpos($_SERVER['SCRIPT_FILENAME'], '/Customizing')));
		require_once('include/inc.ilias_version.php');
		require_once('Services/Component/classes/class.ilComponent.php');
		if (ilComponent::isVersionGreaterString(ILIAS_VERSION_NUMERIC, '5.1.999')) {
			require_once './Services/Cron/classes/class.ilCronStartUp.php';
			$ilCronStartup = new ilCronStartUp($_SERVER['argv'][3], $_SERVER['argv'][1], $_SERVER['argv'][2]);
			$ilCronStartup->initIlias();
			$ilCronStartup->authenticate();
		} elseif (ilComponent::isVersionGreaterString(ILIAS_VERSION_NUMERIC, '5.0.999')) {
			require_once "Services/Context/classes/class.ilContext.php";
			ilContext::init(ilContext::CONTEXT_CRON);
			require_once 'Services/Authentication/classes/class.ilAuthFactory.php';
			ilAuthFactory::setContext(ilAuthFactory::CONTEXT_CRON);
			require_once './include/inc.header.php';
		} elseif (ilComponent::isVersionGreaterString(ILIAS_VERSION_NUMERIC, '4.2.999')) {
			require_once './Services/Context/classes/class.ilContext.php';
			ilContext::init(ilContext::CONTEXT_WEB);
			require_once './Services/Init/classes/class.ilInitialisation.php';
			ilInitialisation::initILIAS();
		} else {
			$_GET['baseClass'] = 'ilStartUpGUI';
			require_once('./include/inc.get_pear.php');
			require_once('./include/inc.header.php');
		}

		require_once './Services/Mail/classes/class.ilMimeMail.php';
		require_once './Services/Mail/classes/class.ilMail.php';

		// fix for some stupid ilias init....
		global $ilSetting;
		if (!$ilSetting) {
			$ilSetting = new ilSessionMock();
		}
	}


	/**
	 *
	 */
	public function run() {
		/** @var xvmpUploadedMedia $uploaded_medium */
		foreach (xvmpUploadedMedia::get() as $uploaded_medium) {
			try {
				$medium = xvmpMedium::find($uploaded_medium->getMid());
				if ($medium->getStatus() == 'legal') {
					$this->sendNotification($medium, $uploaded_medium);
				}
			} catch (xvmpException $e) {
				if ($e->getCode() == 404) {

				}
				continue;
			}
		}
		//TODO: evtl. alte eventlog einträge löschen
	}


	/**
	 * @param xvmpMedium        $medium
	 * @param xvmpUploadedMedia $uploaded_medium
	 */
	protected function sendNotification(xvmpMedium $medium, xvmpUploadedMedia $uploaded_medium) {
		xvmpLog::getInstance()->write('Medium transcoded successfully: ' . $medium->getTitle() . ' (' . $medium->getMid() . ')');

		$body = xvmpConf::getConfig(xvmpConf::F_NOTIFICATION_BODY_SUCCESSFULL);

		// replace placeholders
		$ilObjUser = new ilObjUser($uploaded_medium->getUserId());
		$body = str_replace('{FIRSTNAME}', $ilObjUser->getFirstname(), $body);
		$body = str_replace('{LASTNAME}', $ilObjUser->getLastname(), $body);

		$body = str_replace('{TITLE}', $medium->getTitle(), $body);
		$body = str_replace('{DESCRIPTION}', $medium->getDescription(), $body);

		// send mail
		$notification = new ilMail(ANONYMOUS_USER_ID);
		$notification->sendMail(
			$ilObjUser->getLogin(),
			'',
			'',
			xvmpConf::getConfig(xvmpConf::F_NOTIFICATION_SUBJECT_SUCCESSFULL),
			$body,
			array(),
			array('normal'),
			1
		);
		xvmpLog::getInstance()->write('to: ' . ilObjUser::_lookupLogin($uploaded_medium->getUserId()));

		// delete temp file and entry
		$dir = ILIAS_HTTP_PATH . ltrim(ilUtil::getWebspaceDir(), '.') . '/vimp/' . $uploaded_medium->getTmpId();
		ilUtil::delDir($dir);
		$uploaded_medium->delete();

		// set visible
		/** @var xvmpSelectedMedia $selected */
		foreach (xvmpSelectedMedia::where(array('mid' => $medium->getId()))->get() as $selected) {
			$selected->setVisible(1);
			$selected->update();
		}

		xvmpLog::getInstance()->write('Notification sent to user: ' . ilObjUser::_lookupLogin($uploaded_medium->getUserId()) . ' (' . $uploaded_medium->getUserId() . ')');
	}


}

class ilSessionMock {
	public function get($what, $default) {
		return $default;
	}

}