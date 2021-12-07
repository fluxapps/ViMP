<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class xvmpUploadVideoFormGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xvmpUploadVideoFormGUI extends xvmpVideoFormGUI {

	const F_ADD_AUTOMATICALLY = 'add_automatically';
	const F_NOTIFICATION = 'notification';

	/**
	 * @var ilLanguage
	 */
	protected $lng;
	/**
	 * @var ilViMPPlugin
	 */
	protected $pl;
	/**
	 * @var xvmpOwnVideosGUI
	 */
	protected $parent_gui;
	/**
	 * @var ilObjUser
	 */
	protected $user;


	/**
	 * xvmpUploadVideoFormGUI constructor.
	 *
	 * @param $parent_gui
	 */
	public function __construct($parent_gui) {
		global $DIC;
		$ilUser = $DIC['ilUser'];
		$this->user = $ilUser;

		$this->setId('xoct_event');

		parent::__construct($parent_gui);

		$this->setTitle($this->pl->txt('upload_video'));
		$this->setTarget('_top');
	}


	/**
	 *
	 */
	protected function initForm() {
		$this->setFormAction($this->ctrl->getFormAction($this->parent_gui));

		$this->addHiddenIdInput();

        $this->addTitleInput();
        $this->addDescriptionInput();
        $this->addFileInput();

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
        $this->addFormHeader('notification');

        // NOTIFICATION
        $input = new ilCheckboxInputGUI($this->pl->txt(self::F_NOTIFICATION), self::F_NOTIFICATION);
        $input->setInfo($this->pl->txt(self::F_NOTIFICATION . '_info'));
        $this->addItem($input);

        // ADD AUTOMATICALLY
        $input = new ilCheckboxInputGUI($this->pl->txt(self::F_ADD_AUTOMATICALLY), self::F_ADD_AUTOMATICALLY);
        $input->setInfo($this->pl->txt(self::F_ADD_AUTOMATICALLY . '_info'));
        $this->addItem($input);
	}


    protected function addCustomInputs()
    {
        foreach (xvmpConf::getConfig(xvmpConf::F_FORM_FIELDS) as $field) {
            if (!$field[xvmpConf::F_FORM_FIELD_ID]) {
                continue;
            }

            $lng_title = $this->lng->exists($this->pl->getPrefix() . "_" . $field[xvmpConf::F_FORM_FIELD_ID])
                ? $this->lng->txt($this->pl->getPrefix() . "_" . $field[xvmpConf::F_FORM_FIELD_ID])
                : $field[xvmpConf::F_FORM_FIELD_TITLE];
            if ($field[xvmpConf::F_FORM_FIELD_TYPE]) {
                $input = new ilCheckboxInputGUI($lng_title, $field[xvmpConf::F_FORM_FIELD_ID]);
            } else {
                $input = new ilTextInputGUI($lng_title, $field[xvmpConf::F_FORM_FIELD_ID]);
            }

            $input->setRequired($field[xvmpConf::F_FORM_FIELD_REQUIRED]);
            if ($field[xvmpConf::F_FORM_FIELD_FILL_USER_DATA]) {
                $input->setValue($this->user->getFirstname() . ' ' . $this->user->getLastname());
            }
            $this->addItem($input);
        }
    }

    public function saveForm() : bool
    {
        if (parent::saveForm()) {
            // the object has to be loaded again, since the response from "upload" has another format for the categories
            // also, this adds it to the cache
            $video = xvmpMedium::getObjectAsArray($this->data[xvmpMedium::F_MID]);
            xvmpEventLog::logEvent(xvmpEventLog::ACTION_UPLOAD, $this->parent_gui->getObjId(), $video);
            return true;
        }

		return false;
	}

    protected function storeVideo() : int
    {
        $this->data = xvmpMedium::upload(
            $this->data,
            $this->parent_gui->getObjId(),
            (int) $this->getInput(self::F_ADD_AUTOMATICALLY),
            (int) $this->getInput(self::F_NOTIFICATION)
        );
        return $this->data['mid'];
    }

    protected function fillVideoByPost()
    {
        parent::fillVideoByPost();
        if (!xvmp::isAllowedToSetPublic()) {
            if (in_array(xvmpConf::getConfig(xvmpConf::F_DEFAULT_PUBLICATION),
                array_values(xvmpMedium::$published_id_mapping))) {
                $this->data[xvmpMedium::PUBLISHED_HIDDEN] = xvmpConf::getConfig(xvmpConf::F_DEFAULT_PUBLICATION);
            } else {
                $this->data[xvmpMedium::PUBLISHED_HIDDEN] = xvmpMedium::$published_id_mapping[xvmpMedium::PUBLISHED_HIDDEN];
            }
        }
        $this->data['uid'] = xvmpUser::getOrCreateVimpUser($this->user)->getUid();
    }

    public function fillForm()
    {
        $array = array();
        if (in_array(xvmpConf::getConfig(xvmpConf::F_DEFAULT_PUBLICATION),
            array_values(xvmpMedium::$published_id_mapping))) {
            $array[xvmpMedium::F_PUBLISHED] = array_keys(xvmpMedium::$published_id_mapping)[xvmpConf::getConfig(xvmpConf::F_DEFAULT_PUBLICATION)];
        }

        if (xvmpConf::getConfig(xvmpConf::F_MEDIA_PERMISSIONS_PRESELECTED)) {
            $selectable_roles = xvmpConf::getConfig(xvmpConf::F_MEDIA_PERMISSIONS_SELECTION);
            $array[xvmpMedium::F_MEDIAPERMISSIONS . '[]'] = $selectable_roles;
        }

        $this->setValuesByArray($array, true);
    }

    /**
     *
     */
    protected function addCommandButtons() {
        $this->addCommandButton(xvmpOwnVideosGUI::CMD_CREATE, $this->lng->txt('save'));
        $this->addCommandButton(xvmpOwnVideosGUI::CMD_CANCEL, $this->lng->txt(xvmpOwnVideosGUI::CMD_CANCEL));
    }
}
