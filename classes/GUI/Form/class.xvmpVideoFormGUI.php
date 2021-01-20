<?php

use ILIAS\DI\Container;
use ILIAS\Filesystem\Exception\IOException;
use ILIAS\FileUpload\Exception\IllegalStateException;

/**
 * Class xvmpVideoFormGUI
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
abstract class xvmpVideoFormGUI extends xvmpFormGUI
{
    const F_SOURCE_URL = 'source_url';

    /**
     * @var xvmpOwnVideosGUI | ilVimpPageComponentPluginGUI
     */
    protected $parent_gui;
    /**
     * @var array
     */
    protected $data = [];
    /**
     * @var Container
     */
    protected $dic;
    /**
     * @var xvmpUploadService
     */
    protected $upload_service;

    /**
     * xvmpVideoFormGUI constructor.
     * @param ilVimpPageComponentPluginGUI|xvmpOwnVideosGUI $parent_gui
     */
    public function __construct($parent_gui)
    {
        global $DIC;
        $this->dic = $DIC;
        $this->upload_service = new xvmpUploadService($DIC->filesystem(), $DIC->upload());
        $tmp_id = ilUtil::randomhash();
        $this->dic->ctrl()->setParameter($parent_gui, 'tmp_id', $tmp_id);
        parent::__construct($parent_gui);
        $this->addCommandButtons();
    }

    /**
     * @param string $post_var
     * @return string|null
     * @throws IllegalStateException
     * @throws IOException
     * @throws ilWACException
     * @throws xvmpException
     */
    protected function formatInput(string $post_var)
    {
        $value = $this->getInput($post_var);
        $tmp_id = filter_input(INPUT_GET, 'tmp_id', FILTER_SANITIZE_STRING);
        switch ($post_var) {
            case self::F_SOURCE_URL:
                /** @var array $value */
                if (!$value['name']) {
                    return null;
                }
                return $this->upload_service->getSignedUrl($value['name'], $tmp_id);
            case xvmpMedium::F_THUMBNAIL:
                if (!$_FILES[$post_var]['tmp_name']) {
                    return null;
                }
                $this->upload_service->moveUploadToWebDir($_FILES[$post_var]['tmp_name'], $tmp_id);
                return $this->upload_service->getSignedUrl($_FILES[$post_var]['name'], $tmp_id);
            case xvmpMedium::F_MEDIAPERMISSIONS:
                /** @var array $media_permissions */
                $media_permissions = $value;
                foreach (xvmpUserRoles::getAll() as $role) {
                    if ($role->isInvisibleDefault() && !in_array($role->getId(), $media_permissions)) {
                        $media_permissions[] = $role->getId();
                    }
                }

                return implode(',', $media_permissions);
            case xvmpMedium::F_PUBLISHED:
                return xvmpMedium::$published_id_mapping[$value];
            default:
                return is_array($value) ? implode(',', $value) : $value;
        }
    }

    /**
     * @param string $post_var
     * @return string|null
     */
    protected function mapPostVarToMediumField(string $post_var)
    {
        switch ($post_var) {
            case xvmpMedium::F_PUBLISHED:
                return xvmpMedium::PUBLISHED_HIDDEN;
            case self::F_SOURCE_URL:
            case xvmpMedium::F_MID:
            case xvmpMedium::F_TITLE;
            case xvmpMedium::F_DESCRIPTION;
            case xvmpMedium::F_CATEGORIES:
            case xvmpMedium::F_TAGS:
            case xvmpMedium::F_MEDIAPERMISSIONS:
            case xvmpMedium::F_THUMBNAIL:
                return $post_var;
            default:
                if (in_array($post_var, array_map(function(array $field) {
                    return $field[xvmpConf::F_FORM_FIELD_ID];
                }, xvmpConf::getConfig(xvmpConf::F_FORM_FIELDS)))) {
                    return $post_var;
                }
                return null;
        }
    }

    /**
     * @return bool
     */
    public function saveForm() : bool
    {
        if (!$this->checkInput()) {
            return false;
        }

        try {
            /** @var ilFormPropertyGUI $item */
            $this->fillVideoByPost();
            $this->storeVideo();
            $this->upload_service->cleanUp();
        } catch (Exception $e) {
            $this->upload_service->cleanUp();
            ilUtil::sendFailure($e->getMessage(), true);
            return false;
        }

        return true;
    }

    /**
     * @throws IOException
     * @throws IllegalStateException
     * @throws ilWACException
     * @throws xvmpException
     */
    protected function fillVideoByPost()
    {
        foreach ($this->getItems() as $item) {
            $post_var = rtrim($item->getPostVar(), '[]');
            $field = $this->mapPostVarToMediumField($post_var);
            if (!is_null($field)) {
                $this->data[$field] = $this->formatInput($post_var);
            }
        }
    }

    protected function addFormHeader(string $title)
    {
        $header = new ilFormSectionHeaderGUI();
        $header->setTitle($this->pl->txt('form_header_' . $title));
        $this->addItem($header);
    }

    protected function addHiddenIdInput()
    {
        $input = new ilHiddenInputGUI(xvmpMedium::F_MID);
        $this->addItem($input);
    }

    protected function addTitleInput()
    {
        $input = new ilTextInputGUI($this->pl->txt(xvmpMedium::F_TITLE), xvmpMedium::F_TITLE);
        $input->setRequired(true);
        $input->setMaxLength(128);
        $this->addItem($input);
    }

    protected function addDescriptionInput()
    {
        $input = new ilTextAreaInputGUI($this->pl->txt(xvmpMedium::F_DESCRIPTION), xvmpMedium::F_DESCRIPTION);
        $input->setRequired(true);
        $this->addItem($input);
    }


    protected function addFileInput(bool $required = true)
    {
        $input = new xvmpFileUploadInputGUI($this, xvmpOwnVideosGUI::CMD_CREATE, $this->lng->txt('file'),
            self::F_SOURCE_URL);
        $config = xvmpConfig::find('upload_max_size')->getValue();
        if ($config !== null) {
            $max_filesize_vimp = trim($config, "'");
        }
        $max_filesize_plugin = xvmpConf::getConfig(xvmpConf::F_UPLOAD_LIMIT);
        if ($max_filesize_vimp || $max_filesize_plugin) {
            $max_filesize_vimp = $this->getSizeInMB($max_filesize_vimp);
            if (!$max_filesize_vimp || !$max_filesize_plugin) {
                $max_filesize = max($max_filesize_vimp, $max_filesize_plugin);
            } else {
                $max_filesize = min($max_filesize_vimp, $max_filesize_plugin);
            }
            $input->setMaxFileSize($max_filesize . 'MB');
        }

        $suffixes = array(
            'mov',
            'mp4',
            'm4v',
            'flv',
            'mpeg',
            'avi',
        );
        $config = xvmpConfig::find('extension_whitelist_video')->getValue();
        if ($config !== null) {
            $suffixes = eval('return ' . $config . ';');
        }
        $input->setUrl($this->ctrl->getLinkTarget($this->parent_gui, xvmpOwnVideosGUI::CMD_UPLOAD_CHUNKS));
        $input->setSuffixes($suffixes);
        $input->setMimeTypes(array(
            'video/avi',
            'video/quicktime',
            'video/mpeg',
            'video/mp4',
            'video/ogg',
            'video/webm',
            'video/x-ms-wmv',
            'video/x-flv',
            'video/x-matroska',
            'video/x-msvideo',
            'video/x-dv',
        ));
        $input->setRequired($required);
        $this->addItem($input);
    }

    protected function addCategoriesInput()
    {
        $input = new ilMultiSelectSearchInputGUI($this->lng->txt(xvmpMedium::F_CATEGORIES), xvmpMedium::F_CATEGORIES);
        $categories = xvmpCategory::getAll();
        $options = array();
        /** @var xvmpCategory $category */
        foreach ($categories as $category) {
            $options[$category->getId()] = $category->getNameWithPath();
        }
        asort($options);
        $input->setOptions($options);
        $input->setRequired(true);
        $this->addItem($input);
    }

    protected function addTagsInput()
    {
        $input = new ilTextInputGUI($this->pl->txt(xvmpMedium::F_TAGS), xvmpMedium::F_TAGS);
        $input->setInfo($this->pl->txt(xvmpMedium::F_TAGS . '_info'));
        $input->setRequired(true);
        $this->addItem($input);
    }

    protected function addCustomInputs()
    {
        foreach (xvmpConf::getConfig(xvmpConf::F_FORM_FIELDS) as $field) {
            if (!$field[xvmpConf::F_FORM_FIELD_ID]) {
                continue;
            }
            if ($field[xvmpConf::F_FORM_FIELD_TYPE]) {
                $input = new ilCheckboxInputGUI($field[xvmpConf::F_FORM_FIELD_TITLE],
                    $field[xvmpConf::F_FORM_FIELD_ID]);
            } else {
                $input = new ilTextInputGUI($field[xvmpConf::F_FORM_FIELD_TITLE], $field[xvmpConf::F_FORM_FIELD_ID]);
            }
            $input->setRequired($field[xvmpConf::F_FORM_FIELD_REQUIRED]);
            $this->addItem($input);
        }
    }

    protected function addPublishedInput()
    {
        if (xvmp::isAllowedToSetPublic()) {
            $input = new ilRadioGroupInputGUI($this->pl->txt(xvmpMedium::F_PUBLISHED), xvmpMedium::F_PUBLISHED);
            $radio_item = new ilRadioOption($this->pl->txt(xvmpMedium::PUBLISHED_PUBLIC), xvmpMedium::PUBLISHED_PUBLIC);
            $radio_item->setInfo($this->pl->txt(xvmpMedium::PUBLISHED_PUBLIC . '_info'));
            $input->addOption($radio_item);
            $radio_item = new ilRadioOption($this->pl->txt(xvmpMedium::PUBLISHED_HIDDEN), xvmpMedium::PUBLISHED_HIDDEN);
            $radio_item->setInfo($this->pl->txt(xvmpMedium::PUBLISHED_HIDDEN . '_info'));
            $input->addOption($radio_item);
            $radio_item = new ilRadioOption($this->pl->txt(xvmpMedium::PUBLISHED_PRIVATE),
                xvmpMedium::PUBLISHED_PRIVATE);
            $radio_item->setInfo($this->pl->txt(xvmpMedium::PUBLISHED_PRIVATE . '_info'));
            $input->addOption($radio_item);
            $input->setRequired(true);
            $this->addItem($input);
        }
    }

    protected function addMediaPermissionsInput()
    {
        $media_permissions = xvmpConf::getConfig(xvmpConf::F_MEDIA_PERMISSIONS);
        if ($media_permissions) {
            $input = $this->getMediaPermissionsInput($media_permissions);
            if (!empty($input->getOptions())) {
                $this->addItem($input);
            }
        }
    }

    protected function getMediaPermissionsInput(int $media_permissions) : ilMultiSelectSearchInputGUI
    {
        $input = new ilMultiSelectSearchInputGUI($this->pl->txt(xvmpConf::F_MEDIA_PERMISSIONS),
            xvmpMedium::F_MEDIAPERMISSIONS);
        $input->setInfo($this->pl->txt(xvmpConf::F_MEDIA_PERMISSIONS . '_info'));
        $input->setRequired(true);
        $options = array();
        if ($media_permissions == xvmpConf::MEDIA_PERMISSION_SELECTION) {
            $selectable_roles = xvmpConf::getConfig(xvmpConf::F_MEDIA_PERMISSIONS_SELECTION);
        }
        foreach (xvmpUserRoles::getAll() as $role) {
            if (!$role->getField('visible') || ($selectable_roles && !in_array($role->getId(),
                        $selectable_roles))) {
                continue;
            }
            $options[$role->getId()] = $role->getName();
        }
        $input->setOptions($options);
        return $input;
    }

    protected function addThumbnailInput()
    {
        $input = new ilImageFileInputGUI($this->pl->txt(xvmpMedium::F_THUMBNAIL), xvmpMedium::F_THUMBNAIL);
        $this->addItem($input);
    }

    /**
     * @param $size
     * @return bool|float|int|string
     */
    protected function getSizeInMB($size) {
        switch (substr($size, -2)) {
            case 'GB':
                return substr($size, 0, (strlen($size) - 2)) * 1024;
            case 'MB':
                return substr($size, 0, (strlen($size) - 2));
            case 'KB':
                return substr($size, 0, (strlen($size) - 2)) / 1024;
            default:
                return 0;
        }
    }


    abstract protected function storeVideo();

    abstract public function fillForm();

    abstract protected function addCommandButtons();
}
