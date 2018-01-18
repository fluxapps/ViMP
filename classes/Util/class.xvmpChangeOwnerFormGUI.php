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
	 * xvmpChangeOwnerGUI constructor.
	 */
	public function __construct($parent_gui, $mid) {
		global $DIC;
		$this->global_tpl = $DIC['tpl'];
		parent::__construct($parent_gui);
		$this->mid = $mid;

		$this->setTitle($this->pl->txt('form_title_change_owner'));

		$this->global_tpl->addJavaScript($this->pl->getDirectory() . '/js/xvmp_change_owner.js');

		$this->ctrl->setParameterByClass(ilObjViMPGUI::class, 'mid', $this->mid);
		$ajax_url = $this->ctrl->getLinkTargetByClass(ilObjViMPGUI::class, ilObjViMPGUI::CMD_SEARCH_USER_AJAX, '', true);
		$this->global_tpl->addOnLoadCode("VimpChangeOwner.ajax_base_url = '" . $ajax_url . "';");

		$this->ctrl->setParameterByClass(xvmpOwnVideosGUI::class, 'mid', $this->mid);
		$url = $this->ctrl->getLinkTargetByClass(xvmpOwnVideosGUI::class, xvmpOwnVideosGUI::CMD_CHANGE_OWNER);
		$this->global_tpl->addOnLoadCode("VimpChangeOwner.base_url = '" . $url . "';");

		$this->setFormAction($this->ctrl->getFormAction($this->parent_gui));
		$this->addCommandButton(xvmpOwnVideosGUI::CMD_STANDARD,$this->lng->txt('cancel'));
	}


	/**
	 *
	 */
	protected function initForm() {
		// Search Field & Button
		$input = new ilCustomInputGUI();
		$input->setTitle($this->pl->txt('username'));
		$input->setHtml(
			"<input type='text' id='xvmp_username' onkeypress='if (event.keyCode==13) {VimpChangeOwner.search_user();return false;}'>
					<a class='btn btn-default' id='xvmp_search' onclick='VimpChangeOwner.search_user()'>" . $this->pl->txt('search') . "</a>
					<span id='xvmp_search_results' style='clear: both;display:block;'></span>
					<img id='xvmp_spinner' hidden height='20px' width='20px' src='Customizing/global/plugins/Services/Repository/RepositoryObject/ViMP/templates/images/spinner.gif'>
					");
		$this->addItem($input);


	}
}