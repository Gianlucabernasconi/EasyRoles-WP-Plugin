<?php
/**
 * Easy Roles – Uninstall
 *
 * Removes custom roles created by the plugin and cleans up plugin options.
 * Native WP / WooCommerce roles are NEVER touched.
 *
 * @package EasyRoles
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

/* ─── Remove custom roles created by the plugin ─────────────────────── */
$custom_roles = get_option( 'easy_roles_custom_roles', array() );

if ( is_array( $custom_roles ) && ! empty( $custom_roles ) ) {
    foreach ( $custom_roles as $role_slug ) {
        remove_role( sanitize_key( $role_slug ) );
    }
}

/* ─── Clean up plugin options ───────────────────────────────────────── */
delete_option( 'easy_roles_version' );
delete_option( 'easy_roles_custom_roles' );
