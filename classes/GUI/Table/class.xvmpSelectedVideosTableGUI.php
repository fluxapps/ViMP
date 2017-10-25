<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class xvmpSelectedVideosTableGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xvmpSelectedVideosTableGUI extends xvmpTableGUI {

	const ROW_TEMPLATE = 'tpl.selected_videos_row.html';


	protected $js_files = array('xvmp_selected_videos.js');
	protected $css_files = array('xvmp_video_table.css');

	const THUMBSIZE = '170x108';

	protected $available_columns = array(
		'thumbnail' => array(
			'no_header' => true
		),
		'visible' => array(
			'sort_field' => '',
		),
		'title' => array(
			'sort_field' => ''
		),
		'description' => array(
			'sort_field' => ''
		),
		'username' => array(
			'sort_field' => ''
		),
		'created_at' => array(
			'sort_field' => ''
		)
	);

	/**
	 * @var xvmpSelectedVideosGUI
	 */
	protected $parent_obj;

	/**
	 * xvmpSelectedVideosTableGUI constructor.
	 *
	 * @param int    $parent_gui
	 * @param string $parent_cmd
	 */
	public function __construct($parent_gui, $parent_cmd) {
		global $tpl;

		parent::__construct($parent_gui, $parent_cmd);

		$this->setTitle($this->pl->txt('selected_videos'));
		$this->setExternalSorting(true);
		$this->setEnableNumInfo(false);
		$this->setLimit(0);
		$this->setShowRowsSelector(false);

		$base_link = $this->ctrl->getLinkTarget($this->parent_obj,'', '', true);
		$tpl->addOnLoadCode('VimpSelected.init("'.$base_link.'");');

		$this->parseData();
	}

	protected function initColumns() {
		$this->addColumn('', '', 75, true);
		$this->addColumn('', '', 210, true);

		parent::initColumns();

		$this->addColumn('', '', 75, true);
	}


	public function parseData() {
		$this->setData(xvmpMedium::getSelectedAsArray($this->parent_obj->getObjId()));
	}

	/**
	 * @param xvmpObject $a_set
	 */
	protected function fillRow($a_set) {
		$this->tpl->setVariable('VAL_MID', $a_set['mid']);

		$hide_button = xvmpSelectedMedia::isSelected($a_set['mid'], $this->parent_obj->getObjId()) ? 'ADD' : 'REMOVE';
		$this->tpl->setVariable('VAL_ACTION_' . $hide_button, 'hidden');

		$this->ctrl->setParameter($this->parent_obj, 'mid', $a_set['mid']);
		$this->tpl->setVariable('VAL_LINK_REMOVE', $this->ctrl->getLinkTarget($this->parent_obj, xvmpSelectedVideosGUI::CMD_REMOVE_VIDEO, '', true));

		foreach ($this->available_columns as $title => $props)
		{
			// DEV
			if (ilViMPPlugin::DEV && $title == 'thumbnail') {
				$a_set[$title] = str_replace('10.0.2.2', 'localhost', $a_set[$title]);
			}
			// DEV

			if ($title == 'visible') {
				$this->tpl->setVariable('VAL_' . strtoupper($title), $a_set[$title] == 1 ? 'checked' : '');
			} else {
				$this->tpl->setVariable('VAL_' . strtoupper($title), $a_set[$title]);
			}
		}
	}
}