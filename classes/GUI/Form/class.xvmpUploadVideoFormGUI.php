<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class xvmpUploadVideoFormGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xvmpUploadVideoFormGUI extends ilPropertyFormGUI {

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


	public function __construct($parent_gui) {
		global $ilCtrl, $lng;
		$this->lng = $lng;
		$this->pl = ilViMPPlugin::getInstance();
		$this->parent_gui = $parent_gui;

		parent::__construct();

		$this->setTitle($this->pl->txt('upload_video'));
		$this->setFormAction($ilCtrl->getFormAction($this->parent_gui));
		$this->initForm();
	}

	protected function initForm() {


		$required_metada = xvmpConf::getConfig(xvmpConf::F_REQUIRED_METADATA);

		// HIDDEN ID
		$input = new ilHiddenInputGUI('mid');
		$this->addItem($input);

		// FILE
		$input = new ilFileInputGUI($this->pl->txt('file'), 'source_url');

		$this->addItem($input);

		// TITLE
		$input = new ilTextInputGUI($this->pl->txt('title'), 'title');
		$input->setRequired(true);
		$this->addItem($input);

		// DESCRIPTION
		$input = new ilTextAreaInputGUI($this->pl->txt('description'), 'description');
		$input->setRequired(in_array('description', $required_metada));
		$this->addItem($input);

		// AUTHOR
		$input = new ilTextInputGUI($this->lng->txt('author'), 'custom_author');
		$input->setRequired(in_array('author', $required_metada));
		$this->addItem($input);

		// COPYRIGHT
		$input = new ilTextInputGUI($this->pl->txt('copyright'), 'copyright');
		$input->setRequired(in_array('copyright', $required_metada));
		$this->addItem($input);

		// PUBLISHED (Zugriff)
		$input = new ilRadioGroupInputGUI($this->pl->txt('published'), 'published');
		$radio_item = new ilRadioOption($this->pl->txt('private'), 'private');
		$input->addOption($radio_item);
		$radio_item = new ilRadioOption($this->lng->txt('public'), 'public');
		$input->addOption($radio_item);
		$this->addItem($input);

		// MEDIA PERMISSIONS
		$media_permissions = xvmpConf::getConfig(xvmpConf::F_MEDIA_PERMISSIONS);

		//		if ($media_permissions )
		// TODO: media permissions

		// ADD AUTOMATICALLY
		if (!$this->video) {
			// TODO: automatisch hinzufügen
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
		$this->addItem($input);

		// TAGS
		$input = new ilTextInputGUI($this->pl->txt('tags'), 'tags');
		$this->addItem($input);

		$this->addCommandButtons();
	}

	protected function addCommandButtons() {
		$this->addCommandButton(xvmpOwnVideosGUI::CMD_UPLOAD_VIDEO, $this->lng->txt('save'));
		$this->addCommandButton(xvmpOwnVideosGUI::CMD_CANCEL, $this->lng->txt(xvmpOwnVideosGUI::CMD_CANCEL));
	}


	/**
	 *
	 */
	public function uploadVideo() {
		$this->checkInput();
		$video = array();
		/** @var ilFormPropertyGUI $item */
		foreach ($this->getItems() as $item) {
			$value = $this->getInput($item->getPostVar());

			if ($item instanceof ilFileInputGUI) {
				$tmp_id = ilUtil::randomhash();

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
				$dir = $this->pl->getDirectory() . '/transfer/' . $tmp_id;
				if (!is_dir($dir)) {
					ilUtil::makeDir($dir);
				}
				$target_path = $dir . '/' . $value['name'];
				ilUtil::moveUploadedFile($value['tmp_name'], $value['name'], $target_path);
				$value = htmlentities(ILIAS_HTTP_PATH . '/' . ltrim($target_path, '.'));
			}

			if ($value) {
				$video[$item->getPostVar()] = is_array($value) ? implode(',', $value) : $value;
			}

		}

		xvmpMedium::upload($video);
	}
}