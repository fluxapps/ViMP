<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class xvmpDeletedMedium
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xvmpDeletedMedium extends xvmpMedium {

	public function __construct() {
		$this->title = $this->getTitle();
		$this->description = $this->getDescription();
		$this->duration = 0;
		$this->duration_formatted = '';
		$this->thumbnail = $this->getThumbnail();
		$this->medium = $this->getMedium();
		$this->created_at = $this->getCreatedAt();
	}


	/**
	 * @return String
	 */
	public function getTitle() {
		return ilViMPPlugin::getInstance()->txt('not_available');
	}


	/**
	 * @return String
	 */
	public function getDescription() {
		return ilViMPPlugin::getInstance()->txt('not_available_description');
	}


	/**
	 * @return int
	 */
	public function getDuration() {
		return 0;
	}


	/**
	 * @return String
	 */
	public function getDurationFormatted() {
		return '';
	}


	/**
	 * @return String
	 */
	public function getThumbnail($width = 0, $height = 0) {
		return ilViMPPlugin::getInstance()->getDirectory() . '/templates/images/not_available.png';
	}


	/**
	 * @return array
	 */
	public function getMedium() {
		return '';
	}


	/**
	 * @return String
	 */
	public function getCreatedAt($format = '') {
		return '';
	}

}