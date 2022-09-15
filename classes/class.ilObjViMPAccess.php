<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Class ilObjViMPAccess
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ilObjViMPAccess extends ilObjectPluginAccess {

	const ACTION_ADD_VIDEO = 'add_video';
	const ACTION_REMOVE_VIDEO = 'remove_video';
	const ACTION_PLAY_VIDEO = 'play_video';
	const ACTION_DOWNLOAD_VIDEO = 'download_video';
	/**
	 * delete / edit / change owner
	 */
	const ACTION_MANIPULATE_VIDEO = 'manipulate_video'; // delete, edit, change owner

	const CONTEXT_OBJECT = 'context_object';
	const CONTEXT_PAGE_EDITOR = 'context_page_editor';

	/**
	 * @param string $a_cmd
	 * @param string $a_permission
	 * @param int $a_ref_id
	 * @param int $a_obj_id
	 * @param string $a_user_id
	 *
	 * @return bool
	 */
	public function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id = NULL, $a_user_id = '') {
		global $DIC;
		$ilUser = $DIC['ilUser'];
		$ilAccess = $DIC['ilAccess'];
		/**
		 * @var $ilAccess ilAccessHandler
		 */
		if ($a_user_id == '') {
			$a_user_id = $ilUser->getId();
		}
		if ($a_obj_id === NULL) {
			$a_obj_id = ilObject2::_lookupObjId($a_ref_id);
		}

		switch ($a_permission) {
			case 'read':
				if (!self::checkOnline($a_obj_id) AND !$ilAccess->checkAccessOfUser($a_user_id, 'write', '', $a_ref_id)) {
					return false;
				}
				break;
			case 'visible':
				if (!self::checkOnline($a_obj_id) AND !$ilAccess->checkAccessOfUser($a_user_id, 'write', '', $a_ref_id)) {
					return false;
				}
				break;
		}

		return true;
	}

	/**
	 * @param $ref_id
	 *
	 * @return bool
	 */
	public static function hasWriteAccess($ref_id = NULL) {
		if ($ref_id === NULL) {
			$ref_id = $_GET['ref_id'];
		}
		global $DIC;
		$ilAccess = $DIC['ilAccess'];

		/**
		 * @var $ilAccess ilAccesshandler
		 */

		return $ilAccess->checkAccess('write', '', $ref_id);
	}

	/**
	 * @param $ref_id
	 *
	 * @return bool
	 */
	public static function hasReadAccess($ref_id = NULL) {
		if ($ref_id === NULL) {
			$ref_id = $_GET['ref_id'];
		}
		global $DIC;
		$ilAccess = $DIC['ilAccess'];

		/**
		 * @var $ilAccess ilAccesshandler
		 */

		return $ilAccess->checkAccess('read', '', $ref_id);
	}

	/**
	 * @param $ref_id
	 *
	 * @return bool
	 */
	public static function hasUploadPermission($ref_id = NULL) {
		if ($ref_id === NULL) {
			$ref_id = $_GET['ref_id'];
		}
		global $DIC;
		$ilAccess = $DIC['ilAccess'];

		/**
		 * @var $ilAccess ilAccesshandler
		 */

		return $ilAccess->checkAccess('rep_robj_xvmp_perm_upload', '', $ref_id);
	}

	/**
	 * @param $ref_id
	 *
	 * @return bool
	 */
	public static function hasAccessToLink($ref_id = NULL) {
		if ($ref_id === NULL) {
			$ref_id = $_GET['ref_id'];
		}
		global $DIC;
		$ilAccess = $DIC['ilAccess'];

		/**
		 * @var $ilAccess ilAccesshandler
		 */

		return $ilAccess->checkAccess('rep_robj_xvmp_perm_readlink', '', $ref_id);
	}

	/**
	 * @param                 $action
	 * @param xvmpGUI         $GUI
	 * @param xvmpMedium|NULL $medium
	 */
	public static function checkAction($action, $GUI, xvmpMedium $medium = null) {
		if (ilObject2::_lookupType($_GET['ref_id'], true) == 'xvmp') {
			$context = self::CONTEXT_OBJECT;
		} else {
			$context = self::CONTEXT_PAGE_EDITOR;
		}

		if (!self::isActionAllowed($action, $GUI, $context, $medium)) {
			$GUI->accessDenied();
		}

	}


	/**
	 * @param                 $action
	 * @param                 $GUI
	 * @param                 $context
	 * @param xvmpMedium|NULL $medium
	 *
	 * @return bool
	 */
	public static function isActionAllowed($action, $GUI, $context, xvmpMedium $medium = null) {
		switch ($action) {
			case self::ACTION_PLAY_VIDEO:
				if ($medium->isPublic() || $medium->isCurrentUserOwner()) {
					return true;
				}
				if ($context == self::CONTEXT_OBJECT
                    && xvmpSelectedMedia::isSelected($medium->getId(), $GUI->getObjId())
                    && self::hasReadAccess()) {
					return true;
				}
				break;
            case self::ACTION_DOWNLOAD_VIDEO:
                if ($medium->isPublic() || $medium->isCurrentUserOwner()) {
                    return true;
                }
                if ($context == self::CONTEXT_OBJECT
                    && xvmpSelectedMedia::isSelected($medium->getId(), $GUI->getObjId())
                    && self::hasReadAccess()
                    && $medium->isDownloadAllowed()) {
                    return true;
                }
                break;
			case self::ACTION_ADD_VIDEO:
				if ($medium->isPublic() || $medium->isCurrentUserOwner() && (self::hasWriteAccess() || self::hasUploadPermission())) {
					return true;
				}
				break;
			case self::ACTION_REMOVE_VIDEO:
				if (self::hasWriteAccess() || self::hasUploadPermission()) {
					return true;
				}
				break;
			case self::ACTION_MANIPULATE_VIDEO:
				if ($medium->isCurrentUserOwner() && (self::hasWriteAccess() || self::hasUploadPermission())) {
					return true;
				}
				break;
		}
		return false;
	}

	/**
	 * @param $obj_id
	 *
	 * @return mixed
	 */
	public function checkOnline($obj_id) {
		return xvmpSettings::find($obj_id)->getIsOnline();
	}

}
