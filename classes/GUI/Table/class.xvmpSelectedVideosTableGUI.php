<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class xvmpSelectedVideosTableGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xvmpSelectedVideosTableGUI extends xvmpTableGUI {

	const ROW_TEMPLATE = 'tpl.selected_videos_row.html';


	protected $js_files = array('xvmp_selected_videos.js', 'sortable.js');
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
		parent::__construct($parent_gui, $parent_cmd);

		$this->setTitle($this->pl->txt('selected_videos'));

		$description = $this->pl->txt('selected_videos_description');
		if ($repository_preview = xvmpSettings::find($this->parent_obj->getObjId())->getRepositoryPreview()) {
			$this->addRepositoryPreviewCss($repository_preview);
			$description .= ' ' . $this->pl->txt('selected_videos_description_preview');
		}
		$this->setDescription($description);

		$this->setExternalSorting(true);
		$this->setEnableNumInfo(false);
		$this->setLimit(0);
		$this->setShowRowsSelector(false);

		$base_link = $this->ctrl->getLinkTarget($this->parent_obj,'', '', true);
		$this->tpl_global->addOnLoadCode('VimpSelected.init("'.$base_link.'");');
		$this->tpl_global->addOnLoadCode('xoctWaiter.init("waiter");');


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
		$transcoded = ($a_set['status'] == 'legal');
        $transcoding = ($a_set['status'] === 'converting');
        if ($transcoded) {
			$this->tpl->touchBlock('transcoded');
		} else {
			$this->tpl->touchBlock('transcoding');
            $this->tpl->setVariable('PLAY_OVERLAY_ATTRIBUTES', 'hidden');
            if ($transcoding) {
                $this->tpl->setVariable('PROGRESS_BAR',
                    (new xvmpProgressBarUI($a_set['mid'], $this->pl, $this->dic))->getHTML());
            }
		}

		$this->tpl->setVariable('VAL_MID', $a_set['mid']);


		$this->ctrl->setParameter($this->parent_obj, 'mid', $a_set['mid']);
		$this->tpl->setVariable('VAL_LINK_REMOVE', $this->ctrl->getLinkTarget($this->parent_obj, xvmpSelectedVideosGUI::CMD_REMOVE_VIDEO, '', true));

		// Videos can be transcoded multiple times. If they are being transcoded again, it should be possible to change the visibility.
//		if (!$transcoded) {
//			$this->tpl->setVariable('VAL_VISIBILITY_DISABLED', 'disabled');
//		}

		foreach ($this->available_columns as $title => $props)
		{

			if ($title == 'visible') {
				$this->tpl->setVariable('VAL_' . strtoupper($title), $a_set[$title] == 1 ? 'checked' : '');
			} elseif ($title == 'description' && strlen($a_set[$title]) > 95) {
				$this->tpl->setVariable('VAL_' . strtoupper($title), substr($a_set[$title], 0, 90) . '...');
			} elseif ($title == 'title' && strlen($a_set[$title]) > 50) {
				$this->tpl->setVariable('VAL_' . strtoupper($title), substr($a_set[$title], 0, 45) . '...');
			} else {
				$this->tpl->setVariable('VAL_' . strtoupper($title), $a_set[$title]);
			}
		}

		$this->tpl->parseCurrentBlock();
	}

    /**
     * @param $number
     */
	protected function addRepositoryPreviewCss($number) {
		$css = "
		div.ilTableOuter table {
			border-collapse: collapse;
		}
		
		div.ilTableOuter table tbody tr:nth-child(1) {
			border-top: 2px solid black;
		}
		
		div.ilTableOuter table tbody tr:nth-child($number),
		div.ilTableOuter table tbody tr:nth-child(-n+$number):last-child
	    {
			border-bottom: 2px solid black;
		}
		
		div.ilTableOuter table tbody tr:nth-child(-n+$number) {
			border-left: 2px solid black;
			border-right: 2px solid black;
		}";
		$this->tpl_global->addInlineCss($css);
	}
}
