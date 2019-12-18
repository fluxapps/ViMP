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
	const CMD_DELIVER_VIDEO = 'deliverVideo';
	const CMD_PLAY_VIDEO = 'playVideo';
    const GET_TEMPLATE = 'tpl';


    /**
	 *
	 */
	protected function index() {
		xvmpVideoPlayer::loadVideoJSAndCSS(!xvmpConf::getConfig(xvmpConf::F_EMBED_PLAYER));

		if (!$this->ctrl->isAsynch() && ilObjViMPAccess::hasWriteAccess()) {
			$this->addFlushCacheButton();
		}

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


	protected function performCommand($cmd) {
		switch ($cmd) {
			case self::CMD_RENDER_ITEM:
				$mid = $_GET['mid'];
				if (!$mid || !xvmpSelectedMedia::isSelected($mid, $this->getObjId())) {
					$this->accessDenied();
				}
				break;
		}
		parent::performCommand($cmd);
	}


    /**
     * used for goto link
     */
	public function playVideo() {
	    $mid = filter_input(INPUT_GET, ilObjViMPGUI::GET_VIDEO_ID, FILTER_SANITIZE_NUMBER_INT);
	    if ($mid) {
	        $this->tpl->addOnLoadCode('VimpContent.playVideo(' . $mid . ');');
        }
        $this->index();
    }

	/**
	 * ajax
	 */
	public function renderItem() {
        $mid = filter_input(INPUT_GET, ilObjViMPGUI::GET_VIDEO_ID, FILTER_SANITIZE_NUMBER_INT);
        $template = filter_input(INPUT_GET, self::GET_TEMPLATE, FILTER_SANITIZE_STRING);
		try {
			$video = xvmpMedium::find($mid);
			$tpl = new ilTemplate("tpl.content_{$template}.html", true, true, $this->pl->getDirectory());

			$tpl->setVariable('MID', $mid);
			$tpl->setVariable('THUMBNAIL', $video->getThumbnail());
			$tpl->setVariable('TITLE', $video->getTitle());
			$tpl->setVariable('DESCRIPTION', strip_tags($video->getDescription(50)));

			if (!$video instanceof xvmpDeletedMedium) {

				if ($video->getStatus() !== 'legal') {
					$tpl->setCurrentBlock('info_transcoding');
					$tpl->setVariable('INFO_TRANSCODING', $this->pl->txt('info_transcoding_short'));
					$tpl->parseCurrentBlock();
				}

				$tpl->setVariable('LABEL_TITLE', $this->pl->txt( xvmpMedium::F_TITLE) . ':');
				$tpl->setVariable('LABEL_DESCRIPTION', $this->pl->txt(xvmpMedium::F_DESCRIPTION) . ':');
				$tpl->setVariable('LABEL_DURATION', $this->pl->txt(xvmpMedium::F_DURATION) . ':');
				$tpl->setVariable('DURATION', $video->getDurationFormatted());
				$tpl->setVariable('LABEL_CREATED_AT', $this->pl->txt(xvmpMedium::F_CREATED_AT) . ':');
				$tpl->setVariable('CREATED_AT', $video->getCreatedAt('d.m.Y, H:i'));
				if (xvmp::showWatched($this->getObjId(), $video)) {
					$tpl->setVariable('LABEL_WATCHED', $this->pl->txt('watched') . ':');
					$tpl->setVariable('WATCHED', xvmpUserProgress::calcPercentage($this->user->getId(), $mid) . '%');
				}
			}

			echo $tpl->get();
			exit;
		} catch (xvmpException $e) {
			exit;
		}
	}


	/**
	 *
	 */
	public function deliverVideo() {
		$mid = $_GET['mid'];
		$video = xvmpMedium::find($mid);
		$medium = $video->getMedium();
		if (is_array($medium)) {
			$medium = $medium[0];
		}
//		$ctype= 'video/mp4';
//		header('Content-Type: ' . $ctype);
//		$handle = fopen($video->getMedium(), "rb");
//		fpassthru($handle);
////		$contents = fread($handle, filesize(()));
//		fclose($handle);
////		echo $contents;
//		header("Location: " . $medium);
//		exit;

		// TODO: this request fetches the filesize. Cache filesize to reduce loading time
		ini_set('max_execution_time', 0);
		$useragent = "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.96 Safari/537.36";
		$v = $medium;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 222222);
		curl_setopt($ch, CURLOPT_URL, $v);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
		if (xvmpConf::getConfig(xvmpConf::F_DISABLE_VERIFY_PEER)) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		}
		curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
		curl_setopt($ch, CURLOPT_NOBODY, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$info = curl_exec($ch);
		$size2 = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
		header("Content-Type: video/mp4");


		$filesize = $size2;
		$offset = 0;
		$length = $filesize;
		if (isset($_SERVER['HTTP_RANGE'])) {
			$partialContent = "true";
			preg_match('/bytes=(\d+)-(\d+)?/', $_SERVER['HTTP_RANGE'], $matches);
			$offset = intval($matches[1]);
			$length = $size2 - $offset - 1;
		} else {
			$partialContent = "false";
		}
		if ($partialContent == "true") {
			header('HTTP/1.1 206 Partial Content');
			header('Accept-Ranges: bytes');
			header('Content-Range: bytes '.$offset.
				'-'.($offset + $length).
				'/'.$filesize);
		} else {
			header('Accept-Ranges: bytes');
		}
		header("Content-length: ".$size2);


		$ch = curl_init();
		if (isset($_SERVER['HTTP_RANGE'])) {
			// if the HTTP_RANGE header is set we're dealing with partial content
			$partialContent = true;
			// find the requested range
			// this might be too simplistic, apparently the client can request
			// multiple ranges, which can become pretty complex, so ignore it for now
			preg_match('/bytes=(\d+)-(\d+)?/', $_SERVER['HTTP_RANGE'], $matches);
			$offset = intval($matches[1]);
			$length = $filesize - $offset - 1;
			$headers = array(
				'Range: bytes='.$offset.
				'-'.($offset + $length).
				''
			);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 222222);
		curl_setopt($ch, CURLOPT_URL, $v);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
		curl_setopt($ch, CURLOPT_NOBODY, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_exec($ch);
		exit;
//		echo $out;
	}
}