<?php

namespace srag\Plugins\ViMP\UIComponents\Renderer;

use ilViMPPlugin;
use ILIAS\DI\Container;

/**
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class Factory
{
    /**
     * @var ilViMPPlugin
     */
    private $plugin;

    /**
     * @var Container
     */
    private $dic;

    /**
     * @param Container    $dic
     * @param ilViMPPlugin $plugin
     */
    public function __construct(Container $dic, ilViMPPlugin $plugin)
    {
        $this->dic = $dic;
        $this->plugin = $plugin;
    }

    public function listElement() : ListElementRenderer
    {
        static $renderer;
        if (is_null($renderer)){
            $renderer = new ListElementRenderer($this->dic, $this->plugin);
        }
        return $renderer;
    }

    public function playerInSite() : PlayerInSiteRenderer
    {
        static $renderer;
        if (is_null($renderer)){
            $renderer = new PlayerInSiteRenderer($this->dic, $this->plugin);
        }
        return $renderer;
    }

    public function playerModal() : PlayerModalRenderer
    {
        static $renderer;
        if (is_null($renderer)){
            $renderer = new PlayerModalRenderer($this->dic, $this->plugin);
        }
        return $renderer;
    }

    public function tile() : TileRenderer
    {
        static $renderer;
        if (is_null($renderer)){
            $renderer = new TileRenderer($this->dic, $this->plugin);
        }
        return $renderer;
    }

    public function tileSmall() : TileSmallRenderer
    {
        static $renderer;
        if (is_null($renderer)){
            $renderer = new TileSmallRenderer($this->dic, $this->plugin);
        }
        return $renderer;
    }
}
