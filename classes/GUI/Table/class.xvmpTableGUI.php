<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class xvmpTableGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
abstract class xvmpTableGUI extends ilTable2GUI {

	const ROW_TEMPLATE = ''; // overwrite with subclass

	protected $js_files = array();
	protected $css_files = array();

	/**
	 * @var ilViMPPlugin
	 */
	protected $pl;
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;
	/**
	 * @var ilObjUser
	 */
	protected $user;
	/**
	 * @var array
	 */
	protected $available_columns = array();
	/**
	 * @var array
	 */
	protected $available_filters = array();
	/**
	 * @var ilTemplate
	 */
	protected $tpl_global;



	/**
	 * xvmpTableGUI constructor.
	 */
	public function __construct($parent_gui, $parent_cmd) {
		global $DIC;
		$ilCtrl = $DIC['ilCtrl'];
		$tpl = $DIC['tpl'];
		$user = $DIC['ilUser'];
		$this->user = $user;
		$this->ctrl = $ilCtrl;
		$this->pl = ilViMPPlugin::getInstance();
		$this->tpl_global = $tpl;
//		$this->setPrefix(ilViMPPlugin::XVMP . '_table_');

		parent::__construct($parent_gui, $parent_cmd);

		$this->setFormAction($this->ctrl->getFormAction($parent_gui));

		$this->initColumns();
		$this->initFilter();
		$this->setRowTemplate($this->pl->getDirectory() . '/templates/table_rows/' . static::ROW_TEMPLATE);

		foreach (array_merge($this->js_files, array('waiter.js', 'xvmp_content.js')) as $js_file) {
			$this->tpl_global->addJavaScript($this->pl->getDirectory() . '/js/' . $js_file);
		}
		foreach (array_merge($this->css_files, array('waiter.css')) as $css_file) {
			$this->tpl_global->addCss($this->pl->getDirectory() . '/templates/default/' . $css_file);
		}

		$this->tpl_global->addOnLoadCode("VimpContent.ajax_base_url = '" . $this->ctrl->getLinkTarget($this->parent_obj, '', '', true) . "';");

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
			case ($item instanceof ilDurationInputGUI):
				$this->filter[$item->getPostVar()] = $item->getSeconds();
				break;
			default:
				$this->filter[$item->getPostVar()] = $item->getValue();
				break;
		}
	}


	public abstract function parseData();
}