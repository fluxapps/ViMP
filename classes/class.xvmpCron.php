<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
use srag\DIC\ViMP\DICTrait;

/**
 * Class xvmpCron
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xvmpCron {
    use DICTrait;
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
	 *
	 */
	function __construct() {
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
        $path = ilFileUtils::getWebspaceDir() . '/vimp';
        if (is_dir($path)) {
            foreach (new DirectoryIterator($path) as $directory) {
                if (!$directory->isDot()) {
                    if ((time() - $directory->getCTime()) > (24 * 60 * 60)) {
                        ilFileUtils::delDir($directory->getPathname());
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
        if (self::version()->is6()) {
            $notification->sendMail(
                $ilObjUser->getLogin(),
                '',
                '',
                $subject,
                $body,
                array(),
                true
            );
        } else {
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
		}

//		xvmpLog::getInstance()->write('Notification sent to user: ' . ilObjUser::_lookupLogin($uploaded_medium->getUserId()) . ' (' . $uploaded_medium->getUserId() . ')');
	}
}
