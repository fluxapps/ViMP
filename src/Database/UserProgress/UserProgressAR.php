<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace srag\Plugins\ViMP\Database\UserProgress;

use ActiveRecord;
use xvmpMedium;
use srag\Plugins\ViMP\Database\UserLPStatus\UserLPStatusAR;

/**
 * Class xvmpUserProgress
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class UserProgressAR extends ActiveRecord {
	const DB_TABLE_NAME = 'xvmp_user_progress';

	const F_USR_ID = 'usr_id';
	const F_MID = xvmpMedium::F_MID;
	const F_RANGES = 'ranges';
	const F_TOTAL_WATCHED = 'total_watched';
	const F_VIDEO_DURATION = 'video_duration';


    /**
     * @return string|void
     */
    public static function returnDbTableName() {
		return self::DB_TABLE_NAME;
	}


	/**
	 * @var int
	 *
	 * @db_has_field        true
	 * @db_is_unique        true
	 * @db_is_primary       true
	 * @db_fieldtype        integer
	 * @db_length           8
	 * @con_sequence        true
	 */
	protected $id = 0;
	/**
	 * @var int
	 *
	 * @db_has_field        true
	 * @db_fieldtype        integer
	 * @db_length           8
	 */
	protected $usr_id;
	/**
	 * @var int
	 *
	 * @db_has_field        true
	 * @db_fieldtype        integer
	 * @db_length           8
	 */
	protected $mid;
	/**
	 * @var String
	 *
	 * @db_has_field        true
	 * @db_fieldtype        clob
	 */
	protected $ranges = '[]';
	/**
	 * @var int
	 *
	 * @db_has_field        true
	 * @db_fieldtype        integer
	 * @db_length           8
	 */
	protected $total_watched;
	/**
	 * @var int
	 *
	 * @db_has_field        true
	 * @db_fieldtype        integer
	 */
	protected $video_duration;


	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}


	/**
	 * @param int $id
	 */
	public function setId($id) {
		$this->id = $id;
	}


	/**
	 * @return int
	 */
	public function getUsrId() {
		return $this->usr_id;
	}


	/**
	 * @param int $usr_id
	 */
	public function setUsrId($usr_id) {
		$this->usr_id = $usr_id;
	}


	/**
	 * @return int
	 */
	public function getMid() {
		return $this->mid;
	}


	/**
	 * @param int $mid
	 */
	public function setMid($mid) {
		$this->mid = $mid;
	}


	/**
	 * @return String
	 */
	public function getRanges() {
		return $this->ranges;
	}


	/**
	 * @param String $ranges
	 */
	public function setRanges($ranges) {
		$this->ranges = $ranges;
	}


	/**
	 * @return int
	 */
	public function getVideoDuration() {
		return $this->video_duration;
	}


	/**
	 * @param int $video_duration
	 */
	public function setVideoDuration($video_duration) {
		$this->video_duration = $video_duration;
	}


	/**
	 * @return int
	 */
	public function getTotalWatched() {
		return $this->total_watched;
	}


	/**
	 * @param int $total_watched
	 */
	public function setTotalWatched($total_watched) {
		$this->total_watched = $total_watched;
	}


	/**
	 * @param $usr_id
	 * @param $mid
	 * @param $ranges
	 */
	public static function storeProgress($usr_id, $mid, $ranges) {
		$progress = self::where(array('usr_id' => $usr_id, 'mid' => $mid))->first();
		if (!$progress) {
			$progress = new self();
			$progress->setUsrId($usr_id);
			$progress->setMid($mid);
			$progress->setVideoDuration(xvmpMedium::getObjectAsArray($mid)['duration']);
		}
		$progress->setRanges($ranges);
		$progress->store();
	}


    /**
     *
     */
    public function store() {
		$this->calcTotalWatched();
		parent::store();

		// learning progress
		UserLPStatusAR::updateLPStatuses(0, true, $this->getUsrId());
	}

    /**
     *
     */
    protected function calcTotalWatched() {
		$ranges = json_decode($this->getRanges());
		$watched_seconds = 0;
		foreach ($ranges as $range) {
			$watched_seconds += ceil($range->e - $range->s);
		}
		$this->total_watched = min($watched_seconds, $this->getVideoDuration());
	}

	/**
	 * @param $usr_id
	 * @param $mid
	 *
	 * @return float|int
	 */
	public static function calcPercentage($usr_id, $mid) {
		/** @var self $progress */
		$progress = self::where(array('usr_id' => $usr_id, 'mid' => $mid))->first();
		if (!$progress || ($progress->getTotalWatched() == 0)) {
			return 0;
		}
		return ceil(($progress->getTotalWatched() / $progress->getVideoDuration()) * 100);
	}


}