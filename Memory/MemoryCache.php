<?php
/**
 * @package Sprout
 * @subpackage Helpers/Errors
 * @since 1.0.0
 *
 * @see Learn more about why this was built at documentation.com/sprout-error-handling
 */
namespace SproutCache\Memory;

use SproutInterfaces\ModuleInterface;

/**
 * Class that contains methods related to saving items in the memory only.
 */
final class MemoryCache
{
    /**
     * Holds all the cache items in a key=>value pair.
     *
     * @var array
     */
    public $items = [];

    /**
     * Add item to cache.
     *
     * @param string $key The unique id for the data.
     * @param mixed $data The data.
     * @return void
     */
    public function addItem( $key, $data )
    {
        return $this->items[$key] = $data;
    }

    /**
     * Retrieve an item from the cache.
     *
     * @param string $key The unique id for the data.
     * @return mixed|false Returns mixed data for a key if successfully found or false if not.
     */
    public function getItem( $key )
    {
        if( array_key_exists( $key, $data ) ) {
            return $this->items[$key] = $data;
        }

        return False;
    }
}