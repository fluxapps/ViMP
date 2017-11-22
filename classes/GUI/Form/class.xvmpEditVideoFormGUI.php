<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class xvmpEditVideoFormGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xvmpEditVideoFormGUI extends xvmpFormGUI {

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

	public function __construct($parent_gui, $mid) {
		$this->video = xvmpMedium::getObjectAsArray($mid);

		parent::__construct($parent_gui);

		$this->ctrl->setParameter($this->parent_gui, 'mid', $mid);
		$this->setTitle($this->pl->txt('edit_video'));
	}

	protected function initForm() {

		// HIDDEN ID
		$input = new ilHiddenInputGUI('mid');
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
			$this->addItem($input);
		}

		// PUBLISHED (Zugriff)
		$input = new ilRadioGroupInputGUI($this->pl->txt('published'), 'published');
		$radio_item = new ilRadioOption($this->pl->txt('private'), xvmpMedium::PUBLISHED_PRIVATE);
		$input->addOption($radio_item);
		$radio_item = new ilRadioOption($this->lng->txt('public'), xvmpMedium::PUBLISHED_PUBLIC);
		$input->addOption($radio_item);
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

		$this->addCommandButtons();
	}

	public function fillForm() {
		$this->setValuesByArray($this->video);
	}


	public function saveForm() {
		if (!$this->checkInput()) {
			return false;
		}
		/** @var ilFormPropertyGUI $item */
		foreach ($this->getItems() as $item) {
			$this->video[$item->getPostVar()] = $this->getInput($item->getPostVar());
		}

		$video = new xvmpMedium();
		$video->buildObjectFromArray($this->video);
		$video->update();

		return true;
	}

	protected function addCommandButtons() {
		$this->addCommandButton(xvmpOwnVideosGUI::CMD_UPDATE_VIDEO, $this->lng->txt('save'));
		$this->addCommandButton(xvmpOwnVideosGUI::CMD_CANCEL, $this->lng->txt(xvmpOwnVideosGUI::CMD_CANCEL));
	}


	function setValuesByArray($a_values, $a_restrict_to_value_keys = false) {
		parent::setValuesByArray($a_values, $a_restrict_to_value_keys);
	}
}