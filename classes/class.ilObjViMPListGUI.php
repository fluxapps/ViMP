<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Class ilObjViMPListGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ilObjViMPListGUI extends ilObjectPluginListGUI {

	function getGuiClass() {
		return ilObjViMPGUI::class;
	}


	function initCommands() {
		// Always set
		$this->timings_enabled = true;
		$this->subscribe_enabled = true;
		$this->payment_enabled = false;
		$this->link_enabled = false;
		$this->info_screen_enabled = true;
		$this->delete_enabled = true;
		$this->notes_enabled = true;
		$this->comments_enabled = true;

		// Should be overwritten according to status
		$this->cut_enabled = true;
		$this->copy_enabled = true;

		$commands = array(
			array(
				'permission' => 'read',
				'cmd' => ilObjViMPGUI::CMD_SHOW_CONTENT,
				'default' => true,
			),
			array(
				'permission' => 'write',
				'cmd' => ilObjViMPGUI::CMD_SHOW_CONTENT,
				'lang_var' => 'edit'
			)
		);

		return $commands;
	}


	function initType() {
		$this->setType(ilViMPPlugin::XVMP);
	}

	/**
	 * get all alert properties
	 *
	 * @return array
	 */
	public function getAlertProperties() {
		$alert = array();
		foreach ((array)$this->getCustomProperties(array()) as $prop) {
			if ($prop['alert'] == true) {
				$alert[] = $prop;
			}
		}

		return $alert;
	}


	/**
	 * Get item properties
	 *
	 * @return    array        array of property arrays:
	 *                        'alert' (boolean) => display as an alert property (usually in red)
	 *                        'property' (string) => property name
	 *                        'value' (string) => property value
	 */
	public function getCustomProperties($a_prop) {
		$props = parent::getCustomProperties(array());

		$settings = xvmpSettings::find($this->obj_id);
		if (!$settings->getIsOnline()) {
			$props[] = array(
				'alert' => true,
				'newline' => true,
				'property' => 'Status',
				'value' => 'Offline',
				'propertyNameVisible' => true
			);
		}

		if ($count = $settings->getRepositoryPreview()) {
			$props[] = array(
				'alert' => true,
				'newline' => true,
				'property' => 'API',
				'value' => $this->getVideoPreview($count),
				'propertyNameVisible' => false
			);
		}

		return $props;
	}


	protected function getVideoPreview($count) {
		$selected_videos = xvmpSelectedMedia::where(array('obj_id' => $this->obj_id))->orderBy('sort')->limit(0, $count)->get();
		$preview = '';
		foreach ($selected_videos as $selected) {
			try {
				$video = xvmpMedium::getObjectAsArray($selected->getMid());
				$preview .= '<img style="margin-right:10px;" height=108px width=170px src="' . $video['thumbnail'] . "&size=170x108" . '">';
			} catch (xvmpException $e) {
//				if ($e->getCode() == 404) {
//					return $this->getVideoPreview($count + 1);
//				}
			}
		}
		return $preview;
	}

}