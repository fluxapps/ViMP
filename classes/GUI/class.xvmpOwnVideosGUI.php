<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class xvmpOwnVideosGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_isCalledBy xvmpOwnVideosGUI: ilObjViMPGUI
 */
class xvmpOwnVideosGUI extends xvmpVideosGUI {

	const SUBTAB_ACTIVE = xvmpVideosGUI::SUBTAB_OWN;

	const TABLE_CLASS = 'xvmpOwnVideosTableGUI';

	const CMD_EDIT_VIDEO = 'editVideo';
	const CMD_CHANGE_OWNER = 'changeOwner';
	const CMD_CONFIRMED_CHANGE_OWNER = 'confirmedChangeOwner';
	const CMD_UPDATE_VIDEO = 'updateVideo';
	const CMD_DELETE_VIDEO = 'deleteVideo';
	const CMD_UPLOAD_VIDEO_FORM = 'uploadVideoForm';
	const CMD_CREATE = 'create';
	const CMD_CONFIRMED_DELETE_VIDEO = 'confirmedDeleteVideo';
	const CMD_UPLOAD_CHUNKS = 'uploadChunks';


	/**
	 *
	 */
	public function executeCommand() {
		parent::executeCommand();
	}


	protected function performCommand($cmd): void
    {
		switch ($cmd) {
			case self::CMD_EDIT_VIDEO:
			case self::CMD_CHANGE_OWNER:
			case self::CMD_UPDATE_VIDEO:
			case self::CMD_DELETE_VIDEO:
			case self::CMD_CONFIRMED_CHANGE_OWNER:
			case self::CMD_CONFIRMED_DELETE_VIDEO:
				$mid = max($_GET['mid'], $_POST['mid']);
				$medium = xvmpMedium::find($mid);
				ilObjViMPAccess::checkAction(ilObjViMPAccess::ACTION_MANIPULATE_VIDEO, $this, $medium);
				break;
			case self::CMD_FILL_MODAL:
				$mid = max($_GET['mid'], $_POST['mid']);
				$medium = xvmpMedium::find($mid);
				ilObjViMPAccess::checkAction(ilObjViMPAccess::ACTION_PLAY_VIDEO, $this, $medium);
				break;
			default:
				if (!ilObjViMPAccess::hasWriteAccess() && !ilObjViMPAccess::hasUploadPermission()) {
					$this->accessDenied();
				}
		}
		if ($cmd != self::CMD_UPLOAD_CHUNKS) {
			/**
			 * this will find (and cache) or create a vimp user,
			 * or throw an exception if no vimp user is found and no vimp user can be created.
			 */
			xvmpUser::getOrCreateVimpUser($this->dic->user());
		}
		parent::performCommand($cmd);
	}


	/**
	 *
	 */
	public function editVideo() {
		$mid = $_GET['mid'];
		$xvmpEditVideoFormGUI = new xvmpEditVideoFormGUI($this, $mid);
		$xvmpEditVideoFormGUI->fillForm();
		$this->dic->ui()->mainTemplate()->setContent($xvmpEditVideoFormGUI->getHTML());
	}


	/**
	 *
	 */
	public function changeOwner() {
		$mid = filter_input(INPUT_GET, 'mid');
		$login = filter_input(INPUT_POST, 'login');
		$login_exists = ilObjUser::_loginExists($login);
		if ($login && $login_exists) {
			$ilConfirmationGUI = new ilConfirmationGUI();
			$ilConfirmationGUI->setFormAction($this->dic->ctrl()->getFormAction($this));
			$ilConfirmationGUI->setHeaderText($this->pl->txt('msg_warning_change_owner'));
			$ilConfirmationGUI->addItem('mid', $mid, sprintf(
				$this->pl->txt('confirmation_new_owner'),
				xvmpMedium::find($mid)->getTitle(),
				$login
			));
			$ilConfirmationGUI->addHiddenItem('login', $login);
			$ilConfirmationGUI->setConfirm($this->dic->language()->txt('confirm'), self::CMD_CONFIRMED_CHANGE_OWNER);
			$ilConfirmationGUI->setCancel($this->dic->language()->txt('cancel'), self::CMD_STANDARD);
			$this->dic->ui()->mainTemplate()->setContent($ilConfirmationGUI->getHTML());
		} else {
			if ($login && !$login_exists) {
                $this->tpl->setOnScreenMessage("failure", $this->pl->txt('msg_error_login_not_found'), true);
			}
			$xvmpChangeOwnerFormGUI = new xvmpChangeOwnerFormGUI($this, $mid);
			$this->dic->ui()->mainTemplate()->setContent($xvmpChangeOwnerFormGUI->getHTML());
		}
	}


