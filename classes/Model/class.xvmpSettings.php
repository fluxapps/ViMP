<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class xvmpSettings
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xvmpSettings extends ActiveRecord {

	const DB_TABLE_NAME = 'xvmp_setting';

	public static function returnDbTableName() {
		return self::DB_TABLE_NAME;
	}

}