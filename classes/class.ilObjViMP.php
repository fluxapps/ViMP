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
}