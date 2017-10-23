<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class xvmpVideosGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
abstract class xvmpVideosGUI extends xvmpGUI {

	const TAB_ACTIVE = ilObjViMPGUI::TAB_VIDEOS;
	const SUBTAB_ACTIVE = ''; // overwrite in subclass

	const SUBTAB_SEARCH = 'search_videos';
	const SUBTAB_SELECTED = 'selected_videos';
	const SUBTAB_OWN = 'own_videos';


	public function executeCommand() {
		$this->setSubTabs();
		$this->tabs->activateSubTab(static::SUBTAB_ACTIVE);
		parent::executeCommand();
	}

	/**
	 *
	 */
	protected function setSubTabs() {
		$this->tabs->addSubTab(self::SUBTAB_SEARCH, $this->pl->txt(self::SUBTAB_SEARCH), $this->ctrl->getLinkTargetByClass(xvmpSearchVideosGUI::class, xvmpSearchVideosGUI::CMD_STANDARD));
		$this->tabs->addSubTab(self::SUBTAB_SELECTED, $this->pl->txt(self::SUBTAB_SELECTED), $this->ctrl->getLinkTargetByClass(xvmpSelectedVideosGUI::class, xvmpSelectedVideosGUI::CMD_STANDARD));
		$this->tabs->addSubTab(self::SUBTAB_OWN, $this->pl->txt(self::SUBTAB_OWN), $this->ctrl->getLinkTargetByClass(xvmpOwnVideosGUI::class, xvmpOwnVideosGUI::CMD_STANDARD));
	}
}