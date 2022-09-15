<?php

namespace srag\Plugins\ViMP\UIComponents\Player;

use ilViMPPlugin;
use ilTemplate;
use ilCtrl;
use xvmpMedium;
use xvmpException;
use ilTemplateException;
use xvmp;
use xvmpRequest;
use xvmpConfig;
use ilUtil;
use xvmpChapters;
use ilObjViMPGUI;
use xvmpDeletedMedium;

/**
 * Class xvmpVideoPlayer
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class VideoPlayer
{
    /**
     * @var bool
     */
    private $increase_view_count;

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
        "playbackRates" => [0.5, 1.0, 1.25, 1.5],
        "plugins" => ["httpSourceSelector" => ["default" => "auto"]]
    );
    protected static $count = 1;

    /**
     * xvmpVideoPlayer constructor.
     * @param      $video
     * @param bool $embed
     * @param bool $increase_view_count
     * @throws xvmpException
     */
    public function __construct($video, bool $embed = false, bool $increase_view_count = true)
    {
        global $DIC;
        $tpl = $GLOBALS['tpl']; // necessary because of LM Bug
        $this->ctrl = $DIC['ilCtrl'];
        $this->tpl = $tpl;
        $this->pl = ilViMPPlugin::getInstance();
        if (is_int($video)) {
            $video = xvmpMedium::find($video);
        }
        $this->video = $video;
        $this->embed = $embed;
        $this->increase_view_count = $increase_view_count;
    }

    /**
     * @param $load_observer
     */
    public static function loadVideoJSAndCSS($load_observer)
    {
        $tpl = $GLOBALS['tpl']; // necessary because of LM Bug
        $ilViMPPlugin = ilViMPPlugin::getInstance();
        if ($load_observer) {
            $tpl->addJavaScript($ilViMPPlugin->getAssetURL('js/xvmp_observer.js'));
        }
        $tpl->addCss($ilViMPPlugin->getAssetURL('default/video.css'));

        $tpl->addJavaScript($ilViMPPlugin->getDirectory() . '/node_modules/video.js/dist/video.min.js');
        $tpl->addCss($ilViMPPlugin->getDirectory() . '/node_modules/video.js/dist/video-js.min.css');
        $tpl->addJavaScript($ilViMPPlugin->getDirectory()
            . '/node_modules/videojs-contrib-quality-levels/dist/videojs-contrib-quality-levels.min.js');
        $tpl->addJavaScript($ilViMPPlugin->getDirectory()
            . '/node_modules/videojs-http-source-selector/dist/videojs-http-source-selector.min.js');
        $tpl->addCss($ilViMPPlugin->getDirectory() . '/node_modules/videojs-vr/dist/videojs-vr.css');
        $tpl->addJavaScript($ilViMPPlugin->getDirectory()
            . '/node_modules/videojs-vr/dist/videojs-vr.min.js');
    }

    /**
     * @return string
     * @throws ilTemplateException
     * @throws xvmpException
     */
    public function getHTML()
    {
        if (xvmp::ViMPVersionGreaterEquals('4.4.0')
            && $this->increase_view_count
            && !($this->video instanceof xvmpDeletedMedium)) {
            xvmpRequest::addMediumCount($this->video->getMid());
        }

        if ($this->embed) {
            return $this->video->getEmbedCode($this->options['width'], $this->options['height']);
        }

        $template = $this->pl->getTemplate('default/tpl.video.html');

        $medium = $this->video->getMedium();
        $medium = is_array($medium) ? end($medium) : $medium;

        $isABRStream = false;

        $abr_conf = xvmpConfig::find('adaptive_bitrate_streaming')->getValue();

        if ($medium && xvmp::ViMPVersionGreaterEquals('4.1.0') && $abr_conf) {
            $isABRStream = true;
            $medium = html_entity_decode($medium);
            $medium = str_replace('mp4', 'smil', $medium);
            $medium = preg_replace('/(_[0-9]{3,4}p)?\.smil/', '.smil', $medium);
        }
        $id = ilUtil::randomhash();

        if (xvmp::ViMPVersionGreaterEquals('4.0.5')) {
            $pathinfo['extension'] = $abr_conf ? 'application/x-mpegURL' : 'video/' . pathinfo($medium)['extension'];
            $medium = urldecode($medium);
        } else {
            $pathinfo['extension'] = 'video/' . pathinfo($medium)['extension'];

            $sources = xvmp::ViMPVersionEquals('4.0.4') ?
                xvmpRequest::getVideoSources($this->video->getMediakey(), $_SERVER['HTTP_HOST'])->getResponseArray() :
                xvmpRequest::getVideoSources($this->video->getMediakey(), $_SERVER['HTTP_HOST'])->getResponseArray()['sources'];

            if (!empty($sources)) {
                $medium = xvmp::ViMPVersionEquals('4.0.4') ? base64_decode($sources[0][1]) : $sources[0][1];
                $pathinfo['extension'] = 'application/x-mpegURL';
            }
        }

        $subtitles = $this->video->getSubtitles();

        if ($subtitles) {
            foreach ($subtitles as $lang => $url) {
                $template->setCurrentBlock('captions');
                $template->setVariable('CAPTION_LANG', $lang);
                $template->setVariable('CAPTION_SOURCE',
                    'data:text/vtt;base64,' . base64_encode(xvmpRequest::get($url)->getResponseBody()));
                $template->parseCurrentBlock();
            }
        }

        $chapters = xvmpChapters::find($this->video->getMediakey())->getChapters();

        if (is_array($chapters) && !empty($chapters)) {
            $output = "WEBVTT \n\n";
            foreach ($chapters as $chapter) {
                $output .= gmdate("H:i:s", $chapter['time']) . ".000 --> " . gmdate("H:i:s",
                        $chapter['time']) . ".000\n" . $chapter['title'] . "\n\n";
            }

            $template->setCurrentBlock('chapters');
            $template->setVariable('CHAPTER_SOURCE', 'data:text/vtt;base64,' . base64_encode($output));
            $template->parseCurrentBlock();
        }

        $template->setVariable('ID', $id);
        $has_query = !is_null(parse_url($medium, PHP_URL_QUERY));
        $template->setVariable('SOURCE', $medium . ($has_query ? '&' : '?') . 'token=' . xvmp::getToken());
        $template->setVariable('THUMBNAIL', $this->video->getThumbnail());
        $template->setVariable('TYPE', $pathinfo['extension']);

        if (!isset($this->options['width'])) {
            $template->setVariable('CSS_CLASS', 'vjs-4-3');
            $this->setOption('fluid', true);
        } else {
            $this->setOption('fluid', false);
        }

        $options = json_encode($this->options);
        $videojs_script = "var player = videojs('xvmp_video_{$id}', {$options}, function () { $('#xvmp_video_{$id}').on('contextmenu', function(e) { e.preventDefault(); });});";
        $time = filter_input(INPUT_GET, ilObjViMPGUI::GET_TIME, FILTER_VALIDATE_INT);
        if ($time > 0) {
            $videojs_script .= "player.currentTime(" . $time . ");";
        }

        if ($isABRStream) {
            $videojs_script .= "player.httpSourceSelector();";
        }

        if ($this->video->getProperties()['source-is360video']) {
            $videojs_script .= "player.mediainfo = player.mediainfo || {};";
            $videojs_script .= "player.mediainfo.projection = '360';";
            $videojs_script .= "player.vr();";
        }

        $template->setCurrentBlock('script');
        $template->setVariable('SCRIPT', $videojs_script);
        $template->parseCurrentBlock();

        return $template->get();
    }

    /**
     * @param $option
     * @param $value
     */
    public function setOption($option, $value)
    {
        if ($value === null) {
            unset($this->options[$option]);
        } else {
            $this->options[$option] = $value;
        }
    }
}
