<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use Detection\MobileDetect;

/**
 * Class xvmpContentGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_isCalledBy xvmpContentGUI: ilObjViMPGUI
 */
class xvmpContentGUI extends xvmpGUI {

	const TAB_ACTIVE = ilObjViMPGUI::TAB_CONTENT;

	const CMD_SHOW_MODAL_PLAYER = 'showModalPlayer';
	const CMD_RENDER_ITEM = 'renderItem';
	const CMD_RENDER_TILE_SMALL = 'renderTileSmall';

	/**
	 *
	 */
	protected function index() {
//		try {
			xvmpRequest::version();
//		} catch (xvmpException $e) {
//
//		}

		xvmpVideoPlayer::loadVideoJSAndCSS(true);

		if (!$this->ctrl->isAsynch() && ilObjViMPAccess::hasWriteAccess()) {
			$this->addFlushCacheButton();
		}
		$detect_mobile = new MobileDetect();

//		$layout_type = $detect_mobile->isMobile() ? xvmpSettings::LAYOUT_TYPE_TILES : xvmpSettings::find($this->getObjId())->getLayoutType();
		$layout_type = xvmpSettings::find($this->getObjId())->getLayoutType();

		switch ($layout_type) {
			case xvmpSettings::LAYOUT_TYPE_LIST:
				$xvmpContentListGUI = new xvmpContentListGUI($this);
				$xvmpContentListGUI->show();
				break;
			case xvmpSettings::LAYOUT_TYPE_TILES:
				$xvmpContentTilesGUI = new xvmpContentTilesGUI($this);
				$xvmpContentTilesGUI->show();
				break;
			case xvmpSettings::LAYOUT_TYPE_PLAYER:
				$xvmpContentPlayerGUI = new xvmpContentPlayerGUI($this);
				$xvmpContentPlayerGUI->show();
				break;
		}
	}


	/**
	 *
	 */
	public function renderItem() {
		$mid = $_GET['mid'];
		$template = $_GET['tpl'];
		try {
			$video = xvmpMedium::find($mid);
			$tpl = new ilTemplate("tpl.content_{$template}.html", true, true, $this->pl->getDirectory());

			$tpl->setVariable('MID', $mid);
			$tpl->setVariable('THUMBNAIL', $video->getThumbnail());
			$tpl->setVariable('TITLE', $video->getTitle());
			$tpl->setVariable('DESCRIPTION', strip_tags($video->getDescription(50)));

			if (!$video instanceof xvmpDeletedMedium) {
				$tpl->setVariable('LABEL_TITLE', $this->pl->txt('title') . ':');
				$tpl->setVariable('LABEL_DESCRIPTION', $this->pl->txt('description') . ':');
				$tpl->setVariable('LABEL_DURATION', $this->pl->txt('duration') . ':');
				$tpl->setVariable('DURATION', $video->getDurationFormatted());
				$tpl->setVariable('LABEL_AUTHOR', $this->pl->txt('author') . ':');
				$tpl->setVariable('AUTHOR', $video->getCustomAuthor());
				$tpl->setVariable('LABEL_CREATED_AT', $this->pl->txt('created_at') . ':');
				$tpl->setVariable('CREATED_AT', $video->getCreatedAt('d.m.Y, H:i'));
				$tpl->setVariable('LABEL_WATCHED', $this->pl->txt('watched') . ':');
				$tpl->setVariable('WATCHED', xvmpUserProgress::calcPercentage($this->user->getId(), $mid) . '%');
			}

			echo $tpl->get();
			exit;
		} catch (xvmpException $e) {
			exit;
		}
	}

}