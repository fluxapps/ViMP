<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once 'Services/Table/classes/class.ilTable2GUI.php';
/**
 * Class xvmpSearchVideosTableGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xvmpSearchVideosTableGUI extends ilTable2GUI {

	/**
	 * xvmpSearchVideosTableGUI constructor.
	 */
	public function __construct(xvmpSearchVideosGUI $parent_gui, $parent_cmd, $obj_id) {
		global $ilCtrl;
		$this->ctrl = $ilCtrl;
		$this->pl = ilViMPPlugin::getInstance();
		$this->setPrefix(ilViMPPlugin::XVMP . '_');
		$this->setId($obj_id);

		parent::__construct($parent_gui, $parent_cmd);

		
	}


}