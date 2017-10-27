<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once __DIR__ . '/../vendor/autoload.php';
/**
 * Class ilObjViMP
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 *
 */
class ilObjViMP extends ilObjectPlugin {

	protected function initType() {
		$this->setType(ilViMPPlugin::XVMP);
	}


	protected function doCreate() {
		$xvmpSettings = new xvmpSettings();
		$xvmpSettings->setObjId($this->getId());
		$xvmpSettings->create();
	}


	protected function doDelete() {
		xvmpSettings::find($this->getId())->delete();
		foreach (xvmpSelectedMedia::where(array('obj_id' => $this->getId()))->get() as $selected_media) {
			$selected_media->delete();
		}
	}

}