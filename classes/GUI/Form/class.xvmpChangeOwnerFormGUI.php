<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class xvmpChangeOwnerFormGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xvmpChangeOwnerFormGUI extends xvmpFormGUI {

	/**
	 * @var
	 */
	protected $mid;
	/**
	 * @var mixed
	 */
	protected $global_tpl;
	/**
	 * @var xvmpOwnVideosGUI
	 */
	protected $parent_gui;


	/**
	 * xvmpChangeOwnerFormGUI constructor.
	 *
	 * @param $parent_gui xvmpOwnVideosGUI
	 * @param $mid
	 */
	public function __construct($parent_gui, $mid) {
		global $DIC;
		$this->global_tpl = $DIC['tpl'];
		parent::__construct($parent_gui);
		$this->mid = $mid;

		$this->setTitle($this->pl->txt('form_title_change_owner'));

		$this->ctrl->setParameterByClass(xvmpOwnVideosGUI::class, 'mid', $this->mid);
		$this->setFormAction($this->ctrl->getFormAction($this->parent_gui));
		$this->addCommandButton(xvmpOwnVideosGUI::CMD_CHANGE_OWNER, $this->lng->txt('save'));
		$this->addCommandButton(xvmpOwnVideosGUI::CMD_STANDARD,$this->lng->txt('cancel'));
	}


	/**
	 *
	 */
	protected function initForm() {
		$input = new ilTextInputGUI($this->pl->txt('username'), 'login');
		$input->setRequired(true);
		$input->setInfo($this->pl->txt('info_autocomplete'));
		$input->setDataSource($this->ctrl->getLinkTargetByClass(array(
			ilUIPluginRouterGUI::class,
			ilViMPPlugin::class
		), ilViMPPlugin::CMD_ADD_USER_AUTO_COMPLETE, "", true));

		$this->addItem($input);
	}
}