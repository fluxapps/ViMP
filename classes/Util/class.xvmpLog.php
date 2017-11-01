<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once('./Services/Logging/classes/class.ilLog.php');

/**
 * Class xvmpLog
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xvmpLog extends ilLog {

	const LOG_TITLE = 'vimp.log';

	/**
	 * @var xoctLog
	 */
	protected static $instance;

	/**
	 * @return xvmpLog
	 */
	public static function getInstance() {
		if (! isset(self::$instance)) {
			self::$instance = new self(ILIAS_LOG_DIR, self::LOG_TITLE);
		}

		return self::$instance;
	}

	public function writeTrace() {
		try {
			throw new Exception();
		} catch (Exception $e) {
			parent::write($e->getTraceAsString());
		}
	}


	/**
	 * @return mixed
	 */
	public function getLogDir() {
		return ILIAS_LOG_DIR;
	}

	/**
	 * @return string
	 */
	public static function getFullPath() {
		$log = self::getInstance();

		return $log->getLogDir() . '/' . $log->getLogFile();
	}

	/**
	 * @return string
	 */
	public function getLogFile() {
		return self::LOG_TITLE;
	}


}