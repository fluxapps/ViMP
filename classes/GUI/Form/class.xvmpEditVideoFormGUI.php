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
	 * @var xvmpOwnVideosGUI | ilVimpPageComponentPluginGUI
	 */
	protected $parent_gui;
	/**
	 * @var array
	 */
	protected $video;


	public function __construct($parent_gui, $mid) {
		// load the video from the api, not from the cache
		xvmpCacheFactory::getInstance()->delete(xvmpMedium::class . '-' . $mid);
		$this->video = xvmpMedium::getObjectAsArray($mid);

		parent::__construct($parent_gui);

		$this->ctrl->setParameter($this->parent_gui, xvmpMedium::F_MID, $mid);
		$this->setTitle($this->pl->txt('edit_video'));
	}

	protected function initForm() {

		// HIDDEN ID
		$input = new ilHiddenInputGUI(xvmpMedium::F_MID);
		$this->addItem($input);

		// TITLE
		$input = new ilTextInputGUI($this->pl->txt(xvmpMedium::F_TITLE), xvmpMedium::F_TITLE);
		$input->setRequired(true);
		$input->setMaxLength(128);
		$this->addItem($input);

		// DESCRIPTION
		$input = new ilTextAreaInputGUI($this->pl->txt(xvmpMedium::F_DESCRIPTION), xvmpMedium::F_DESCRIPTION);
		$input->setRequired(true);
		$this->addItem($input);

		// CATEGORIES
		$input = new ilMultiSelectSearchInputGUI($this->lng->txt(xvmpMedium::F_CATEGORIES), xvmpMedium::F_CATEGORIES);
		$categories = xvmpCategory::getAll();
		$options = array();
		/** @var xvmpCategory $category */
		foreach ($categories as $category) {
			$options[$category->getId()] = $category->getNameWithPath();
		}
		asort($options);
		$input->setOptions($options);
		$input->setRequired(true);
		$this->addItem($input);

		// TAGS
		$input = new ilTextInputGUI($this->pl->txt(xvmpMedium::F_TAGS), xvmpMedium::F_TAGS);
		$input->setInfo($this->pl->txt(xvmpMedium::F_TAGS . '_info'));
		$input->setRequired(true);
		$this->addItem($input);

		$this->addCommandButtons();
		// custom fields
		foreach (xvmpConf::getConfig(xvmpConf::F_FORM_FIELDS) as $field) {
			if (!$field[xvmpConf::F_FORM_FIELD_ID]) {
				continue;
			}
            if ($field[xvmpConf::F_FORM_FIELD_TYPE]) {
                $input = new ilCheckboxInputGUI($field[xvmpConf::F_FORM_FIELD_TITLE], $field[xvmpConf::F_FORM_FIELD_ID]);
            } else {
                $input = new ilTextInputGUI($field[xvmpConf::F_FORM_FIELD_TITLE], $field[xvmpConf::F_FORM_FIELD_ID]);
            }
            $input->setRequired($field[xvmpConf::F_FORM_FIELD_REQUIRED]);
			$this->addItem($input);
		}

		// PUBLISHED (Zugriff)
		if (xvmp::isAllowedToSetPublic()) {
			$input = new ilRadioGroupInputGUI($this->pl->txt(xvmpMedium::F_PUBLISHED), xvmpMedium::F_PUBLISHED);
			$radio_item = new ilRadioOption($this->pl->txt(xvmpMedium::PUBLISHED_PUBLIC), xvmpMedium::PUBLISHED_PUBLIC);
			$radio_item->setInfo($this->pl->txt(xvmpMedium::PUBLISHED_PUBLIC . '_info'));
			$input->addOption($radio_item);
			$radio_item = new ilRadioOption($this->pl->txt(xvmpMedium::PUBLISHED_HIDDEN), xvmpMedium::PUBLISHED_HIDDEN);
			$radio_item->setInfo($this->pl->txt(xvmpMedium::PUBLISHED_HIDDEN . '_info'));
			$input->addOption($radio_item);
			$radio_item = new ilRadioOption($this->pl->txt(xvmpMedium::PUBLISHED_PRIVATE), xvmpMedium::PUBLISHED_PRIVATE);
			$radio_item->setInfo($this->pl->txt(xvmpMedium::PUBLISHED_PRIVATE . '_info'));
			$input->addOption($radio_item);
			$this->addItem($input);
		}

		// MEDIA PERMISSIONS
		$media_permissions = xvmpConf::getConfig(xvmpConf::F_MEDIA_PERMISSIONS);
		if ($media_permissions) {
			$input = new ilMultiSelectSearchInputGUI($this->pl->txt(xvmpConf::F_MEDIA_PERMISSIONS), xvmpMedium::F_MEDIAPERMISSIONS);
			$input->setInfo($this->pl->txt(xvmpConf::F_MEDIA_PERMISSIONS . '_info'));
			$input->setRequired(true);
			$options = array();
			if ($media_permissions == xvmpConf::MEDIA_PERMISSION_SELECTION) {
				$selectable_roles = xvmpConf::getConfig(xvmpConf::F_MEDIA_PERMISSIONS_SELECTION);
			}
			foreach (xvmpUserRoles::getAll() as $role) {
                if (!$role->getField('visible') || ($selectable_roles && !in_array($role->getId(), $selectable_roles))) {
					continue;
				}
				$options[$role->getId()] = $role->getName();
			}
			$input->setOptions($options);
			if (!empty($options)) {
				$this->addItem($input);
			}
		}

	}

	public function fillForm() {
		$array = $this->video;
		$array[xvmpMedium::F_CATEGORIES] = array_keys($this->video[xvmpMedium::F_CATEGORIES]);
		$this->setValuesByArray($array);
	}


	public function saveForm() {
		if (!$this->checkInput()) {
			return false;
		}

		// store current state for changelog
		$old = $this->video;

		/** @var ilFormPropertyGUI $item */
		foreach ($this->getItems() as $item) {
			$post_var = rtrim($item->getPostVar(), '[]');
			$this->video[$post_var] = $this->getInput($post_var);
		}

        // add default & invisible roles as media permissions
        $media_permissions = is_array($this->video[xvmpMedium::F_MEDIAPERMISSIONS]) ? $this->video[xvmpMedium::F_MEDIAPERMISSIONS] : array();
        foreach (xvmpUserRoles::getAll() as $role) {
            if ($role->isInvisibleDefault() && !in_array($role->getId(), $media_permissions)) {
                $media_permissions[] = $role->getId();
            }
        }
        $this->video[xvmpMedium::F_MEDIAPERMISSIONS] = $media_permissions;

		$video = new xvmpMedium();
		$video->buildObjectFromArray($this->video);
		$video->update();

		// changelog entry
		xvmpCacheFactory::getInstance()->delete(xvmpMedium::class . '-' . $video->getMid());
		$new = xvmpMedium::getObjectAsArray($video->getMid());

		xvmpEventLog::logEvent(xvmpEventLog::ACTION_EDIT, $this->parent_gui->getObjId(), $new, $old);

		return true;
	}

	protected function addCommandButtons() {
		if ($this->parent_gui instanceof xvmpOwnVideosGUI) {
			$this->addCommandButton(xvmpOwnVideosGUI::CMD_UPDATE_VIDEO, $this->lng->txt('save'));
			$this->addCommandButton(xvmpOwnVideosGUI::CMD_CANCEL, $this->lng->txt(xvmpOwnVideosGUI::CMD_CANCEL));
		} else {
			$this->addCommandButton(ilVimpPageComponentPluginGUI::CMD_STANDARD, $this->lng->txt('save'));
			$this->addCommandButton(ilVimpPageComponentPluginGUI::CMD_OWN_VIDEOS, $this->lng->txt(xvmpOwnVideosGUI::CMD_CANCEL));
		}
	}


	function setValuesByArray($a_values, $a_restrict_to_value_keys = false) {
		parent::setValuesByArray($a_values, $a_restrict_to_value_keys);
	}
}