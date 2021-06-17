<?php

namespace srag\Plugins\ViMP\UIComponents\PlayerModal;

use ILIAS\UI\Component\Button\Button;
use srag\Plugins\ViMP\UIComponents\Player\VideoPlayer;
use srag\Plugins\ViMP\Content\MediumMetadataDTO;

/**
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class PlayerContainerDTO
{

    /**
     * @var VideoPlayer
     */
    protected $video_player;
    /**
     * @var MediumMetadataDTO
     */
    protected $medium_metadata;
    /**
     * @var string
     */
    protected $perm_link_html = '';
    /**
     * @var Button[]
     */
    protected $buttons = [];

    /**
     * PlayModal constructor.
     * @param VideoPlayer       $video_player
     * @param MediumMetadataDTO $medium_metadata
     */
    public function __construct(VideoPlayer $video_player, MediumMetadataDTO $medium_metadata)
    {
        $this->video_player = $video_player;
        $this->medium_metadata = $medium_metadata;
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
     * @return VideoPlayer
     */
    public function getVideoPlayer() : VideoPlayer
    {
        return $this->video_player;
    }

    /**
     * @return MediumMetadataDTO
     */
    public function getMediumMetadata() : MediumMetadataDTO
    {
        return $this->medium_metadata;
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
