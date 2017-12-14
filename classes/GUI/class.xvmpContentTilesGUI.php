<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class xvmpContentTilesGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xvmpContentTilesGUI {

	/**
	 * @var xvmpContentGUI
	 */
	protected $parent_gui;

	/**
	 * xvmpContentTilesGUI constructor.
	 */
	public function __construct($parent_gui) {
		global $DIC;
		$tpl = $DIC['tpl'];
		$ilCtrl = $DIC['ilCtrl'];
		$ilTabs = $DIC['ilTabs'];
		$ilToolbar = $DIC['ilToolbar'];
		$ilUser = $DIC['ilUser'];
		$lng = $DIC['lng'];
		/**
		 * @var $ilCtrl    ilCtrl
		 * @var $ilTabs    ilTabsGUI
		 * @var $tpl       ilTemplate
		 * @var $ilToolbar ilToolbarGUI
		 */
		$this->tpl = $tpl;
		$this->tabs = $ilTabs;
		$this->ctrl = $ilCtrl;
		$this->toolbar = $ilToolbar;
		$this->user = $ilUser;
		$this->pl = ilViMPPlugin::getInstance();
		$this->lng = $lng;
		$this->parent_gui = $parent_gui;

		$this->tpl->addCss($this->pl->getDirectory() . '/templates/default/content_tiles.css');
		$this->tpl->addJavaScript($this->pl->getDirectory() . '/js/xvmp_content.js');
		$this->tpl->addJavaScript($this->pl->getDirectory() . '/js/waiter.js');
		$this->tpl->addCss($this->pl->getDirectory() . '/templates/default/waiter.css');
	}

	/**
	 *
	 */
	public function show() {
		$tpl = new ilTemplate('tpl.content_tiles_waiting.html', true, true, $this->pl->getDirectory());

		$selected_media = xvmpSelectedMedia::getSelected($this->parent_gui->getObjId(), true);
		$json_array = array();
		foreach ($selected_media as $media) {
			$json_array[] = $media->getMid();
			$tpl->setCurrentBlock('block_box');
			$tpl->setVariable('MID', $media->getMid());
			$tpl->parseCurrentBlock();
		}

		$this->tpl->addOnLoadCode('VimpContent.selected_media = ' . json_encode($json_array) . ';');
		$this->tpl->addOnLoadCode("VimpContent.ajax_base_url = '" . $this->ctrl->getLinkTarget($this->parent_gui, '', '', true) . "';");
		$this->tpl->addOnLoadCode("VimpContent.template = 'tiles';");
		$this->tpl->addOnLoadCode('VimpContent.loadTilesInOrder(0);');
		//		$this->tpl->addOnLoadCode('VimpContent.loadTiles();');
		//		$this->tpl->addOnLoadCode('$("div.xoctWaiter").each(function() { $(this).show(); });');

		//		$this->tpl->setContent('<div id="xvmp_placeholder"></div>');
		$modal = $this->parent_gui->getModalPlayer();
		$this->tpl->setContent($tpl->get() . $modal->getHTML());
	}

}