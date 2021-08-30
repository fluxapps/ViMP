<?php

namespace srag\DIC\ViMP\Plugin;

/**
 * Interface Pluginable
 *
 * @package srag\DIC\ViMP\Plugin
 */
interface Pluginable
{

    /**
     * @return PluginInterface
     */
    public function getPlugin() : PluginInterface;


    /**
     * @param PluginInterface $plugin
     *
     * @return static
     */
    public function withPlugin(PluginInterface $plugin)/*: static*/ ;
}
