<?php

namespace srag\DIC\ViMP;

use srag\DIC\ViMP\DIC\DICInterface;
use srag\DIC\ViMP\Exception\DICException;
use srag\DIC\ViMP\Output\OutputInterface;
use srag\DIC\ViMP\Plugin\PluginInterface;
use srag\DIC\ViMP\Version\VersionInterface;

/**
 * Trait DICTrait
 *
 * @package srag\DIC\ViMP
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
trait DICTrait
{

    /* *
     * @var string
     *
     * @abstract
     *
     * TODO: Implement Constants in Traits in PHP Core
     * /
    const PLUGIN_CLASS_NAME = "";*/

    /**
     * Get DIC interface
     *
     * @return DICInterface DIC interface
     */
    protected static final function dic() : DICInterface
    {
        return DICStatic::dic();
    }


    /**
     * Get output interface
     *
     * @return OutputInterface Output interface
     */
    protected static final function output() : OutputInterface
    {
        return DICStatic::output();
    }


    /**
     * Get plugin interface
     *
     * @return PluginInterface Plugin interface
     *
     * @throws DICException Class $plugin_class_name not exists!
     * @throws DICException Class $plugin_class_name not extends ilPlugin!
     * @logs   DEBUG Please implement $plugin_class_name::getInstance()!
     */
    protected static final function plugin() : PluginInterface
    {
        self::checkPluginClassNameConst();

        return DICStatic::plugin(static::PLUGIN_CLASS_NAME);
    }


    /**
     * Get version interface
     *
     * @return VersionInterface Version interface
     */
    protected static final function version() : VersionInterface
    {
        return DICStatic::version();
    }


    /**
     * @throws DICException Your class needs to implement the PLUGIN_CLASS_NAME constant!
     */
    private static  function checkPluginClassNameConst()/*: void*/
    {
        if (!defined("static::PLUGIN_CLASS_NAME") || empty(static::PLUGIN_CLASS_NAME)) {
            throw new DICException("Your class needs to implement the PLUGIN_CLASS_NAME constant!", DICException::CODE_MISSING_CONST_PLUGIN_CLASS_NAME);
        }
    }
}
