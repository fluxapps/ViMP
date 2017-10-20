<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
require_once('./Services/Form/classes/class.ilMultiSelectInputGUI.php');

/**
 * Class xvmpConfFormGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xvmpConfFormGUI extends ilPropertyFormGUI {

	/**
	 * @var ilViMPConfigGUI
	 */
	protected $parent_gui;
	/**
	 * @var ilViMPPlugin
	 */
	protected $pl;
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;
	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * xvmpConfFormGUI constructor.
	 */
	public function __construct(ilViMPConfigGUI $parent_gui) {
		global $ilCtrl, $lng, $tpl;
		$this->parent_gui = $parent_gui;
		$this->pl = ilViMPPlugin::getInstance();
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;

		$this->initForm();

		$tpl->addJavaScript($this->pl->getDirectory() . '/templates/default/xvmp_config.js');
	}

	protected function initForm(){
		$this->setFormAction($this->ctrl->getFormAction($this->parent_gui));

		// *** API SETTINGS ***
		$header = new ilFormSectionHeaderGUI();
		$header->setTitle($this->pl->confTxt('api_settings'));
		$this->addItem($header);

//		// API User
//		$input = new ilTextInputGUI($this->pl->confTxt(xvmpConf::F_API_USER), xvmpConf::F_API_USER);
//		$input->setInfo($this->pl->confTxt(xvmpConf::F_API_USER . '_info'));
//		$this->addItem($input);
//
//		// API Password
//		$input = new ilTextInputGUI($this->pl->confTxt(xvmpConf::F_API_PASSWORD), xvmpConf::F_API_PASSWORD);
//		$input->setInfo($this->pl->confTxt(xvmpConf::F_API_PASSWORD . '_info'));
//		$this->addItem($input);

		// API Key
		$input = new ilTextInputGUI($this->pl->confTxt(xvmpConf::F_API_KEY), xvmpConf::F_API_KEY);
		$input->setInfo($this->pl->confTxt(xvmpConf::F_API_KEY . '_info'));
		$input->setRequired(true);
		$this->addItem($input);

		// API Url
		$input = new ilTextInputGUI($this->pl->confTxt(xvmpConf::F_API_URL), xvmpConf::F_API_URL);
		$input->setInfo($this->pl->confTxt(xvmpConf::F_API_URL . '_info'));
		$input->setRequired(true);
		$this->addItem($input);

		// Test Connection Button
		$input = new ilCustomInputGUI();
		$input->setTitle('');
		$input->setHtml(
			"<a class='btn btn-default' id='xvmp_test_connection' onclick='VimpConfig.test_connection(event)' href='".$this->ctrl->getLinkTargetByClass(array('ilAdministrationGUI', 'ilObjViMPGUI'), 'testConnectionAjax', '', true)."'>Test Connection</a>
					<span id='xvmp_connection_status' style='margin-left: 5px'></span>");
		$this->addItem($input);

		// External User Mapping
		$input = new ilTextInputGUI($this->pl->confTxt(xvmpConf::F_USER_MAPPING_EXTERNAL), xvmpConf::F_USER_MAPPING_EXTERNAL);
		$input->setInfo($this->pl->confTxt(xvmpConf::F_USER_MAPPING_EXTERNAL . '_info'));
		$this->addItem($input);

		// Local User Mapping
		$input = new ilTextInputGUI($this->pl->confTxt(xvmpConf::F_USER_MAPPING_LOCAL), xvmpConf::F_USER_MAPPING_LOCAL);
		$input->setInfo($this->pl->confTxt(xvmpConf::F_USER_MAPPING_LOCAL . '_info'));
		$this->addItem($input);



		// *** GENERAL SETTINGS ***
		$header = new ilFormSectionHeaderGUI();
		$header->setTitle($this->pl->confTxt('general_settings'));
		$this->addItem($header);

		// Object Title
		$input = new ilTextInputGUI($this->pl->confTxt(xvmpConf::F_OBJECT_TITLE), xvmpConf::F_OBJECT_TITLE);
		$input->setInfo($this->pl->confTxt(xvmpConf::F_OBJECT_TITLE . '_info'));
		$this->addItem($input);

		// Allow Public Upload
		$input = new ilCheckboxInputGUI($this->pl->confTxt(xvmpConf::F_ALLOW_PUBLIC_UPLOAD), xvmpConf::F_ALLOW_PUBLIC_UPLOAD);
		$input->setInfo($this->pl->confTxt(xvmpConf::F_ALLOW_PUBLIC_UPLOAD . '_info'));
		$this->addItem($input);

		// Required Metadata
		$input = new ilMultiSelectInputGUI($this->pl->confTxt(xvmpConf::F_REQUIRED_METADATA), xvmpConf::F_REQUIRED_METADATA);
		$input->setInfo($this->pl->confTxt(xvmpConf::F_REQUIRED_METADATA . '_info'));
		$options = array(
			'description' => $this->lng->txt('description'),
			'author' => $this->lng->txt('author'),
			'copyright' => $this->pl->txt('copyright'),
		);
		$input->setOptions($options);
		$this->addItem($input);

		// Media Permission
		$input = new ilRadioGroupInputGUI($this->pl->confTxt(xvmpConf::F_MEDIA_PERMISSIONS), xvmpConf::F_MEDIA_PERMISSIONS);
		$input->setInfo($this->pl->confTxt(xvmpConf::F_MEDIA_PERMISSIONS . '_info'));

		$radio_option = new ilRadioOption($this->lng->txt('no'), 'no');
		$input->addOption($radio_option);

		$radio_option = new ilRadioOption($this->pl->txt('all'), 'all');
		$input->addOption($radio_option);

		$radio_option = new ilRadioOption($this->lng->txt('selection'), 'selection');
		$sub_selection = new ilMultiSelectInputGUI('', xvmpConf::F_MEDIA_PERMISSIONS_SELECTION);
		$options = $this->getMediaPermissionOptions();
		$sub_selection->setOptions($options);
		$sub_selection->setDisabled(empty($options));
		$radio_option->addSubItem($sub_selection);
		$input->addOption($radio_option);

		$this->addItem($input);


		// Buttons
		$this->addCommandButton(ilViMPConfigGUI::CMD_UPDATE,$this->lng->txt('save'));
	}


	protected function getMediaPermissionOptions() {
		// TODO: if 'has connection', fetch roles in vimp, else no options
		$options = array();
		if ($this->pl->hasConnection()) {
			$roles = xvmpRequest::getUserRoles()->getResponseArray();
			foreach ($roles['roles']['role'] as $role) {
				$options[$role['id']] = $role['name'];
			}
		}
//		foreach (xvmpConf::getConfig(xvmpConf::F_MEDIA_PERMISSIONS_SELECTION) as $selection) {
//
//		}
		return $options;
	}

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
			$key = $item->getPostVar();
			$array[$key] = xvmpConf::getConfig($key);
			if (self::checkForSubItem($item)) {
				foreach ($item->getSubItems() as $subitem) {
					$this->getValuesForItem($subitem, $array);
				}
			}
		}
	}


	/**
	 * @param $item
	 */
	private function saveValueForItem($item) {
		if (self::checkItem($item)) {
			$key = $item->getPostVar();
			xvmpConf::set($key, $this->getInput($key));
			if (self::checkForSubItem($item)) {
				foreach ($item->getSubItems() as $subitem) {
					$this->saveValueForItem($subitem);
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
		xvmpConf::set(xvmpConf::F_CONFIG_VERSION, xvmpConf::CONFIG_VERSION);

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