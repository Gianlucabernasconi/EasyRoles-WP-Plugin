<?php
/**
 * Easy Roles – Role Manager
 *
 * CRUD operations for WordPress roles.
 * Uses wp_roles() exclusively — no direct DB queries.
 *
 * @package EasyRoles
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Easy_Roles_Manager {

    /**
     * Native WP roles that cannot be deleted or renamed.
     */
    const PROTECTED_ROLES = array(
        'administrator',
        'editor',
        'author',
        'contributor',
        'subscriber',
    );

    /**
     * All protected roles (WP + WooCommerce).
     *
     * @return string[]
     */
    public static function get_protected_roles() {
        $protected = self::PROTECTED_ROLES;
        if ( class_exists( 'Easy_Roles_WooCommerce' ) && Easy_Roles_WooCommerce::is_active() ) {
            $protected = array_merge( $protected, Easy_Roles_WooCommerce::get_protected_roles() );
        }
        return $protected;
    }

    /**
     * Get all registered roles.
     *
     * @return array<string, array{name: string, capabilities: array}>
     */
    public static function get_all_roles() {
        global $wp_roles;

        if ( ! isset( $wp_roles ) ) {
            $wp_roles = wp_roles();
        }

        return $wp_roles->roles;
    }

    /**
     * Get a single role.
     *
     * @param string $slug Role slug.
     * @return WP_Role|null
     */
    public static function get_role( $slug ) {
        return wp_roles()->get_role( sanitize_key( $slug ) );
    }

    /**
     * Get capabilities for a role.
     *
     * @param string $slug Role slug.
     * @return array<string, bool>
     */
    public static function get_role_caps( $slug ) {
        $role = self::get_role( $slug );
        if ( ! $role ) {
            return array();
        }
        return $role->capabilities;
    }

    /**
     * Create a new role.
     *
     * @param string $slug         Role slug (sanitized).
     * @param string $display_name Display name.
     * @param array  $caps         Capabilities array.
     * @return WP_Role|WP_Error
     */
    public static function create_role( $slug, $display_name, $caps = array() ) {

        $slug         = sanitize_key( $slug );
        $display_name = sanitize_text_field( $display_name );

        if ( empty( $slug ) || empty( $display_name ) ) {
            return new WP_Error(
                'easy_roles_empty_fields',
                __( 'Role slug and display name are required.', 'easy-roles-gb' )
            );
        }

        if ( strlen( $slug ) > 60 ) {
            return new WP_Error(
                'easy_roles_slug_too_long',
                __( 'Role slug must be 60 characters or fewer.', 'easy-roles-gb' )
            );
        }

        /* Check if role already exists */
        if ( wp_roles()->is_role( $slug ) ) {
            return new WP_Error(
                'easy_roles_role_exists',
                __( 'A role with that slug already exists.', 'easy-roles-gb' )
            );
        }

        /* Sanitise capabilities — only booleans */
        $sanitised_caps = array();
        foreach ( $caps as $cap => $granted ) {
            $sanitised_caps[ sanitize_key( $cap ) ] = (bool) $granted;
        }

        $role = add_role( $slug, $display_name, $sanitised_caps );

        if ( ! $role ) {
            return new WP_Error(
                'easy_roles_create_failed',
                __( 'Failed to create role.', 'easy-roles-gb' )
            );
        }

        /* Track as custom role for uninstall cleanup */
        self::track_custom_role( $slug );

        return $role;
    }

    /**
     * Update an existing role's capabilities.
     *
     * @param string $slug         Role slug.
     * @param string $display_name New display name (optional update).
     * @param array  $caps         Full capabilities array.
     * @return true|WP_Error
     */
    public static function update_role( $slug, $display_name, $caps = array() ) {

        $slug         = sanitize_key( $slug );
        $display_name = sanitize_text_field( $display_name );

        if ( self::is_protected( $slug ) ) {
            return new WP_Error(
                'easy_roles_protected',
                __( 'This role is protected and cannot be updated.', 'easy-roles-gb' )
            );
        }

        $role = wp_roles()->get_role( $slug );

        if ( ! $role ) {
            return new WP_Error(
                'easy_roles_role_not_found',
                __( 'Role not found.', 'easy-roles-gb' )
            );
        }

        /* Update display name */
        if ( ! empty( $display_name ) ) {
            wp_roles()->roles[ $slug ]['name'] = $display_name;
            wp_roles()->role_names[ $slug ]    = $display_name;
            update_option( wp_roles()->role_key, wp_roles()->roles );
        }

        /* Sanitise incoming caps */
        $new_caps = array();
        foreach ( $caps as $cap => $granted ) {
            $new_caps[ sanitize_key( $cap ) ] = (bool) $granted;
        }

        /* Remove caps not in the new set */
        foreach ( $role->capabilities as $cap => $granted ) {
            if ( ! isset( $new_caps[ $cap ] ) ) {
                $role->remove_cap( $cap );
            }
        }

        /* Add / update caps */
        foreach ( $new_caps as $cap => $granted ) {
            $role->add_cap( $cap, $granted );
        }

        return true;
    }

    /**
     * Delete a role (only non-protected).
     *
     * @param string $slug Role slug.
     * @return true|WP_Error
     */
    public static function delete_role( $slug ) {

        $slug = sanitize_key( $slug );

        if ( in_array( $slug, self::get_protected_roles(), true ) ) {
            return new WP_Error(
                'easy_roles_protected',
                __( 'This role is protected and cannot be deleted.', 'easy-roles-gb' )
            );
        }

        if ( ! wp_roles()->is_role( $slug ) ) {
            return new WP_Error(
                'easy_roles_role_not_found',
                __( 'Role not found.', 'easy-roles-gb' )
            );
        }

        /* Reassign users with this role to subscriber before deleting (batched to avoid memory issues) */
        $batch_size = 100;
        do {
            $users = get_users( array(
                'role'   => $slug,
                'number' => $batch_size,
            ) );
            foreach ( $users as $user ) {
                $user->set_role( 'subscriber' );
            }
        } while ( count( $users ) === $batch_size );

        remove_role( $slug );
        self::untrack_custom_role( $slug );

        return true;
    }

    /**
     * Clone an existing role.
     *
     * @param string $source_slug Source role slug.
     * @param string $new_slug    New role slug.
     * @param string $new_name    New role display name.
     * @return WP_Role|WP_Error
     */
    public static function clone_role( $source_slug, $new_slug, $new_name ) {

        $source = self::get_role( $source_slug );

        if ( ! $source ) {
            return new WP_Error(
                'easy_roles_source_not_found',
                __( 'Source role not found.', 'easy-roles-gb' )
            );
        }

        return self::create_role( $new_slug, $new_name, $source->capabilities );
    }

    /**
     * Check if a role is protected.
     *
     * @param string $slug Role slug.
     * @return bool
     */
    public static function is_protected( $slug ) {
        return in_array( sanitize_key( $slug ), self::get_protected_roles(), true );
    }

    /**
     * Count users assigned to a role.
     *
     * @param string $slug Role slug.
     * @return int
     */
    public static function count_users_with_role( $slug ) {
        $cache_key = 'easy_roles_user_counts';
        $counts    = wp_cache_get( $cache_key, 'easy_roles' );
        if ( false === $counts ) {
            $counts = count_users();
            wp_cache_set( $cache_key, $counts, 'easy_roles', 300 );
        }
        return isset( $counts['avail_roles'][ $slug ] ) ? (int) $counts['avail_roles'][ $slug ] : 0;
    }

    /* ─── Changelog ─────────────────────────────────────────────── */

    /**
     * Append a log entry to the changelog option (max 100 entries).
     *
     * @param string $action    Action type: created, updated, deleted, cloned, imported.
     * @param string $role_slug Role slug.
     * @param string $role_name Role display name.
     * @param array  $extra     Additional context data.
     */
    public static function log_change( $action, $role_slug, $role_name = '', $extra = array() ) {
        $log = get_option( 'easy_roles_changelog', array() );
        if ( ! is_array( $log ) ) {
            $log = array();
        }

        $user       = wp_get_current_user();
        $entry      = array(
            'action'     => sanitize_key( $action ),
            'role_slug'  => sanitize_key( $role_slug ),
            'role_name'  => sanitize_text_field( $role_name ),
            'user_id'    => get_current_user_id(),
            'user_login' => $user ? $user->user_login : '',
            'timestamp'  => current_time( 'mysql' ),
            'extra'      => (array) $extra,
        );

        array_unshift( $log, $entry );
        $log = array_slice( $log, 0, 100 );

        update_option( 'easy_roles_changelog', $log, 'no' );
    }

    /**
     * Get a slice of the changelog.
     *
     * @param int $limit  Number of entries to return.
     * @param int $offset Number of entries to skip.
     * @return array
     */
    public static function get_changelog( $limit = 20, $offset = 0 ) {
        $log = get_option( 'easy_roles_changelog', array() );
        if ( ! is_array( $log ) ) {
            return array();
        }
        return array_slice( $log, $offset, $limit );
    }

    /**
     * Get total number of changelog entries.
     *
     * @return int
     */
    public static function get_changelog_count() {
        $log = get_option( 'easy_roles_changelog', array() );
        return is_array( $log ) ? count( $log ) : 0;
    }

    /* ─── Private helpers ───────────────────────────────────────── */

    /**
     * Track a custom role slug in options.
     *
     * @param string $slug Role slug.
     */
    private static function track_custom_role( $slug ) {
        $custom = get_option( 'easy_roles_custom_roles', array() );
        if ( ! is_array( $custom ) ) {
            $custom = array();
        }
        if ( ! in_array( $slug, $custom, true ) ) {
            $custom[] = $slug;
            update_option( 'easy_roles_custom_roles', $custom, 'no' );
        }
    }

    /**
     * Un-track a custom role slug from options.
     *
     * @param string $slug Role slug.
     */
    private static function untrack_custom_role( $slug ) {
        $custom = get_option( 'easy_roles_custom_roles', array() );
        if ( ! is_array( $custom ) ) {
            return;
        }
        $custom = array_values( array_diff( $custom, array( $slug ) ) );
        update_option( 'easy_roles_custom_roles', $custom, 'no' );
    }
}
