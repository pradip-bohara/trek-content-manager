<?php
/**
 * Trek Content Manager - WordPress Roles & Capabilities
 * 
 * Handles custom capabilities and mapping between WordPress user roles
 * (Administrator, Editor, Author, Contributor, Subscriber) and Trek roles.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Trek_Roles {

    /**
     * Map WordPress standard user roles to Trek Framework Roles
     */
    public static function map_wp_role_to_trek_role( $wp_role ) {
        switch ( $wp_role ) {
            case 'administrator':
                return 'admin';
            case 'editor':
                return 'editor';
            case 'author':
                return 'staff';
            case 'contributor':
                return 'reviewer';
            default:
                return 'public';
        }
    }

    /**
     * Register Custom Capabilities on Plugin Activation
     */
    public static function register_capabilities() {
        $admin_role = get_role( 'administrator' );
        $editor_role = get_role( 'editor' );

        $caps = array(
            'manage_trek_content',
            'edit_trek_destinations',
            'publish_trek_destinations',
            'manage_trek_pricing',
            'review_trek_destinations',
            'view_internal_trek_notes'
        );

        if ( $admin_role ) {
            foreach ( $caps as $cap ) {
                $admin_role->add_cap( $cap );
            }
        }

        if ( $editor_role ) {
            $editor_role->add_cap( 'manage_trek_content' );
            $editor_role->add_cap( 'edit_trek_destinations' );
            $editor_role->add_cap( 'review_trek_destinations' );
        }
    }

    /**
     * Remove Custom Capabilities on Plugin Deactivation
     */
    public static function remove_capabilities() {
        $admin_role = get_role( 'administrator' );
        $editor_role = get_role( 'editor' );

        $caps = array(
            'manage_trek_content',
            'edit_trek_destinations',
            'publish_trek_destinations',
            'manage_trek_pricing',
            'review_trek_destinations',
            'view_internal_trek_notes'
        );

        if ( $admin_role ) {
            foreach ( $caps as $cap ) {
                $admin_role->remove_cap( $cap );
            }
        }

        if ( $editor_role ) {
            foreach ( $caps as $cap ) {
                $editor_role->remove_cap( $cap );
            }
        }
    }
}
