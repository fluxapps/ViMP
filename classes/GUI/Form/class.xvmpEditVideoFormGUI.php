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
		$this->formatVideo();

		parent::__construct($parent_gui);

		$this->ctrl->setParameter($this->parent_gui, 'mid', $mid);
		$this->setTitle($this->pl->txt('edit_video'));
	}

	protected function initForm() {
		$required_metada = xvmpConf::getConfig(xvmpConf::F_REQUIRED_METADATA);

		// HIDDEN ID
		$input = new ilHiddenInputGUI('mid');
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


	/**
	 * some attributes have to be formatted to fill the form correctly
	 */
	protected function formatVideo() {
		foreach (array(array('categories', 'category', 'cid'), array('tags', 'tag', 'tid')) as $labels) {
			$array = array();
			if (isset($this->video[$labels[0]][$labels[1]][$labels[2]])) {
				$this->video[$labels[0]][$labels[1]] = array( $this->video[$labels[0]][$labels[1]] );
			}
			foreach ($this->video[$labels[0]][$labels[1]] as $item) {
				$array[$item[$labels[2]]] = $item['name'];
			}
			$this->video[$labels[0]] = $labels[0] == 'tags' ? implode(', ', $array) : $array;
		}
	}


	function setValuesByArray($a_values, $a_restrict_to_value_keys = false) {
		$a_values['categories'] = array_keys($a_values['categories']);
		parent::setValuesByArray($a_values, $a_restrict_to_value_keys);
	}
}