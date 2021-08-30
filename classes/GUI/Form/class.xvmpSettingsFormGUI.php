<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use srag\Plugins\ViMP\Database\Settings\SettingsAR;

/**
 * Class xvmpSettingsFormGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xvmpSettingsFormGUI extends xvmpFormGUI {


	const F_TITLE = 'title';
	const F_DESCRIPTION = 'description';
	const F_ONLINE = 'online';
	const F_LAYOUT = 'layout';
	const F_REPOSITORY_PREVIEW = 'repository_preview';
	const F_LEARNING_PROGRESS = 'enable_learning_progress';
	/**
	 * @var ilObjViMP
	 */
	protected $object;
	/**
	 * @var SettingsAR
	 */
	protected $settings;


	/**
	 * xvmpSettingsFormGUI constructor.
	 *
	 * @param $parent_gui
	 */
	public function __construct($parent_gui) {
        $this->object = $parent_gui->getObject();
        parent::__construct($parent_gui);
        $this->setTitle($this->lng->txt('settings'));
		$this->settings = SettingsAR::find($this->parent_gui->getObjId());
		$this->fillForm();
	}


	/**
	 *
	 */
	protected function initForm() {
		// TITLE
		$input = new ilTextInputGUI($this->pl->txt(self::F_TITLE), self::F_TITLE);
		$input->setRequired(true);
		$this->addItem($input);

		// DESCRIPTION
		$input = new ilTextInputGUI($this->pl->txt(self::F_DESCRIPTION), self::F_DESCRIPTION);
		$this->addItem($input);

		// ONLINE
		$input = new ilCheckboxInputGUI($this->lng->txt(self::F_ONLINE), self::F_ONLINE);
		$this->addItem($input);

		// LAYOUT
		$input = new ilRadioGroupInputGUI($this->pl->txt(self::F_LAYOUT), self::F_LAYOUT);
		$option = new ilRadioOption(ilUtil::img($this->pl->getImagePath(self::F_LAYOUT . '_' . SettingsAR::LAYOUT_TYPE_LIST . '.png')),SettingsAR::LAYOUT_TYPE_LIST);
		$input->addOption($option);
		$option = new ilRadioOption(ilUtil::img($this->pl->getImagePath(self::F_LAYOUT . '_' . SettingsAR::LAYOUT_TYPE_TILES . '.png')),SettingsAR::LAYOUT_TYPE_TILES);
		$input->addOption($option);
		$option = new ilRadioOption(ilUtil::img($this->pl->getImagePath(self::F_LAYOUT . '_' . SettingsAR::LAYOUT_TYPE_PLAYER . '.png')),SettingsAR::LAYOUT_TYPE_PLAYER);
		$input->addOption($option);
		$this->addItem($input);

		// REPOSITORY PREVIEW
		$input = new ilSelectInputGUI($this->pl->txt(self::F_REPOSITORY_PREVIEW), self::F_REPOSITORY_PREVIEW);
		$input->setOptions(array(
			0 => $this->pl->txt('no_preview'),
			1 => '1 ' . $this->pl->txt('video'),
			2 => '2 ' . $this->pl->txt('videos'),
			3 => '3 ' . $this->pl->txt('videos'),
		));
		$this->addItem($input);

		// LEARNING PROGRESS
		$input = new ilCheckboxInputGUI($this->pl->txt(self::F_LEARNING_PROGRESS), self::F_LEARNING_PROGRESS);
		$input->setInfo($this->pl->txt(self::F_LEARNING_PROGRESS . '_info'));
		$input->setDisabled(!xvmp::isLearningProgressPossible($this->parent_gui->getObjId()));
		$this->addItem($input);

		$this->dic->object()->commonSettings()->legacyForm($this, $this->object)->addTileImage();

		$this->initCommandButtons();
	}


	/**
	 *
	 */
	protected function initCommandButtons() {
		$this->addCommandButton(xvmpSettingsGUI::CMD_UPDATE, $this->lng->txt('save'));
		$this->addCommandButton(xvmpSettingsGUI::CMD_CANCEL, $this->lng->txt('cancel'));
	}


	/**
	 *
	 */
	public function fillForm() {
		$values = array(
			self::F_TITLE => $this->parent_gui->getObject()->getTitle(),
			self::F_DESCRIPTION => $this->parent_gui->getObject()->getDescription(),
			self::F_ONLINE => $this->settings->getIsOnline(),
			self::F_LAYOUT => $this->settings->getLayoutType(),
			self::F_REPOSITORY_PREVIEW => $this->settings->getRepositoryPreview(),
			self::F_LEARNING_PROGRESS => $this->settings->getLpActive()
		);
		$this->setValuesByArray($values);
	}


	/**
	 * @return bool
	 */
	public function saveForm() {
		if (!$this->checkInput()) {
			return false;
		}

		$this->object->setTitle($this->getInput(self::F_TITLE));
		$this->object->setDescription($this->getInput(self::F_DESCRIPTION));
		$this->object->update();

		$this->settings->setIsOnline($this->getInput(self::F_ONLINE));
		$this->settings->setLayoutType($this->getInput(self::F_LAYOUT));
		$this->settings->setRepositoryPreview($this->getInput(self::F_REPOSITORY_PREVIEW));
		$this->settings->setLpActive($this->getInput(self::F_LEARNING_PROGRESS));
		$this->settings->update();

        $this->dic->object()->commonSettings()->legacyForm($this, $this->object)->saveTileImage();

		return true;
	}
}
