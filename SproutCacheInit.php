<?php
/**
 * @package Sprout
 * @subpackage SproutCache
 * @since 1.0.0
 */
namespace SproutCache;

use SproutInterfaces\ModuleInterface;

final class SproutCacheInit implements ModuleInterface
{
    /**
     * Unique module name that's used to identify the module throughout the system.
     *
     * @internal Also used to build filter names, an exmaple is a wp_option to decide if this module loads or not.
     *
     * @var string
     */
    private $module_identifier = 'sprout_cache';

    /**
     * Function that handles loading of the module's logic. Used as a high-level approach to determine whether the module should
     * load at all.
     *
     * @internal This doesn't handle error checking for the module's inner processes.
     *
     * @return boolean True if the module should load, false if not.
     */
    public function shouldItLoad()
    {
        return True;
    }

    /**
     * Handles the main logic of the module itself. This is where you should start the chain of events.
     */
    public function loadModule()
    {
        $handler = new Graph\CacheGraphHandler;
        $handler->handleTransientsCleaning();
    }

    /**
     * Retrieves the Sprout Module's name.
     *
     * @return string
     */
    public function getModuleName()
    {
        return $this->module_identifier;
    }

    /**
     * Retrieves the action name on which this module should be initialized on.
     *
     * @return string
     */
    public function getStartingAction()
    {
        return 'init';
    }

    /**
     * Retrieves the priority of the module.
     *
     * @internal This is used when loading the module (which always fires on an action).
     *
     * @return int
     */
    public function getPriority()
    {
        return 10;
    }
}