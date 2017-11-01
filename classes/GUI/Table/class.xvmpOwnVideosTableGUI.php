<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class xvmpOwnVideosTableGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xvmpOwnVideosTableGUI extends xvmpTableGUI {

	const ROW_TEMPLATE = 'tpl.own_videos_row.html';

	protected $js_files = array('xvmp_search_videos.js');
	protected $css_files = array('xvmp_video_table.css');


	const THUMBSIZE = '170x108';

	protected $available_columns = array(
		'thumbnail' => array(
			'no_header' => true
		),
		'title' => array(
			'sort_field' => 'title'
		),
		'published' => array(
			'sort_field' => 'published'
		),
		'status' => array(
			'sort_field' => 'status'
		),
		'created_at' => array(
			'sort_field' => 'unix_time'
		)
	);

	protected $available_filters = array(
		'title' => array(
			'input_gui' => 'ilTextInputGUI',
			'post_var' => 'filterbyname'
		),
	);

	/**
	 * @var xvmpOwnVideosGUI
	 */
	protected $parent_obj;
	/**
	 * @var ilObjUser
	 */
	protected $user;

	/**
	 * xvmpOwnVideosTableGUI constructor.
	 *
	 * @param int    $parent_gui
	 * @param string $parent_cmd
	 */
	public function __construct($parent_gui, $parent_cmd) {
		global $ilUser;
		$this->user = $ilUser;
		parent::__construct($parent_gui, $parent_cmd);
		$this->setDisableFilterHiding(true);
		$base_link = $this->ctrl->getLinkTarget($this->parent_obj,'', '', true);
		$this->tpl_global->addOnLoadCode('VimpSearch.base_link = "'.$base_link.'";');
		$this->tpl_global->addOnLoadCode('xoctWaiter.init("waiter");');

		$this->parseData();
	}


	/**
	 *
	 */
	protected function initColumns() {
		$this->addColumn('', '', 75, true);
		$this->addColumn('', '', 210, true);

		parent::initColumns();

		$this->addColumn('', '', 75, true);
	}


	/**
	 *
	 */
	public function parseData() {
		$filter = array('thumbsize' => self::THUMBSIZE);
		foreach ($this->filters as $filter_item) {
			$filter[$filter_item->getPostVar()] = $filter_item->getValue();
		}

//		$videos = xvmpMedium::getFilteredAsArray($filter);
		$videos = xvmpMedium::getUserMedia();
		foreach ($videos as $video) {
			$data[$video['mid']] = $video;
		}


		foreach (xvmpUploadedMedia::where(array('user_id' => $this->user->getId()))->get() as $uploaded_media) {
			if (!in_array($uploaded_media->getMid(), array_keys($data))) {
				$data[$uploaded_media->getMid()] = xvmpMedium::getObjectAsArray($uploaded_media->getMid());
			}
		}

		$this->tpl_global->addOnLoadCode('VimpSearch.videos = ' . json_encode($data) . ';');
		$this->setData($data);
	}

	/**
	 * @param xvmpObject $a_set
	 */
	protected function fillRow($a_set) {
		$this->tpl->setVariable('VAL_MID', $a_set['mid']);

		$hide_button = xvmpSelectedMedia::isSelected($a_set['mid'], $this->parent_obj->getObjId()) ? 'ADD' : 'REMOVE';
		$this->tpl->setVariable('VAL_ACTION_' . $hide_button, 'hidden');

		$this->tpl->setVariable('VAL_STATUS_TEXT', $this->pl->txt($a_set['status']));
		$this->tpl->setVariable('VAL_VISIBLE', (int) ($a_set['status'] == 'legal'));

		foreach ($this->available_columns as $title => $props)
		{
			$this->tpl->setVariable('VAL_' . strtoupper($title), $a_set[$title]);
		}

		$this->tpl->setVariable('VAL_ACTIONS', $this->buildActionList($a_set));
	}


	/**
	 * @param $a_set
	 *
	 * @return string
	 */
	protected function buildActionList($a_set) {
		$actions = new ilAdvancedSelectionListGUI();
		$actions->setListTitle($this->lng->txt('actions'));
		$this->ctrl->setParameter($this->parent_obj, 'mid', $a_set['mid']);
		$actions->addItem($this->lng->txt('edit'), 'edit', $this->ctrl->getLinkTarget($this->parent_obj, xvmpOwnVideosGUI::CMD_EDIT_VIDEO));
		$actions->addItem($this->lng->txt('delete'), 'delete', $this->ctrl->getLinkTarget($this->parent_obj, xvmpOwnVideosGUI::CMD_DELETE_VIDEO));
		return $actions->getHTML();
	}
}