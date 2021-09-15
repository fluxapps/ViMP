<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use srag\Plugins\ViMP\Service\ViMPDBService;

/**
 * Class xvmpEditVideoFormGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xvmpEditVideoFormGUI extends xvmpVideoFormGUI {

	/**
	 * @var xvmpOwnVideosGUI | ilVimpPageComponentPluginGUI
	 */
	protected $parent_gui;
    /**
     * @var array
     */
	protected $medium;

    /** @var ViMPDBService */
    protected $db_service;


	public function __construct($parent_gui, $mid) {
        global $DIC;
        $this->db_service = new ViMPDBService($DIC);
		// load the video from the api, not from the cache
		xvmpCacheFactory::getInstance()->delete(xvmpMedium::class . '-' . $mid);
		$this->medium = xvmpMedium::getObjectAsArray($mid);

		parent::__construct($parent_gui);

		$this->ctrl->setParameter($this->parent_gui, xvmpMedium::F_MID, $mid);
		$this->setTitle($this->pl->txt('edit_video'));
	}



    protected function initForm() {
        $this->addHiddenIdInput();

        $this->addTitleInput();
        $this->addDescriptionInput();
        if (xvmp::ViMPVersionGreaterEquals('4.4.0')) {
            $this->addFileInput(false);
        }

        $this->addFormHeader('metadata');
        $this->addCategoriesInput();
        $this->addTagsInput();
        $this->addCustomInputs();

        $this->addFormHeader('access');
        $this->addPublishedInput();
        $this->addMediaPermissionsInput();

        if (xvmp::ViMPVersionGreaterEquals('4.4.0')) {
            $this->addFormHeader('additional_options');
            $this->addThumbnailInput();
            if (xvmp::ViMPVersionGreaterEquals('4.4.1')) {
                $this->addSubtitleInput();
            }
        }
	}

	public function fillForm() {
		$array = $this->medium;
		$array[xvmpMedium::F_CATEGORIES] = array_keys($this->medium[xvmpMedium::F_CATEGORIES]);
        $subtitles = $this->medium[xvmpMedium::F_SUBTITLES] ?? [];
        foreach ($subtitles as $lang_key => $subtitle_url) {
            $array[xvmpMedium::F_SUBTITLES . '_' . $lang_key] = substr($subtitle_url, strrpos($subtitle_url, '/') + 1);
        }
		$this->setValuesByArray($array);

		// fill thumbnail
        if ($this->medium[xvmpMedium::F_THUMBNAIL]) {
            $item = $this->getItemByPostVar(xvmpMedium::F_THUMBNAIL);
            if ($item instanceof ilImageFileInputGUI) {
                $item->setImage($this->medium[xvmpMedium::F_THUMBNAIL]);
            }
        }
	}


	public function saveForm() : bool
    {
		if (parent::saveForm()) {
            // changelog entry
            xvmpCacheFactory::getInstance()->delete(xvmpMedium::class . '-' . $this->medium['mid']);
            $this->db_service->logUpdate($this->parent_gui->getObjId(), $this->medium);
            return true;
        }

        return false;
	}

    protected function storeVideo() : int
    {
        xvmpMedium::update($this->data);
        $this->upload_service->cleanUp();
        return $this->data['mid'];
    }

    protected function addCommandButtons() {
		if ($this->parent_gui instanceof xvmpOwnVideosGUI) {
			$this->addCommandButton(xvmpOwnVideosGUI::CMD_UPDATE_VIDEO, $this->lng->txt('save'));
			$this->addCommandButton(xvmpOwnVideosGUI::CMD_CANCEL, $this->lng->txt(xvmpOwnVideosGUI::CMD_CANCEL));
		} else {
			$this->addCommandButton(ilVimpPageComponentPluginGUI::CMD_STANDARD, $this->lng->txt('save'));
			$this->addCommandButton(ilVimpPageComponentPluginGUI::CMD_OWN_VIDEOS, $this->lng->txt(xvmpOwnVideosGUI::CMD_CANCEL));
		}
	}
}
