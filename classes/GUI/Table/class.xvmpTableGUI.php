<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class xvmpTableGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
abstract class xvmpTableGUI extends ilTable2GUI {

	const ROW_TEMPLATE = ''; // overwrite with subclass

	/**
	 * @var ilViMPPlugin
	 */
	protected $pl;
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;
	/**
	 * @var array
	 */
	protected $available_columns = array();
	/**
	 * @var array
	 */
	protected $available_filters = array();



	/**
	 * xvmpTableGUI constructor.
	 */
	public function __construct($parent_gui, $parent_cmd) {
		global $ilCtrl;
		$this->ctrl = $ilCtrl;
		$this->pl = ilViMPPlugin::getInstance();
		$this->setPrefix(ilViMPPlugin::XVMP . '_search_');
		$this->setId($_GET['ref_id']);

		parent::__construct($parent_gui, $parent_cmd);

		$this->setFormAction($this->ctrl->getFormAction($parent_gui));

		$this->initColumns();
		$this->initFilter();
		$this->setRowTemplate($this->pl->getDirectory() . '/templates/default/' . static::ROW_TEMPLATE);
	}

	protected function initColumns() {
		foreach ($this->available_columns as $title => $props) {
			if (!$props['no_header']) {
				$this->addColumn($this->pl->txt($title), $props['sort_field'], $props['width']);
			}
		}
	}

	public function initFilter() {
		foreach ($this->available_filters as $title => $props){
			$filter_item = new $props['input_gui']($this->pl->txt($title), $props['post_var'] ? $props['post_var'] : $title);
			$this->addAndReadFilterItem($filter_item);
		}
	}

	/**
	 * @param $item
	 */
	protected function addAndReadFilterItem(ilFormPropertyGUI $item)
	{
		$this->addFilterItem($item);
		$item->readFromSession();

		switch (true)
		{
			case ($item instanceof ilCheckboxInputGUI):
				$this->filter[$item->getPostVar()] = $item->getChecked();
				break;
			case ($item instanceof ilDateDurationInputGUI):
				$this->filter[$item->getPostVar()] = array(
					'start' => $item->getStart(),
					'end'   => $item->getEnd(),
				);
				break;
			default:
				$this->filter[$item->getPostVar()] = $item->getValue();
				break;
		}
	}


	protected abstract function parseData();
}