<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class xvmpSearchVideosGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_isCalledBy xvmpSearchVideosGUI: ilObjViMPGUI
 */
class xvmpSearchVideosGUI extends xvmpVideosGUI {

	const SUBTAB_ACTIVE = xvmpVideosGUI::SUBTAB_SEARCH;

	const CMD_SHOW_FILTERED = 'showFiltered';
	const CMD_APPLY_FILTER = 'applyFilter';
	const CMD_RESET_FILTER = 'resetFilter';

	protected function index() {
		$xvmpSearchVideosTableGUI = new xvmpSearchVideosTableGUI($this, self::CMD_STANDARD);
		$this->tpl->setContent($xvmpSearchVideosTableGUI->getHTML());
	}

	protected function showFiltered() {
		$xvmpSearchVideosTableGUI = new xvmpSearchVideosTableGUI($this, self::CMD_STANDARD);
		$xvmpSearchVideosTableGUI->parseData();
		$this->tpl->setContent($xvmpSearchVideosTableGUI->getHTML());
	}

	public function applyFilter() {
		$xvmpSearchVideosTableGUI = new xvmpSearchVideosTableGUI($this, self::CMD_STANDARD);
		$xvmpSearchVideosTableGUI->resetOffset();
		$xvmpSearchVideosTableGUI->writeFilterToSession();
		$this->ctrl->redirect($this, self::CMD_SHOW_FILTERED);
	}

	public function resetFilter() {
		$xvmpSearchVideosTableGUI = new xvmpSearchVideosTableGUI($this, self::CMD_STANDARD);
		$xvmpSearchVideosTableGUI->resetOffset();
		$xvmpSearchVideosTableGUI->resetFilter();
		$this->ctrl->redirect($this, self::CMD_STANDARD);
	}

}