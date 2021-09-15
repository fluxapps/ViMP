<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use srag\Plugins\ViMP\Database\Config\ConfigAR;
use srag\Plugins\ViMP\Service\Utils\ViMPTrait;

/**
 * Class xvmpConfFormGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xvmpConfFormGUI extends xvmpFormGUI {

    use ViMPTrait;
	/**
	 * @var ilViMPConfigGUI
	 */
	protected $parent_gui;
	/**
	 * @var ilDB
	 */
	protected $db;

	/**
	 * xvmpConfFormGUI constructor.
	 *
	 * @param $parent_gui
	 */
	public function __construct($parent_gui) {
		global $DIC;
		$tpl = $DIC['tpl'];
		parent::__construct($parent_gui);

		$this->db = $DIC['ilDB'];
		$tpl->addJavaScript($this->pl->getAssetURL('js/xvmp_config.js'));
	}

    /**
     *
     */
    protected function initForm(){
		$this->setFormAction($this->ctrl->getFormAction($this->parent_gui));

		// *** API SETTINGS ***
		$header = new ilFormSectionHeaderGUI();
		$header->setTitle($this->pl->confTxt('api_settings'));
		$this->addItem($header);

		// API User
		$input = new ilTextInputGUI($this->pl->confTxt(ConfigAR::F_API_USER), ConfigAR::F_API_USER);
		$input->setInfo($this->pl->confTxt(ConfigAR::F_API_USER . '_info'));
		$input->setRequired(true);
		$this->addItem($input);

		// API Password
		$input = new ilTextInputGUI($this->pl->confTxt(ConfigAR::F_API_PASSWORD), ConfigAR::F_API_PASSWORD);
		$input->setInfo($this->pl->confTxt(ConfigAR::F_API_PASSWORD . '_info'));
		$input->setRequired(true);
		$this->addItem($input);

		// API Key
		$input = new ilTextInputGUI($this->pl->confTxt(ConfigAR::F_API_KEY), ConfigAR::F_API_KEY);
		$input->setInfo($this->pl->confTxt(ConfigAR::F_API_KEY . '_info'));
		$input->setRequired(true);
		$this->addItem($input);

		// API Url
		$input = new ilTextInputGUI($this->pl->confTxt(ConfigAR::F_API_URL), ConfigAR::F_API_URL);
		$input->setInfo($this->pl->confTxt(ConfigAR::F_API_URL . '_info'));
		$input->setRequired(true);
		$this->addItem($input);

		// Test Connection Button
		$input = new ilCustomInputGUI();
		$input->setTitle('');
		$input->setHtml(
			"<a class='btn btn-default' id='xvmp_test_connection' onclick='VimpConfig.test_connection(event)' href='".$this->ctrl->getLinkTargetByClass(array('ilAdministrationGUI', 'ilObjViMPGUI'), 'testConnectionAjax', '', true)."'>Test Connection</a>
					<span id='xvmp_connection_status' style='margin-left: 5px'></span>");
		$this->addItem($input);

		// ignore ssl
	    $input = new ilCheckboxInputGUI($this->pl->confTxt(ConfigAR::F_DISABLE_VERIFY_PEER), ConfigAR::F_DISABLE_VERIFY_PEER);
	    $input->setInfo($this->pl->confTxt(ConfigAR::F_DISABLE_VERIFY_PEER . '_info'));
	    $this->addItem($input);

		// External User Mapping
		$input = new ilTextInputGUI($this->pl->confTxt(ConfigAR::F_USER_MAPPING_EXTERNAL), ConfigAR::F_USER_MAPPING_EXTERNAL);
		$input->setInfo($this->pl->confTxt(ConfigAR::F_USER_MAPPING_EXTERNAL . '_info'));
		$input->setRequired(true);
		$this->addItem($input);

		// Local User Mapping
		$input = new ilTextInputGUI($this->pl->confTxt(ConfigAR::F_USER_MAPPING_LOCAL), ConfigAR::F_USER_MAPPING_LOCAL);
		$input->setInfo($this->pl->confTxt(ConfigAR::F_USER_MAPPING_LOCAL . '_info'));
		$input->setRequired(true);
		$this->addItem($input);

		// Mapping Priority
		$input = new ilRadioGroupInputGUI($this->pl->confTxt(ConfigAR::F_MAPPING_PRIORITY), ConfigAR::F_MAPPING_PRIORITY);
		$input->setInfo($this->pl->confTxt(ConfigAR::F_MAPPING_PRIORITY . '_info'));
		$opt = new ilRadioOption($this->pl->confTxt(ConfigAR::F_MAPPING_PRIORITY . '_' . ConfigAR::PRIORITIZE_EMAIL), ConfigAR::PRIORITIZE_EMAIL);
		$input->addOption($opt);
		$opt = new ilRadioOption($this->pl->confTxt(ConfigAR::F_MAPPING_PRIORITY . '_' . ConfigAR::PRIORITIZE_MAPPING), ConfigAR::PRIORITIZE_MAPPING);
		$input->addOption($opt);
		$this->addItem($input);

		// *** GENERAL SETTINGS ***
		$header = new ilFormSectionHeaderGUI();
		$header->setTitle($this->pl->confTxt('general_settings'));
		$this->addItem($header);

		// Upload Limit
        $input = new ilNumberInputGUI($this->pl->confTxt(ConfigAR::F_UPLOAD_LIMIT), ConfigAR::F_UPLOAD_LIMIT);
        $input->setInfo($this->pl->confTxt(ConfigAR::F_UPLOAD_LIMIT . '_info'));
        $this->addItem($input);

		// Object Title
		$input = new ilTextInputGUI($this->pl->confTxt(ConfigAR::F_OBJECT_TITLE), ConfigAR::F_OBJECT_TITLE);
		$input->setRequired(true);
		$input->setInfo($this->pl->confTxt(ConfigAR::F_OBJECT_TITLE . '_info'));
		$this->addItem($input);

		// Default Publication
                $input = new ilSelectInputGUI($this->pl->confTxt(ConfigAR::F_DEFAULT_PUBLICATION), ConfigAR::F_DEFAULT_PUBLICATION);
                $input->setOptions(array(
                        0 => $this->pl->txt('public'),
                        1 => $this->pl->txt('private'),
                        2 => $this->pl->txt('hidden'),
                ));

                $input->setInfo($this->pl->confTxt(ConfigAR::F_DEFAULT_PUBLICATION . '_info'));
                $this->addItem($input);

		// Allow Public Upload
		$input = new ilCheckboxInputGUI($this->pl->confTxt(ConfigAR::F_ALLOW_PUBLIC), ConfigAR::F_ALLOW_PUBLIC);
		$input->setInfo($this->pl->confTxt(ConfigAR::F_ALLOW_PUBLIC . '_info'));

        $input2 = new ilCheckboxInputGUI($this->pl->confTxt(ConfigAR::F_ALLOW_PUBLIC_UPLOAD), ConfigAR::F_ALLOW_PUBLIC_UPLOAD);
        $input2->setInfo($this->pl->confTxt(ConfigAR::F_ALLOW_PUBLIC_UPLOAD . '_info'));
        $input->addSubItem($input2);

        $this->addItem($input);


        // Form Fields
		$input = new srGenericMultiInputGUI($this->pl->confTxt(ConfigAR::F_FORM_FIELDS), ConfigAR::F_FORM_FIELDS);
		$input->setAllowEmptyFields(true);
		$input->setRequired(false);
		$input->setInfo($this->pl->confTxt(ConfigAR::F_FORM_FIELDS . '_info'));
		$subinput = new ilTextInputGUI('', ConfigAR::F_FORM_FIELD_ID);
		$input->addInput($subinput);
		$subinput = new ilTextInputGUI('', ConfigAR::F_FORM_FIELD_TITLE);
		$input->addInput($subinput);
		$subinput = new ilCheckboxInputGUI('', ConfigAR::F_FORM_FIELD_REQUIRED);
		$input->addInput($subinput);
		$subinput = new ilCheckboxInputGUI('', ConfigAR::F_FORM_FIELD_FILL_USER_DATA);
		$input->addInput($subinput);
		$subinput = new ilCheckboxInputGUI('', ConfigAR::F_FORM_FIELD_SHOW_IN_PLAYER);
		$input->addInput($subinput);
		$subinput = new ilSelectInputGUI('', ConfigAR::F_FORM_FIELD_TYPE);
		$subinput->setOptions(array(
		    0 => $this->pl->confTxt('form_field_type_' . ConfigAR::F_FORM_FIELD_TYPE_TEXT),
		    1 => $this->pl->confTxt('form_field_type_' . ConfigAR::F_FORM_FIELD_TYPE_CHECKBOX)
        ));
		$input->addInput($subinput);
		$this->addItem($input);

		// Filter Fields
		$input = new srGenericMultiInputGUI($this->pl->confTxt(ConfigAR::F_FILTER_FIELDS), ConfigAR::F_FILTER_FIELDS);
		$input->setRequired(false);
		$input->setAllowEmptyFields(true);
		$input->setInfo($this->pl->confTxt(ConfigAR::F_FILTER_FIELDS . '_info'));
		$subinput = new ilTextInputGUI('', ConfigAR::F_FILTER_FIELD_ID);
		$input->addInput($subinput);
		$subinput = new ilTextInputGUI('', ConfigAR::F_FILTER_FIELD_TITLE);
		$input->addInput($subinput);
		$this->addItem($input);


		// Media Permission
		$input = new ilRadioGroupInputGUI($this->pl->confTxt(ConfigAR::F_MEDIA_PERMISSIONS), ConfigAR::F_MEDIA_PERMISSIONS);
		$input->setInfo($this->pl->confTxt(ConfigAR::F_MEDIA_PERMISSIONS . '_info'));

        $radio_option = new ilRadioOption($this->lng->txt('no'), ConfigAR::MEDIA_PERMISSION_OFF);
        $radio_option->setInfo($this->pl->confTxt(ConfigAR::F_MEDIA_PERMISSIONS . '_' . ConfigAR::MEDIA_PERMISSION_OFF . '_info'));
        $input->addOption($radio_option);

        $radio_option = new ilRadioOption($this->pl->txt('all'), ConfigAR::MEDIA_PERMISSION_ON);
		$input->addOption($radio_option);

		$radio_option = new ilRadioOption($this->pl->txt('selection'), ConfigAR::MEDIA_PERMISSION_SELECTION);
		$sub_selection = new ilMultiSelectSearchInputGUI('', ConfigAR::F_MEDIA_PERMISSIONS_SELECTION);
		$options = $this->getMediaPermissionOptions();
		$sub_selection->setOptions($options);
		$sub_selection->setDisabled(empty($options));
		$radio_option->addSubItem($sub_selection);

        // Media Permission Preselection
        $sub_input = new ilCheckboxInputGUI($this->pl->confTxt(ConfigAR::F_MEDIA_PERMISSIONS_PRESELECTED), ConfigAR::F_MEDIA_PERMISSIONS_PRESELECTED);
        $sub_input->setInfo($this->pl->confTxt(ConfigAR::F_MEDIA_PERMISSIONS_PRESELECTED . '_info'));
        $radio_option->addSubItem($sub_input);

        $input->addOption($radio_option);
        $this->addItem($input);


        // Embedded Player
		$input = new ilCheckboxInputGUI($this->pl->confTxt(ConfigAR::F_EMBED_PLAYER), ConfigAR::F_EMBED_PLAYER);
		$input->setInfo($this->pl->confTxt(ConfigAR::F_EMBED_PLAYER . '_info'));
		$this->addItem($input);


		// *** NOTIFICATION ***
		$header = new ilFormSectionHeaderGUI();
		$header->setTitle($this->pl->txt('notification'));
		$this->addItem($header);

		// Noticiation Subject
		$input = new ilTextInputGUI($this->pl->confTxt(ConfigAR::F_NOTIFICATION_SUBJECT_SUCCESSFULL), ConfigAR::F_NOTIFICATION_SUBJECT_SUCCESSFULL);
		$input->setInfo($this->pl->confTxt(ConfigAR::F_NOTIFICATION_SUBJECT_SUCCESSFULL . '_info'));
		$input->setRequired(true);
		$this->addItem($input);

		// Noticiation Body
		$input = new ilTextAreaInputGUI($this->pl->confTxt(ConfigAR::F_NOTIFICATION_BODY_SUCCESSFULL), ConfigAR::F_NOTIFICATION_BODY_SUCCESSFULL);
		$input->setInfo($this->pl->confTxt(ConfigAR::F_NOTIFICATION_BODY_SUCCESSFULL . '_info'));
		$input->setRequired(true);
		$this->addItem($input);

		// Noticiation Subject
		$input = new ilTextInputGUI($this->pl->confTxt(ConfigAR::F_NOTIFICATION_SUBJECT_FAILED), ConfigAR::F_NOTIFICATION_SUBJECT_FAILED);
		$input->setInfo($this->pl->confTxt(ConfigAR::F_NOTIFICATION_SUBJECT_FAILED . '_info'));
		$input->setRequired(true);
		$this->addItem($input);

		// Noticiation Body
		$input = new ilTextAreaInputGUI($this->pl->confTxt(ConfigAR::F_NOTIFICATION_BODY_FAILED), ConfigAR::F_NOTIFICATION_BODY_FAILED);
		$input->setInfo($this->pl->confTxt(ConfigAR::F_NOTIFICATION_BODY_FAILED . '_info'));
		$input->setRequired(true);
		$this->addItem($input);


		// *** CACHE ***
		$header = new ilFormSectionHeaderGUI();
		$header->setTitle($this->pl->confTxt('cache'));
		$header->setInfo($this->pl->confTxt('cache_info'));
		$this->addItem($header);

		// Video Cache TTL
		$input = new ilNumberInputGUI($this->pl->confTxt(ConfigAR::F_CACHE_TTL_VIDEOS), ConfigAR::F_CACHE_TTL_VIDEOS);
		$input->setInfo($this->pl->confTxt(ConfigAR::F_CACHE_TTL_VIDEOS . '_info'));
		$this->addItem($input);

		// User Cache TTL
		$input = new ilNumberInputGUI($this->pl->confTxt(ConfigAR::F_CACHE_TTL_USERS), ConfigAR::F_CACHE_TTL_USERS);
		$input->setInfo($this->pl->confTxt(ConfigAR::F_CACHE_TTL_USERS . '_info'));
		$this->addItem($input);

		// Category Cache TTL
		$input = new ilNumberInputGUI($this->pl->confTxt(ConfigAR::F_CACHE_TTL_CATEGORIES), ConfigAR::F_CACHE_TTL_CATEGORIES);
		$input->setInfo($this->pl->confTxt(ConfigAR::F_CACHE_TTL_CATEGORIES . '_info'));
		$this->addItem($input);

		// Token Cache TTL
		$input = new ilNumberInputGUI($this->pl->confTxt(ConfigAR::F_CACHE_TTL_TOKEN), ConfigAR::F_CACHE_TTL_TOKEN);
		$input->setInfo($this->pl->confTxt(ConfigAR::F_CACHE_TTL_TOKEN . '_info'));
		$this->addItem($input);

		// Config TTL
		$input = new ilNumberInputGUI($this->pl->confTxt(ConfigAR::F_CACHE_TTL_CONFIG), ConfigAR::F_CACHE_TTL_CONFIG);
		$input->setInfo($this->pl->confTxt(ConfigAR::F_CACHE_TTL_CONFIG . '_info'));
		$this->addItem($input);


		// Buttons
		$this->addCommandButton(ilViMPConfigGUI::CMD_UPDATE,$this->lng->txt('save'));
	}


    /**
     * @return array
     */
    protected function getMediaPermissionOptions() {
		$options = array();
		if ($this->pl->hasConnection()) {
			$roles = xvmpUserRoles::getAll();
			foreach ($roles as $role) {
				$options[$role->getId()] = $role->getName();
			}
		}

		return $options;
	}

    /**
     *
     */
    public function fillForm() {
		$array = array();
		foreach ($this->getItems() as $item) {
			$this->getValuesForItem($item, $array);
		}
		$this->setValuesByArray($array);
	}


	/**
	 * @param $item
	 * @param $array
	 *
	 * @internal param $key
	 */
	private function getValuesForItem($item, &$array) {
		if (self::checkItem($item)) {
			$key = rtrim($item->getPostVar(), '[]');
			if ($key == ConfigAR::F_OBJECT_TITLE) {
				$sql = $this->db->query('select value from lng_data where module = "rep_robj_xvmp" and identifier = "rep_robj_xvmp_obj_xvmp"');
				$value = $this->db->fetchObject($sql)->value;
			} else {
				$value = self::viMP()->config()->getValueByKey($key);
			}
			$array[$key] = $value;
			if (self::checkForSubItem($item)) {
				foreach ($item->getSubItems() as $subitem) {
					$this->getValuesForItem($subitem, $array);
				}
				if ($item instanceof ilRadioGroupInputGUI) {
					foreach ($item->getOptions() as $option) {
						foreach ($option->getSubItems() as $subitem) {
							$this->getValuesForItem($subitem, $array);
						}
					}
				}
			}
		}
	}


	/**
	 * @param $item
	 */
	private function saveValueForItem($item) {
		if (self::checkItem($item)) {
			$key = rtrim($item->getPostVar(), '[]');
			$value = $this->getInput($key);

			// exception: object title is stored in lng_data, not in config table
			if ($key == ConfigAR::F_OBJECT_TITLE) {
				// obj
				$sql = $this->db->query('select value from lng_data where module = "rep_robj_xvmp" and identifier = "rep_robj_xvmp_obj_xvmp"');
				$existing = $this->db->fetchObject($sql);

				if ($existing) {
					$this->db->update('lng_data',array(
						'value' => array('text', $value)
					), array(
						'module' => array('text', 'rep_robj_xvmp'),
						'identifier' => array('text', 'rep_robj_xvmp_obj_xvmp'),
					));
				} else {
					$this->db->insert('lng_data',array(
						'lang_key' => array('text', 'de'),
						'module' => array('text', 'rep_robj_xvmp'),
						'identifier' => array('text', 'rep_robj_xvmp_obj_xvmp'),
						'value' => array('text', $value)
					));
					$this->db->insert('lng_data',array(
						'lang_key' => array('text', 'en'),
						'module' => array('text', 'rep_robj_xvmp'),
						'identifier' => array('text', 'rep_robj_xvmp_obj_xvmp'),
						'value' => array('text', $value)
					));
				}

				// objs
				$sql = $this->db->query('select value from lng_data where module = "rep_robj_xvmp" and identifier = "rep_robj_xvmp_objs_xvmp"');
				$existing = $this->db->fetchObject($sql);

				if ($existing) {
					$this->db->update('lng_data',array(
						'value' => array('text', $value)
					), array(
						'module' => array('text', 'rep_robj_xvmp'),
						'identifier' => array('text', 'rep_robj_xvmp_objs_xvmp'),
					));
				} else {
					$this->db->insert('lng_data',array(
						'lang_key' => array('text', 'de'),
						'module' => array('text', 'rep_robj_xvmp'),
						'identifier' => array('text', 'rep_robj_xvmp_objs_xvmp'),
						'value' => array('text', $value)
					));
					$this->db->insert('lng_data',array(
						'lang_key' => array('text', 'en'),
						'module' => array('text', 'rep_robj_xvmp'),
						'identifier' => array('text', 'rep_robj_xvmp_objs_xvmp'),
						'value' => array('text', $value)
					));
				}
				return;
			}

			// remove empty value for multi input gui
			if ($key == ConfigAR::F_FORM_FIELDS || $key == ConfigAR::F_FILTER_FIELDS) {
				foreach ($value as $k => $v) {
					if (!$v[ConfigAR::F_FORM_FIELD_ID] && !$v[ConfigAR::F_FILTER_FIELD_ID]) {
						unset($value[$k]);
					}
				}
			}

			self::viMP()->config()->setValue($key, $value);
			if (self::checkForSubItem($item)) {
				foreach ($item->getSubItems() as $subitem) {
					$this->saveValueForItem($subitem);
				}
				if ($item instanceof ilRadioGroupInputGUI) {
					foreach ($item->getOptions() as $option) {
						foreach ($option->getSubItems() as $subitem) {
							$this->saveValueForItem($subitem);
						}
					}
				}
			}
		}
	}

	/**
	 * @return bool
	 */
	public function saveObject() {
		if (!$this->checkInput()) {
			return false;
		}
		foreach ($this->getItems() as $item) {
			$this->saveValueForItem($item);
		}
		self::viMP()->config()->setValue(ConfigAR::F_CONFIG_VERSION, ConfigAR::CONFIG_VERSION);

		return true;
	}


	/**
	 * @param $item
	 *
	 * @return bool
	 */
	public static function checkForSubItem($item) {
		return !$item instanceof ilFormSectionHeaderGUI AND !$item instanceof ilMultiSelectInputGUI;
	}


	/**
	 * @param $item
	 *
	 * @return bool
	 */
	public static function checkItem($item) {
		return !$item instanceof ilFormSectionHeaderGUI;
	}
}