	/**
	 *
	 */
	public function confirmedChangeOwner() {
		$mid = filter_input(INPUT_POST, 'mid');
		$login = filter_input(INPUT_POST, 'login');

		$medium = xvmpMedium::getObjectAsArray($mid);
		if ($medium['uid'] !== xvmpUser::getVimpUser($this->dic->user())->getUid()) {
            $this->tpl->setOnScreenMessage("failure", $this->pl->txt('permission_denied'), true);
			$this->dic->ctrl()->redirect($this, self::CMD_STANDARD);
		}

		$xvmpUser = xvmpUser::getOrCreateVimpUser(new ilObjUser(ilObjUser::getUserIdByLogin($login)));
		$medium['uid'] = $xvmpUser->getUid();
		$edit_fields = ['uid' => $xvmpUser->getUid(), 'mediapermissions' => implode(',', $medium['mediapermissions'])];
		foreach (xvmpConf::getConfig(xvmpConf::F_FORM_FIELDS) as $form_field) {
			// workaround for vimp bug (see PLVIMP-53)
			if ($form_field[xvmpConf::F_FORM_FIELD_REQUIRED] == 1 && $form_field[xvmpConf::F_FORM_FIELD_TYPE] == 1) {
				$edit_fields[$form_field[xvmpConf::F_FORM_FIELD_ID]] = 1;
			}
		}
		$response = xvmpRequest::editMedium($mid, $edit_fields)->getResponseBody();
		if ($response) {
            $this->tpl->setOnScreenMessage("success", $this->pl->txt('form_saved'), true);
			xvmpCacheFactory::getInstance()->delete(xvmpMedium::class . '-' . $mid);
			xvmpMedium::cache(xvmpMedium::class . '-' . $mid, $medium);
			xvmpEventLog::logEvent(xvmpEventLog::ACTION_CHANGE_OWNER, $this->getObjId(), array(
				'owner' => $login,
				'mid' => $mid,
				'title' => $medium['title']
			));
			/** @var xvmpUploadedMedia $xvmpUploadedMedia */
			foreach (xvmpUploadedMedia::where(['mid' => $mid, 'user_id' => $this->dic->user()->getId()])->get() as $xvmpUploadedMedia) {
				$new_user_id = ilObjUser::_lookupId($login);
				$xvmpUploadedMedia->setUserId($new_user_id);
				$xvmpUploadedMedia->setEmail(ilObjUser::_lookupEmail($new_user_id));
				$xvmpUploadedMedia->update();
			}
		} else {
            $this->tpl->setOnScreenMessage("failure", $this->pl->txt('failure'), true);
		}

		$this->dic->ctrl()->redirect($this, self::CMD_STANDARD);
	}

	/**
	 *
	 */
	public function updateVideo() {
		$xvmpEditVideoFormGUI = new xvmpEditVideoFormGUI($this, $_POST['mid']);
		$xvmpEditVideoFormGUI->setValuesByPost();
		if ($xvmpEditVideoFormGUI->saveForm()) {
            $this->tpl->setOnScreenMessage("success", $this->pl->txt('form_saved'), true);
            $this->dic->ctrl()->redirect($this, self::CMD_STANDARD);
		}
        $this->tpl->setOnScreenMessage("failure", $this->pl->txt('msg_incomplete'), true);
		$this->dic->ui()->mainTemplate()->setContent($xvmpEditVideoFormGUI->getHTML());
	}

	/**
	 *
	 */
	public function uploadVideoForm() {
		$xvmpUploadVideoFormGUI = new xvmpUploadVideoFormGUI($this);
		$xvmpUploadVideoFormGUI->fillForm();
		$this->dic->ui()->mainTemplate()->setContent($xvmpUploadVideoFormGUI->getHTML());
	}


	/**
	 *
	 */
	public function create() {
		$xvmpEditVideoFormGUI = new xvmpUploadVideoFormGUI($this);
		$xvmpEditVideoFormGUI->setValuesByPost();
		if ($xvmpEditVideoFormGUI->saveForm()) {
            $this->tpl->setOnScreenMessage("success", $this->pl->txt('video_uploaded'), true);
            $this->dic->ctrl()->redirect($this, self::CMD_STANDARD);
		}
        $this->tpl->setOnScreenMessage("failure", $this->pl->txt('form_incomplete'), true);

		$xvmpEditVideoFormGUI->setValuesByPost();
		$this->dic->ui()->mainTemplate()->setContent($xvmpEditVideoFormGUI->getHTML());
	}


	/**
	 *
	 */
	public function deleteVideo() {
		$mid = $_GET['mid'];
		$video = xvmpMedium::find($mid);
		$confirmation_gui = new ilConfirmationGUI();
		$confirmation_gui->setFormAction($this->dic->ctrl()->getFormAction($this));
		$confirmation_gui->setHeaderText($this->pl->txt('confirm_delete_text'));
		$confirmation_gui->addItem('mid', $mid, $video->getTitle());
		$confirmation_gui->setConfirm($this->dic->language()->txt('delete'),self::CMD_CONFIRMED_DELETE_VIDEO);
		$confirmation_gui->setCancel($this->dic->language()->txt('cancel'), self::CMD_STANDARD);
		$this->dic->ui()->mainTemplate()->setContent($confirmation_gui->getHTML());
	}


	/**
	 *
	 */
	public function confirmedDeleteVideo() {
		$mid = $_POST['mid'];

		// fetch the video for logging purposes
		$video = xvmpMedium::getObjectAsArray($mid);

		xvmpMedium::deleteObject($mid);

		xvmpEventLog::logEvent(xvmpEventLog::ACTION_DELETE, $this->getObjId(), $video);
        $this->tpl->setOnScreenMessage("succuess", $this->pl->txt('video_deleted'), true);
		$this->dic->ctrl()->redirect($this, self::CMD_STANDARD);
	}


	/**
	 *
	 */
	protected function uploadChunks() {
		$xoctPlupload = new xoctPlupload();
		$tmp_id = filter_input(INPUT_GET, 'tmp_id', FILTER_SANITIZE_STRING);

		$dir = ILIAS_ABSOLUTE_PATH  . ltrim(ilFileUtils::getWebspaceDir(), '.') . '/vimp/' . $tmp_id;
		if (!is_dir($dir)) {
			ilFileUtils::makeDir($dir);
		}

		$xoctPlupload->setTargetDir($dir);
		$xoctPlupload->handleUpload();
	}

}
