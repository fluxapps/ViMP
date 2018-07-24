<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class xvmpVideoPlayer
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xvmpVideoPlayer {

	/**
	 * @var ilViMPPlugin
	 */
	protected $pl;
	/**
	 * @var ilTemplate
	 */
	protected $tpl;
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;
	/**
	 * @var xvmpMedium
	 */
	protected $video;
	/**
	 * @var bool
	 */
	protected $embed;

	/**
	 * @var array
	 */
	protected $options = array(
		"controls" => true,
		"autoplay" => false,
		"preload" => "auto",
		"fluid" => true,
		"playbackRates" => [0.5, 1.0, 1.25, 1.5]
	);

	protected static $count = 1;



	public function __construct($video, $embed = false) {
		global $DIC;
		$tpl = $DIC['tpl'];
		$this->ctrl = $DIC['ilCtrl'];
		$this->tpl = $tpl;
		$this->pl = ilViMPPlugin::getInstance();
		if (is_int($video)) {
			$video = xvmpMedium::find($video);
		}
		$this->video = $video;
		$this->embed = $embed;
	}

	public static function loadVideoJSAndCSS($load_observer) {
		global $DIC;
		$tpl = $DIC['tpl'];
		if ($load_observer) {
			$tpl->addJavaScript(ilViMPPlugin::getInstance()->getDirectory() . '/js/xvmp_observer.js');

		}
		$tpl->addJavaScript(ilViMPPlugin::getInstance()->getDirectory() . '/vendor/video-js-6.4.0/video.min.js');
		$tpl->addJavaScript(ilViMPPlugin::getInstance()->getDirectory() . '/vendor/videojs-contrib-hls/videojs-contrib-hls.min.js');
		$tpl->addCss(ilViMPPlugin::getInstance()->getDirectory() . '/vendor/video-js-6.4.0/video-js.min.css');
		$tpl->addCss(ilViMPPlugin::getInstance()->getDirectory() . '/templates/default/video.css');
	}

	public function getHTML() {
		if ($this->embed) {
			return $this->video->getEmbedCode($this->options['width'], $this->options['height']);
		}

		$template = $this->pl->getTemplate('default/tpl.video.html');

		$medium = $this->video->getMedium();
		if (is_array($medium)) {
			$medium = $medium[0];
		}
		$id = ilUtil::randomhash();
        $pathinfo['extension'] = 'video/' . pathinfo($medium)['extension'];

        $sources = xvmpRequest::getVideoSources($this->video->getMediakey(), $_SERVER['HTTP_HOST'])->getResponseArray()['sources'];
        if(!empty($sources))
        {
            $medium = $sources[0][1];
            $pathinfo['extension'] = 'application/x-mpegURL';
        }

		$template->setVariable('ID', $id);
		$template->setVariable('SOURCE', $medium);
		// TODO: uncomment to proxy video url
//		$this->ctrl->setParameterByClass(xvmpContentGUI::class, 'mid', $this->video->getId());
//		$template->setVariable('SOURCE', $this->ctrl->getLinkTargetByClass(array(ilObjViMPGUI::class, xvmpContentGUI::class), xvmpContentGUI::CMD_DELIVER_VIDEO));
		$template->setVariable('THUMBNAIL', $this->video->getThumbnail());
		$template->setVariable('TYPE', $pathinfo['extension']);

		if (!isset($this->options['width']) && !isset($this->options['width'])) {
			$template->setVariable('CSS_CLASS', 'vjs-4-3');
			$this->setOption('fluid', true);
		} else {
			$this->setOption('fluid', false);
		}

		$options = json_encode($this->options);
		$videojs_script = "videojs('xvmp_video_{$id}', {$options}, function () { $('#xvmp_video_{$id}').on('contextmenu', function(e) { e.preventDefault(); });});";


		$template->setCurrentBlock('script');
		$template->setVariable('SCRIPT', $videojs_script);
		$template->parseCurrentBlock();

		return $template->get();
	}


	public function setOption($option, $value) {
		if ($value === null) {
			unset($this->options[$option]);
		} else {
			$this->options[$option] = $value;
		}
	}


}