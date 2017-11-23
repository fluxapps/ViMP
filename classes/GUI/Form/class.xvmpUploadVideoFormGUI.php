<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class xvmpUploadVideoFormGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xvmpUploadVideoFormGUI extends xvmpFormGUI {

	/**
	 * @var ilLanguage
	 */
	protected $lng;
	/**
	 * @var ilViMPPlugin
	 */
	protected $pl;
	/**
	 * @var xvmpOwnVideosGUI
	 */
	protected $parent_gui;
	/**
	 * @var array
	 */
	protected $video;
	/**
	 * @var ilObjUser
	 */
	protected $user;

	public function __construct($parent_gui) {
		global $ilUser;
		$this->user = $ilUser;

		$this->setId('xoct_event');

		parent::__construct($parent_gui);

		$this->setTitle($this->pl->txt('upload_video'));
		$this->setTarget('_top');
		$this->addCommandButtons();

	}

	protected function initForm() {
		$tmp_id = ilUtil::randomhash();
		$this->ctrl->setParameter($this->parent_gui, 'tmp_id', $tmp_id);
		$this->setFormAction($this->ctrl->getFormAction($this->parent_gui));

		// HIDDEN ID
		$input = new ilHiddenInputGUI('mid');
		$this->addItem($input);

		// FILE
		$input = new xvmpFileUploadInputGUI($this, xvmpOwnVideosGUI::CMD_CREATE, $this->lng->txt('file'), 'source_url');

		$input->setUrl($this->ctrl->getLinkTarget($this->parent_gui, xvmpOwnVideosGUI::CMD_UPLOAD_CHUNKS));
		$input->setSuffixes(array(
			'mov',
			'mp4',
			'm4v',
			'flv',
			'mpeg',
			'avi',
		));
		$input->setMimeTypes(array(
			'video/avi',
			'video/quicktime',
			'video/mpeg',
			'video/mp4',
			'video/ogg',
			'video/webm',
			'video/x-ms-wmv',
			'video/x-flv',
			'video/x-matroska',
			'video/x-msvideo',
			'video/x-dv',
		));
		$input->setRequired(true);
		$this->addItem($input);

		// ADD AUTOMATICALLY
		$input = new ilCheckboxInputGUI($this->pl->txt('add_automatically'), 'add_automatically');
		$input->setInfo($this->pl->txt('add_automatically_info'));
		$this->addItem($input);

		// NOTIFICATION
		$input = new ilCheckboxInputGUI($this->pl->txt('notification'), 'notification');
		$input->setInfo($this->pl->txt('notification_info'));
		$this->addItem($input);

		// TITLE
		$input = new ilTextInputGUI($this->pl->txt('title'), 'title');
		$input->setRequired(true);
		$this->addItem($input);

		// DESCRIPTION
		$input = new ilTextAreaInputGUI($this->pl->txt('description'), 'description');
		$input->setRequired(true);
		$this->addItem($input);

		// custom fields
		foreach (xvmpConf::getConfig(xvmpConf::F_FORM_FIELDS) as $field) {
			$input = new ilTextInputGUI($field[xvmpConf::F_FORM_FIELD_TITLE], $field[xvmpConf::F_FORM_FIELD_ID]);
			$input->setRequired($field[xvmpConf::F_FORM_FIELD_REQUIRED]);
			if ($field[xvmpConf::F_FORM_FIELD_FILL_USER_DATA]) {
				$input->setValue($this->user->getFirstname() . ' ' . $this->user->getLastname());
			}
			$this->addItem($input);
		}

		// PUBLISHED (Zugriff)
		$input = new ilRadioGroupInputGUI($this->pl->txt('published'), 'published');
		$radio_item = new ilRadioOption($this->pl->txt('private'), 'private');
		$input->addOption($radio_item);
		$radio_item = new ilRadioOption($this->lng->txt('public'), 'public');
		$input->addOption($radio_item);
		$input->setRequired(true);
		$this->addItem($input);

		// MEDIA PERMISSIONS
		$media_permissions = xvmpConf::getConfig(xvmpConf::F_MEDIA_PERMISSIONS);
		if ($media_permissions) {
			$input = new ilMultiSelectInputGUI($this->pl->txt(xvmpConf::F_MEDIA_PERMISSIONS), 'mediapermissions');
			$options = array();
			if ($media_permissions == xvmpConf::MEDIA_PERMISSION_SELECTION) {
				$selectable_roles = xvmpConf::getConfig(xvmpConf::F_MEDIA_PERMISSIONS_SELECTION);
			}
			foreach (xvmpUserRoles::getAll() as $role) {
				if ($selectable_roles && !in_array($role->getId(), $selectable_roles)) {
					continue;
				}
				$options[$role->getId()] = $role->getName();
			}
			$input->setOptions($options);
			if (!empty($options)) {
				$this->addItem($input);
			}
		}

		// CATEGORIES
		$input = new ilMultiSelectInputGUI($this->lng->txt('categories'), 'categories');
		$categories = xvmpCategory::getAll();
		$options = array();
		/** @var xvmpCategory $category */
		foreach ($categories as $category) {
			$options[$category->getId()] = $category->getName();
		}
		$input->setOptions($options);
		$input->setRequired(true);
		$this->addItem($input);

		// TAGS
		$input = new ilTextInputGUI($this->pl->txt('tags'), 'tags');
		$input->setRequired(true);
		$this->addItem($input);

	}

	protected function addCommandButtons() {
		$this->addCommandButton(xvmpOwnVideosGUI::CMD_CREATE, $this->lng->txt('save'));
		$this->addCommandButton(xvmpOwnVideosGUI::CMD_CANCEL, $this->lng->txt(xvmpOwnVideosGUI::CMD_CANCEL));
	}


	/**
	 *
	 */
	public function uploadVideo() {
		if (!$this->checkInput()) {
			return false;
		}
		$video = array();
		/** @var ilFormPropertyGUI $item */
		foreach ($this->getItems() as $item) {
			$value = $this->getInput($item->getPostVar());

			switch ($item->getPostVar()) {
				case 'source_url':
					$tmp_id = $_GET['tmp_id'];
					$video[$item->getPostVar()] =  ILIAS_HTTP_PATH . ltrim(ilUtil::getWebspaceDir(), '.') . '/vimp/' . $tmp_id . '/' . rawurlencode($value['name']);
					break;
				case 'add_automatically':
					$add_automatically = (int) $value;
					break;
				case 'notification':
					$notification = (int) $value;
					break;
				default:
					if ($value) {
						$video[$item->getPostVar()] = is_array($value) ? implode(',', $value) : $value;
					}
					break;
			}


		}

		try {
			$video = xvmpMedium::upload($video, $this->parent_gui->getObjId(), $tmp_id,$add_automatically, $notification);
		} catch (xvmpException $e) {
			ilUtil::sendFailure($e->getMessage(), true);
			return false;
		}

		// the object has to be loaded again, since the response from "upload" has another format for the categories
		$video = xvmpMedium::getObjectAsArray($video['mid']);

		xvmpEventLog::logEvent(xvmpEventLog::ACTION_UPLOAD, $this->parent_gui->getObjId(), $video);

		return true;
				// indirect file download via ViMP/transfer.php
//				if (!is_dir(CLIENT_DATA_DIR . '/vimp_upload')) {
//					ilUtil::makeDir(CLIENT_DATA_DIR . '/vimp_upload');
//				}
//				if (!is_dir(CLIENT_DATA_DIR . '/vimp_upload/' . $tmp_id)) {
//					ilUtil::makeDir(CLIENT_DATA_DIR . '/vimp_upload/' . $tmp_id);
//				}
//				ilUtil::moveUploadedFile($value['tmp_name'],$value['name'],CLIENT_DATA_DIR . '/vimp_upload/' . $tmp_id . '/' . $value['name']);
//				$value = ILIAS_HTTP_PATH . '/vimp_transfer/' . $tmp_id . '/' . $value['name'];
//				$value = htmlentities($value);

				// direct file download via ViMP/transfer folder

	}
}