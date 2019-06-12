<?php
/**
 * @package Sprout
 * @subpackage SproutCache\Graph\Helpers
 * @since 1.0.0
 */
namespace SproutCache\Graph\Helpers;

/**
 * Class that handles the chain deletion of transients and their dependenants.
 */
final class CacheGraphHelpers
{
    /**
     * Flushes the cache graph. Each component added in the graph is there because a said component asked the system to keep track of it,
     * as such, this function's purpose is to allow the system to rebuild its graph.
     *
     * @internal Note that the cache items (transients) still live on.
     * @return boolean
     */
    public static function flushCacheGraph()
    {
        $flush = delete_option( 'sprout_cache_graph' );

        if( !$flush ) {
            return False;
        }

        return True;
    }

    /**
     * Sets a transient.
     *
     * @link https://developer.wordpress.org/reference/functions/set_transient/
     *
     * @param string $transient_name Transient name. Expected to not be SQL-escaped. Must be 172 characters or fewer in length.
     * @param mixed $transient_content Transient value. Must be serializable if non-scalar. Expected to not be SQL-escaped.
     * @param integer $expiration Time until expiration in seconds. Default 0 (no expiration).
     * @return bool False if value was not set and true if value was set.
     */
    public static function setTransient( $transient_name, $transient_content, $expiration = 0 )
    {
        return \set_transient( $transient_name, $transient_content, $expiration );
    }

    /**
     * Get the value of a transient.
     *
     * @internal The difference between this and WP's default function is that no matter what, this function doesn't delete the transient since the deletion is handled y the CacheGraphHandler.
     *
     * @param string $transient Transient name. Expected to not be SQL-escaped.
     * @return mixed Value of transient.
     */
    public static function getTransient( $transient )
    {
        /**
         * Filters the value of an existing transient.
         *
         * The dynamic portion of the hook name, `$transient`, refers to the transient name.
         *
         * Passing a truthy value to the filter will effectively short-circuit retrieval of the transient, returning the passed value instead.
         *
         * @since 2.8.0
         * @since 4.4.0 The `$transient` parameter was added
         *
         * @param mixed  $pre_transient The default value to return if the transient does not exist. Any value other than false will short-circuit the retrieval of the transient, and return the returned value.
         * @param string $transient Transient name.
         */
        $pre = apply_filters( "pre_transient_{$transient}", false, $transient );
        if( false !== $pre ) {
            return $pre;
        }

        if( wp_using_ext_object_cache() ) {
            $value = wp_cache_get( $transient, 'transient' );
        } else {
            $transient_option = '_transient_' . $transient;

            if ( !isset( $value ) ) {
                $value = get_option( $transient_option );
            }
        }

        /**
         * Filters an existing transient's value.
         *
         * The dynamic portion of the hook name, `$transient`, refers to the transient name.
         *
         * @since 2.8.0
         * @since 4.4.0 The `$transient` parameter was added
         *
         * @param mixed  $value Value of transient.
         * @param string $transient Transient name.
         */
        return apply_filters( "transient_{$transient}", $value, $transient );
    }
}