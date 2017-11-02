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
		if (!$this->ctrl->isAsynch() && ilObjViMPAccess::hasWriteAccess()) {
			$this->addFlushCacheButton();
		}


		switch (xvmpSettings::find($this->getObjId())->getLayoutType()) {
			case xvmpSettings::LAYOUT_TYPE_LIST:
//				$this->tpl->addJavaScript($this->pl->getDirectory() . '/templates/default/xvmp_content.js');
//				$this->tpl->addJavaScript($this->pl->getDirectory() . '/templates/default/waiter.js');
//				$this->tpl->addCss($this->pl->getDirectory() . '/templates/default/waiter.css');
//				$this->tpl->addCss($this->pl->getDirectory() . '/templates/default/xvmp_content_table.css');
//				$modal = $this->getModalPlayer();
//				$this->tpl->setContent('<div id="xvmp_table_placeholder"></div>' . $modal->getHTML());
//				$this->addOnLoadAjaxCode();
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
			xvmpMedium::$thumb_size = $template == 'list' ? '280x220' : '210x150';
			$video = xvmpMedium::find($mid);
			$tpl = new ilTemplate("tpl.content_{$template}.html", true, true, $this->pl->getDirectory());

			$tpl->setVariable('MID', $mid);
			$tpl->setVariable('THUMBNAIL', $video->getThumbnail());
			$tpl->setVariable('LABEL_TITLE', $this->pl->txt('title'));
			$tpl->setVariable('TITLE', $video->getTitle());
			$tpl->setVariable('LABEL_DESCRIPTION', $this->pl->txt('description'));
			$tpl->setVariable('DESCRIPTION', strip_tags($video->getDescription()));
			$tpl->setVariable('LABEL_DURATION', $this->pl->txt('duration'));
			$tpl->setVariable('DURATION', $video->getDurationFormatted());
			$tpl->setVariable('LABEL_AUTHOR', $this->pl->txt('author'));
			$tpl->setVariable('AUTHOR', $video->getCustomAuthor());
			$tpl->setVariable('LABEL_CREATED_AT', $this->pl->txt('created_at'));
			$tpl->setVariable('CREATED_AT', $video->getCreatedAt('d.m.Y, H:i'));

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
        $modal->setBody('
			<div id="xoct_waiter" class="xoct_waiter xoct_waiter_modal"></div>
			<section></section>');
		return $modal;
	}


	/**
	 * ajax
	 */
	public function fillModalPlayer() {
		$mid = $_GET['mid'];
		$video = xvmpMedium::find($mid);
		$video_infos = "				
			<h3>{$video->getDescription()}</h3>
			<p>{$this->pl->txt('duration')}: {$video->getDurationFormatted()}</p>
			<p>{$this->pl->txt('author')}: {$video->getCustomAuthor()}</p>
			<p>{$this->pl->txt('created_at')}: {$video->getCreatedAt('m.d.Y, H:i')}</p>";
		$response = new stdClass();
		$response->html = $video->getEmbedCode() . $video_infos;
		$response->video_title = $video->getTitle();
		echo json_encode($response);
		exit;
	}

}