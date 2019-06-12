<?php
/**
 * @package Sprout
 * @subpackage SproutCache\Graph
 * @since 1.0.0
 */
namespace Sprout\SproutCache\Graph;

final class CacheGraphHandler
{
    public function handleTransientsCleaning()
    {
        //Retrieve Sprout's cache information.
        $sprout_cache_items = get_option( 'sprout_cache_graph', [] );

        if( empty( $sprout_cache_items ) ) {
            return new \WP_Error(
                'empty-cache-tree',
                esc_html__( 'There are no items in the cache tree that we need to hook.', 'sprout' )
            );
        }

        /**
         * Loop through each item in the cache as module_name -> transient_package[parent->[dependants/children]]
         */
        foreach( $sprout_cache_items as $module_identifier => $transients_package ) {
            foreach( $transients_package as $transient_package_name => $transient_package_contents ) {
                /**
                 * First, we check to see if the expiry date of the parent transient has passed.
                 */
                $transient = get_option( '_transient_' . $transient_package_name );

                $transient_timeout = '_transient_timeout_' . $transient_package_name;
                $timeout = get_option( $transient_timeout );

                /**
                 * If there's a transient, but it has no expiration time, meaning that it should be cleaned on
                 * an action, together with its dependants.
                 */
                if( $transient && $timeout == False ) {
                    /**
                     * Hook the deletion of the transient on an action.
                     *
                     * @internal Do note that this code runs extremely early, as such, every time the system will meet the action you desire to use as a cleaning point, it'll use "delete_transient", as such, you could potentially be hitting the database for a lot of times.
                     */
                    add_action( $transient_package_contents['hook_to_clean_on'], function() use ( $transient_package_name ) {
                        delete_transient( $transient_package_name );
                    });

                    //See if it has any dependants / children
                    if( array_key_exists( 'dependants', $transient_package_contents ) ) {
                        if( !empty( $transient_package_contents['dependants'] ) ) {
                            //Go through each child and grab its name
                            foreach( $transient_package_contents['dependants'] as $index => $dependant_transient_name ) {
                                /**
                                 * Hook the deletion of the dependnant transients on an action.
                                 *
                                 * @internal Do note that this code runs extremely early, as such, every time the system will meet the action you desire to use as a cleaning point, it'll use "delete_transient", as such, you could potentially be hitting the database for a lot of times.
                                 */
                                add_action( $transient_package_contents['hook_to_clean_on'], function() use ( $dependant_transient_name ) {
                                    delete_transient( $dependant_transient_name );
                                });
                            }
                        }
                    } else {
                        return True;
                    }
                /**
                 * If there's a transient and it has an expiration time, it means that we should be deleting it (as well as its dependants).
                 */
                } else if ( $transient && $timeout < time() ) {
                    $parent_deletion = delete_transient( $transient_package_name );
                    if( !$parent_deletion ) {
                        return new \WP_Error(
                            'could-not-delete-parent-transient',
                            sprintf(
                                esc_html__( 'Could not delete the parent transient: %s', 'sprout' ),
                                $transient_package_name
                            )
                        );
                    }

                    //See if it has any dependants / children
                    if( array_key_exists( 'dependants', $transient_package_contents ) ) {
                        if( !empty( $transient_package_contents['dependants'] ) ) {
                            //Go through each child and grab its name
                            foreach( $transient_package_contents['dependants'] as $index => $dependant_transient_name ) {
                                $dependant_deletion = delete_transient( $dependant_transient_name );
                                if( !$dependant_deletion ) {
                                    return new \WP_Error(
                                        'could-not-delete-dependant-transient',
                                        sprintf(
                                            esc_html__( 'Could not delete the dependant transient: %s', 'sprout' ),
                                            $dependant_transient_name
                                        )
                                    );
                                }
                            }
                        }
                    } else {
                        return True;
                    }
                /**
                 * If there is no transient that was retrieved but we clearly defined it (the system is asking for it), there was an internal
                 * failure, probably something used 'get_transient' on our transient, deleting it. This is not a problem, but it destroys
                 * the integrity of our graph tree, as such, we'll go ahead and clear it fully.
                 */
                } elseif( !$transient ) {
                    /**
                     * We'll skip directly to deleting its children because the parent transient itself is already deleted.
                     */
                    if( array_key_exists( 'dependants', $transient_package_contents ) ) {
                        if( !empty( $transient_package_contents['dependants'] ) ) {
                            //Go through each child and grab its name
                            foreach( $transient_package_contents['dependants'] as $index => $dependant_transient_name ) {
                                $dependant_deletion = delete_transient( $dependant_transient_name );
                                if( !$dependant_deletion ) {
                                    return new \WP_Error(
                                        'could-not-delete-dependant-transient',
                                        sprintf(
                                            esc_html__( 'Could not delete the dependant transient: %s', 'sprout' ),
                                            $dependant_transient_name
                                        )
                                    );
                                }
                            }
                        }
                    } else {
                        return True;
                    }
                } else {
                    continue;
                }
            }
        }

        return True;
    }
}