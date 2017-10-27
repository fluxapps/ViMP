<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class xvmpFormGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
abstract class xvmpFormGUI extends ilPropertyFormGUI {

	/**
	 * @var xvmpGUI
	 */
	protected $parent_gui;
	/**
	 * @var ilViMPPlugin
	 */
	protected $pl;
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;
	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * xvmpFormGUI constructor.
	 */
	public function __construct($parent_gui) {
		global $ilCtrl, $lng;
		$this->parent_gui = $parent_gui;
		$this->pl = ilViMPPlugin::getInstance();
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;

		$this->setFormAction($this->ctrl->getFormAction($this->parent_gui));

		$this->initForm();
	}

	protected abstract function initForm();


}