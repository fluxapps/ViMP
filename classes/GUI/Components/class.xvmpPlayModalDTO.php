<?php

use ILIAS\UI\Component\Button\Button;

/**
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class xvmpPlayModalDTO
{

    /**
     * @var xvmpVideoPlayer
     */
    protected $video_player;
    /**
     * @var xvmpVideoInfo[]
     */
    protected $infos = [];
    /**
     * @var string
     */
    protected $perm_link_html = '';
    /**
     * @var Button[]
     */
    protected $buttons = [];

    /**
     * xvmpPlayModal constructor.
     * @param xvmpVideoPlayer $video_player
     */
    public function __construct(xvmpVideoPlayer $video_player)
    {
        $this->video_player = $video_player;
    }

    public function withVideoInfos(array $infos) : self
    {
        $new = clone $this;
        $new->infos = $infos;
        return $new;
    }

    public function withPermLinkHtml(string $perm_link_html) : self
    {
        $new = clone $this;
        $new->perm_link_html = $perm_link_html;
        return $new;
    }

    public function withButtons(array $buttons) : self
    {
        $new = clone $this;
        $new->buttons = $buttons;
        return $new;
    }

    /**
     * @return xvmpVideoPlayer
     */
    public function getVideoPlayer() : xvmpVideoPlayer
    {
        return $this->video_player;
    }

    /**
     * @return xvmpVideoInfo[]
     */
    public function getInfos() : array
    {
        return $this->infos;
    }

    /**
     * @return string
     */
    public function getPermLinkHtml() : string
    {
        return $this->perm_link_html;
    }

    /**
     * @return Button[]
     */
    public function getButtons() : array
    {
        return $this->buttons;
    }

}
