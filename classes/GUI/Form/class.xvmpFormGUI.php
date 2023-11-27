<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\DI\Container;

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
	protected ilCtrl $ctrl;
	/**
	 * @var ilLanguage
	 */
	protected ilLanguage $lng;
    /**
     * @var Container
     */
    protected $dic;

	/**
	 * xvmpFormGUI constructor.
	 */
	public function __construct($parent_gui) {
		global $DIC;
		$ilCtrl = $DIC['ilCtrl'];
		$lng = $DIC['lng'];
		$this->parent_gui = $parent_gui;
		$this->pl = ilViMPPlugin::getInstance();
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
		$this->dic = $DIC;

		$this->setFormAction($this->ctrl->getFormAction($this->parent_gui));

		$this->initForm();
	}

	protected abstract function initForm();


}
