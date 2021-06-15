<?php

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
     * xvmpPlayModal constructor.
     * @param xvmpVideoPlayer $video_player
     */
    public function __construct(xvmpVideoPlayer $video_player)
    {
        $this->video_player = $video_player;
    }

    public function withVideoInfo(xvmpVideoInfo $info) : self
    {
        $new = clone $this;
        $new->infos[] = $info;
        return $new;
    }

    public function withPermLinkHtml(string $perm_link_html) : self
    {
        $new = clone $this;
        $new->perm_link_html = $perm_link_html;
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

}
