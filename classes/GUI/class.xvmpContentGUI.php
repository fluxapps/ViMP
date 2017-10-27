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
	const CMD_RENDER_TILE = 'renderTile';
	const CMD_RENDER_TILE_SMALL = 'renderTileSmall';

	/**
	 *
	 */
	protected function index() {
		switch (xvmpSettings::find($this->getObjId())->getLayoutType()) {
			case xvmpSettings::LAYOUT_TYPE_LIST:
				$this->tpl->addJavaScript($this->pl->getDirectory() . '/templates/default/xvmp_content.js');
				$this->tpl->addJavaScript($this->pl->getDirectory() . '/templates/default/waiter.js');
				$this->tpl->addCss($this->pl->getDirectory() . '/templates/default/waiter.css');
				$this->tpl->addCss($this->pl->getDirectory() . '/templates/default/xvmp_content_table.css');
				$modal = $this->getModalPlayer();
				$this->tpl->setContent('<div id="xvmp_table_placeholder"></div>' . $modal->getHTML());
				$this->addOnLoadAjaxCode();
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
	public function renderTile() {
		$mid = $_GET['mid'];
		try {
			xvmpMedium::$thumb_size = '210x150';
			$video = xvmpMedium::find($mid);
			$tpl = new ilTemplate('tpl.content_tiles.html', true, true, $this->pl->getDirectory());

			$tpl->setVariable('MID', $mid);
			$tpl->setVariable('THUMBNAIL', $video->getThumbnail());
			$tpl->setVariable('MID', $video->getId());
			$tpl->setVariable('TITLE', $video->getTitle());
			$tpl->setVariable('DESCRIPTION', $video->getDescription());
			$tpl->setVariable('DURATION', $video->getDuration());

//			$js = "$('#xvmp_modal_player_{$mid}').find('.modal-body').addClass('waiting');";
			$js = "VimpContent.embed_codes[" . $video->getId() . "] = '" . $video->getEmbedCode() . "';";
			$js .= "VimpContent.video_titles[" . $video->getId() . "] = '" . $video->getTitle() . "';";
			$tpl->setVariable('JS', $js);

			$modal = ilModalGUI::getInstance();
			$modal->setId('xvmp_modal_player_' . $mid);
			$modal->setHeading($video->getTitle());
			$modal->setBody($video->getEmbedCode());
//			$modal->setBody('<div id="xoct_waiter" class="xoct_waiter xoct_waiter_modal"></div><section></section>');
//			$tpl->setVariable('MODAL', $modal->getHTML());
//			$tpl->setVariable('MODAL_LINK', 'data-toggle="modal" data-target="#xvmp_modal_player_' . $mid . '"');
			echo $tpl->get();
			exit;
		} catch (xvmpException $e) {
			exit;
		}
	}

	/**
	 *
	 */
	public function renderTileSmall() {
		$mid = $_GET['mid'];
		try {
			xvmpMedium::$thumb_size = '210x150';
			$video = xvmpMedium::find($mid);
			$tpl = new ilTemplate('tpl.content_tiles_small.html', true, true, $this->pl->getDirectory());

			$tpl->setVariable('MID', $mid);
			$tpl->setVariable('THUMBNAIL', $video->getThumbnail());
			$tpl->setVariable('MID', $video->getId());
			$tpl->setVariable('TITLE', $video->getTitle());

			echo $tpl->get();
			exit;
		} catch (xvmpException $e) {
			exit;
		}
	}

	protected function addOnLoadAjaxCode() {
		$ajax_link = $this->ctrl->getLinkTarget($this, 'asyncGetTableGUI', "", true);

		$ajax = "$.ajax({
				    url: '{$ajax_link}',
				    dataType: 'html',
				    success: function(data){
				        $('div#xvmp_table_placeholder').replaceWith($(data));
				    }
				});";
		$this->tpl->addOnLoadCode('xoctWaiter.show();');
		$this->tpl->addOnLoadCode($ajax);
	}


	/**
	 * ajax
	 */
	public function asyncGetTableGUI() {
		$xvmpContentTableGUI = new xvmpContentTableGUI($this, self::CMD_STANDARD);
		$xvmpContentTableGUI->parseData();
		echo $xvmpContentTableGUI->getHTML();
		exit();
	}


	/**
	 * @return ilModalGUI
	 */
	public function getModalPlayer() {
		$modal = ilModalGUI::getInstance();
		$modal->setId('xvmp_modal_player');
		$modal->setType(ilModalGUI::TYPE_LARGE);
        $modal->setBody('<div id="xoct_waiter" class="xoct_waiter xoct_waiter_modal"></div><section></section>');
		return $modal;
	}

}