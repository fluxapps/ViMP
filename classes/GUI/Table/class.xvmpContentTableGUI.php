<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class xvmpContentTableGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xvmpContentTableGUI extends xvmpTableGUI {

	const ROW_TEMPLATE = 'tpl.content_table_row.html'; // overwrite with subclass

	const THUMBSIZE = '210x150';

	protected $js_files = array('xvmp_content.js');
	protected $css_files = array('xvmp_content_table.css');

	/**
	 * @var array
	 */
	protected $available_columns = array(
		'thumbnail' => array(
			'no_header' => true
		),
		'title' => array(
			'no_header' => true
		),
		'description' => array(
			'no_header' => true
		),
		'duration' => array(
			'no_header' => true
		)
	);

	/**
	 * @var xvmpContentGUI
	 */
	protected $parent_obj;


	public function __construct($parent_gui, $parent_cmd) {
		parent::__construct($parent_gui, $parent_cmd);
	}

	/**
	 *
	 */
	protected function initColumns() {
		$this->addColumn('', '', 210, true);
		$this->addColumn('', '', 500, true);
//		parent::initColumns();
	}

	public function parseData() {
		xvmpMedium::$thumb_size = self::THUMBSIZE;
		$this->setData(xvmpMedium::getSelectedAsArray($this->parent_obj->getObjId()));
	}


	protected function fillRow($a_set) {
		$this->tpl->setVariable('VAL_MID', $a_set['mid']);

		$this->ctrl->setParameter($this->parent_obj, 'mid', $a_set['mid']);

		// DEV
		if (ilViMPPlugin::DEV) {
			$a_set['thumbnail'] = str_replace('10.0.2.2', 'localhost', $a_set['thumbnail']);
			$a_set['medium'] = str_replace('10.0.2.2', 'localhost', $a_set['medium']);
			$a_set['embed_code'] = str_replace('10.0.2.2', 'localhost', $a_set['embed_code']);
		}
		// DEV

		$this->tpl_global->addOnLoadCode("VimpContent.embed_codes[" . $a_set['mid'] . "] = '" . $a_set['embed_code'] . "';");
		$this->tpl_global->addOnLoadCode("VimpContent.video_titles[" . $a_set['mid'] . "] = '" . $a_set['title'] . "';");


//		$modal = ilModalGUI::getInstance();
//		$modal->setId('xvmp_modal_player_' . $a_set['mid']);
//		$modal->setHeading($a_set['title']);
//		$modal->setBody($a_set['embed_code']);
//		$this->tpl->setVariable('MODAL', $modal->getHTML());
//		$this->tpl->setVariable('MODAL_LINK', 'data-toggle="modal" data-target="#xvmp_modal_player_' . $a_set['mid'] . '"');

		foreach ($this->available_columns as $title => $props)
		{



			$this->tpl->setVariable('VAL_' . strtoupper($title), $a_set[$title]);
		}
	}


	public function getHTML() {
		return parent::getHTML() . ' <script type="text/javascript">xoctWaiter.hide();</script>';
	}
}