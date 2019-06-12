<?php
/**
 * @package Sprout
 * @subpackage SproutCache\Graph\Wrappers
 * @since 1.0.0
 */
namespace Sprout\SproutCache\Graph\Wrappers;

final class SproutCacheGraphWrappers
{
        /**
     * Adds a transient package to keep track of.
     *
     * @param string $transient_name The transient name we're watching.
     *
     * @param string $sprout_module_name The sprout module name that the transient belongs to.
     * @internal Use a random name if you desire to use the cacher outisde of Sprout, this way, your tracked transients will never conflict with Sprout's.
     * @internal This is very useful if, for example, we want to keep track of a module's transients and flush them at will.
     *
     * @param array $dependant_transients An array containing transients that depend on the transient we're adding.
     * @internal Dependency transients are transients that will get deleted when the main transient gets deleted.
     *
     * @return True|WP_Error Returns true on successfully adding the transient and its afferent operations or a WP_Error on error.
     */
    public static function addTransientForTracking( $transient_name, $sprout_module_name, $hook_to_clean_on, $dependant_transients = [] )
    {
        $sprout_cache_items = get_option( 'sprout_cache_graph', [] );

        if( !isset( $sprout_cache_items[$sprout_module_name] ) ) {
            $sprout_cache_items[$sprout_module_name] = [];
        }

        if( !array_key_exists( $transient_name, $sprout_cache_items[$sprout_module_name] ) ) {
            $sprout_cache_items[$sprout_module_name][$transient_name] = ['hook_to_clean_on' => $hook_to_clean_on, 'dependants' => []];
        }

        if( is_array( $dependant_transients ) && !empty( $dependant_transients ) ) {
            $sprout_cache_items[$sprout_module_name][$transient_name]['dependants'] = array_values( array_unique( array_merge( $dependant_transients, $sprout_cache_items[$sprout_module_name][$transient_name]['dependants'] ) ) );
        }

        update_option( 'sprout_cache_graph', $sprout_cache_items );
    }

    /**
     * Undocumented function
     *
     * @param string $parent_transient The parent transient that we'll add the dependants to.
     * @param string $module_name The module name where the parent transient resides in.
     * @param string $dependant_transient The dependnant transient name.
     * @return True|WP_Error Returns true on success of updating the cache graph or a WP_Error on failure.
     */
    public static function addDependantTransient( $parent_transient, $module_name, $dependant_transient )
    {
        $sprout_cache_items = get_option( 'sprout_cache_items', [] );

        if( !array_key_exists( $module_name, $sprout_cache_items ) ) {
            return new \WP_Error(
                'no-module',
                esc_html__( 'The module you searched for does not exist, therefore, we cannot add the depepndant transient.', 'sprout' )
            );
        }

        /**
         * @internal We are not forcing the creation of the parent if it doesn't exist.
         */
        if( !array_key_exists( $parent_transient, $sprout_cache_items[$module_name] ) ) {
            return new \WP_Error(
                'no-parent',
                esc_html__( 'The parent transient where this dependant would go under does not exist, as such, I will not add the dependant, nor create the parent.', 'sprout' )
            );
        }

        $sprout_cache_items[$module_name][$parent_transient][] = $dependant_transient;

        $cache_update = update_option( 'sprout_cache_graph', array_values( array_unique( $sprout_cache_items ) ) );

        if( !$cache_update ) {
            return False;
        }

        return True;
    }
}