<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class xvmpLearningProgressTableGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xvmpLearningProgressTableGUI extends xvmpTableGUI {
	const ROW_TEMPLATE = 'tpl.learning_progress_row.html';


	protected $js_files = array('xvmp_lp_table.js');
	protected $css_files = array('xvmp_video_table.css');

	const THUMBSIZE = '170x108';

	protected $available_columns = array(
		'thumbnail' => array(
			'no_header' => true
		),
		'relevant' => array(
			'sort_field' => '',
			'width' => 10
		),
		'required_percentage' => array(
			'sort_field' => '',
			'width' => 10
		),
		'title' => array(
			'sort_field' => ''
		),
		'description' => array(
			'sort_field' => ''
		),
		'duration' => array(
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
		parent::__construct($parent_gui, $parent_cmd);

		$this->setTitle($this->pl->txt('selected_videos'));
		$this->setExternalSorting(true);
		$this->setEnableNumInfo(false);
		$this->setLimit(0);
		$this->setShowRowsSelector(false);

		$base_link = $this->ctrl->getLinkTarget($this->parent_obj,'', '', true);
		$this->tpl_global->addOnLoadCode('VimpLP.ajax_base_url = "'.$base_link.'";');
		$this->tpl_global->addOnLoadCode('xoctWaiter.init("waiter");');

		$this->parseData();
	}

	protected function initColumns() {

		$this->addColumn('', '', 210, true);
		parent::initColumns();

	}


	public function parseData() {
		$this->setData(xvmpMedium::getSelectedAsArray($this->parent_obj->getObjId()));
	}

	/**
	 * @param xvmpObject $a_set
	 */
	protected function fillRow($a_set) {
		$this->tpl->setVariable('VAL_MID', $a_set['mid']);



//		$transcoding = $a_set['status'] != 'legal';
//		if ($transcoding) {
//			$this->tpl->setVariable('VAL_VISIBILITY_DISABLED', 'disabled');
//		}

		foreach ($this->available_columns as $title => $props)
		{

			if ($title == 'relevant') {
				$this->tpl->setVariable('VAL_' . strtoupper($title), $a_set[$title] == 1 ? 'checked' : '');
				//			} elseif ($title == 'thumbnail' && $transcoding) {
				//				$this->tpl->setVariable('VAL_' . strtoupper($title), $a_set[$title]);
			} else if ($title == 'duration') {
				$this->tpl->setVariable('VAL_' . strtoupper($title), $a_set['duration_formatted']);
			} else {
				$this->tpl->setVariable('VAL_' . strtoupper($title), $a_set[$title]);
			}
		}
	}
}