<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

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
	/**
	 * @var ilObjViMP
	 */
	protected $object;
	/**
	 * @var xvmpSettings
	 */
	protected $settings;


	/**
	 * xvmpSettingsFormGUI constructor.
	 *
	 * @param $parent_gui
	 */
	public function __construct($parent_gui) {
		parent::__construct($parent_gui);
		$this->setTitle($this->lng->txt('settings'));
		$this->object = $this->parent_gui->getObject();
		$this->settings = xvmpSettings::find($this->parent_gui->getObjId());
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
		$option = new ilRadioOption(ilUtil::img($this->pl->getImagePath(self::F_LAYOUT . '_' . xvmpSettings::LAYOUT_TYPE_LIST . '.png')),xvmpSettings::LAYOUT_TYPE_LIST);
		$input->addOption($option);
		$option = new ilRadioOption(ilUtil::img($this->pl->getImagePath(self::F_LAYOUT . '_' . xvmpSettings::LAYOUT_TYPE_TILES . '.png')),xvmpSettings::LAYOUT_TYPE_TILES);
		$input->addOption($option);
		$option = new ilRadioOption(ilUtil::img($this->pl->getImagePath(self::F_LAYOUT . '_' . xvmpSettings::LAYOUT_TYPE_PLAYER . '.png')),xvmpSettings::LAYOUT_TYPE_PLAYER);
		$input->addOption($option);
		$this->addItem($input);

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
		$this->settings->update();

		return true;
	}
}