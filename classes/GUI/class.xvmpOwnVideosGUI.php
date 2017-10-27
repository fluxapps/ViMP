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
	const CMD_UPDATE_VIDEO = 'updateVideo';
	const CMD_DELETE_VIDEO = 'deleteVideo';
	const CMD_UPLOAD_VIDEO_FORM = 'uploadVideoForm';
	const CMD_UPLOAD_VIDEO = 'uploadVideo';
	const CMD_CONFIRMED_DELETE_VIDEO = 'confirmedDeleteVideo';


	/**
	 *
	 */
	public function editVideo() {
		$mid = $_GET['mid'];
		$xvmpEditVideoFormGUI = new xvmpEditVideoFormGUI($this, $mid);
		$xvmpEditVideoFormGUI->fillForm();
		$this->tpl->setContent($xvmpEditVideoFormGUI->getHTML());
	}


	/**
	 *
	 */
	public function updateVideo() {
		$xvmpEditVideoFormGUI = new xvmpEditVideoFormGUI($this, $_POST['mid']);
		$xvmpEditVideoFormGUI->setValuesByPost();
		if (!$xvmpEditVideoFormGUI->saveForm()) {
			ilUtil::sendFailure($this->pl->txt('msg_incomplete'));
			$this->tpl->setContent($xvmpEditVideoFormGUI->getHTML());
		}
		ilUtil::sendSuccess($this->pl->txt('form_saved'), true);
		$this->ctrl->redirect($this, self::CMD_EDIT_VIDEO);

	}

	/**
	 *
	 */
	public function uploadVideoForm() {
		$xvmpEditVideoFormGUI = new xvmpUploadVideoFormGUI($this);
		$this->tpl->setContent($xvmpEditVideoFormGUI->getHTML());
	}

	public function uploadVideo() {
		$xvmpEditVideoFormGUI = new xvmpUploadVideoFormGUI($this);
		$xvmpEditVideoFormGUI->setValuesByPost();
		if ($xvmpEditVideoFormGUI->uploadVideo()) {
			ilUtil::sendSuccess($this->pl->txt('video_uploaded'), true);
			$this->ctrl->redirect($this, self::CMD_STANDARD);
		}

		ilUtil::sendFailure($this->pl->txt('form_incomplete'));
		$xvmpEditVideoFormGUI->setValuesByPost();
		$this->tpl->setContent($xvmpEditVideoFormGUI->getHTML());
	}

	public function deleteVideo() {
		$mid = $_GET['mid'];
		$video = xvmpMedium::find($mid);
		$confirmation_gui = new ilConfirmationGUI();
		$confirmation_gui->setFormAction($this->ctrl->getFormAction($this));
		$confirmation_gui->setHeaderText($this->pl->txt('confirm_delete_text'));
		$confirmation_gui->addItem('mid', $mid, $video->getTitle());
		$confirmation_gui->setConfirm($this->lng->txt('delete'),self::CMD_CONFIRMED_DELETE_VIDEO);
		$confirmation_gui->setCancel($this->lng->txt('cancel'), self::CMD_STANDARD);
		$this->tpl->setContent($confirmation_gui->getHTML());
	}

	public function confirmedDeleteVideo() {
		$mid = $_POST['mid'];
		xvmpMedium::deleteObject($mid);
		ilUtil::sendSuccess($this->pl->txt('video_deleted'), true);
		$this->ctrl->redirect($this, self::CMD_STANDARD);
	}

}