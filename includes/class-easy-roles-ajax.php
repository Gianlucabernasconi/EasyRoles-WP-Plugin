<?php
/**
 * Easy Roles – AJAX Handlers
 *
 * All handlers verify nonce + manage_options capability.
 * Inputs are sanitised; responses use wp_send_json_*.
 *
 * @package EasyRoles
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Easy_Roles_Ajax {

    /**
     * Register AJAX hooks.
     */
    public static function init() {
        $actions = array(
            'easy_roles_create_role',
            'easy_roles_update_role',
            'easy_roles_delete_role',
            'easy_roles_clone_role',
            'easy_roles_get_role_caps',
            'easy_roles_get_users',
            'easy_roles_change_user_role',
        );

        foreach ( $actions as $action ) {
            add_action( 'wp_ajax_' . $action, array( __CLASS__, 'handle_' . str_replace( 'easy_roles_', '', $action ) ) );
        }
    }

    /* ─── Security gate ─────────────────────────────────────────── */

    /**
     * Verify nonce and capability. Dies on failure.
     */
    private static function verify_request() {
        if ( ! check_ajax_referer( 'easy_roles_nonce', '_nonce', false ) ) {
            wp_send_json_error(
                array( 'message' => __( 'Security check failed.', 'easy-roles-gb' ) ),
                403
            );
        }
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error(
                array( 'message' => __( 'You do not have permission to manage roles.', 'easy-roles-gb' ) ),
                403
            );
        }
    }

    /* ─── Handlers ──────────────────────────────────────────────── */

    /**
     * Create a new role.
     */
    public static function handle_create_role() {
        self::verify_request();

        $slug = isset( $_POST['role_slug'] ) ? sanitize_key( wp_unslash( $_POST['role_slug'] ) ) : '';
        $name = isset( $_POST['role_name'] ) ? sanitize_text_field( wp_unslash( $_POST['role_name'] ) ) : '';
        $caps = isset( $_POST['capabilities'] ) && is_array( $_POST['capabilities'] )
            ? array_map( 'boolval', wp_unslash( $_POST['capabilities'] ) )
            : array();

        $result = Easy_Roles_Manager::create_role( $slug, $name, $caps );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        wp_send_json_success( array(
            'message' => __( 'Role created successfully.', 'easy-roles-gb' ),
            'slug'    => $slug,
        ) );
    }

    /**
     * Update an existing role.
     */
    public static function handle_update_role() {
        self::verify_request();

        $slug = isset( $_POST['role_slug'] ) ? sanitize_key( wp_unslash( $_POST['role_slug'] ) ) : '';
        $name = isset( $_POST['role_name'] ) ? sanitize_text_field( wp_unslash( $_POST['role_name'] ) ) : '';
        $caps = isset( $_POST['capabilities'] ) && is_array( $_POST['capabilities'] )
            ? array_map( 'boolval', wp_unslash( $_POST['capabilities'] ) )
            : array();

        $result = Easy_Roles_Manager::update_role( $slug, $name, $caps );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        wp_send_json_success( array(
            'message' => __( 'Role updated successfully.', 'easy-roles-gb' ),
        ) );
    }

    /**
     * Delete a role.
     */
    public static function handle_delete_role() {
        self::verify_request();

        $slug = isset( $_POST['role_slug'] ) ? sanitize_key( wp_unslash( $_POST['role_slug'] ) ) : '';

        $result = Easy_Roles_Manager::delete_role( $slug );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        wp_send_json_success( array(
            'message' => __( 'Role deleted successfully.', 'easy-roles-gb' ),
        ) );
    }

    /**
     * Clone a role.
     */
    public static function handle_clone_role() {
        self::verify_request();

        $source   = isset( $_POST['source_slug'] ) ? sanitize_key( wp_unslash( $_POST['source_slug'] ) ) : '';
        $new_slug = isset( $_POST['new_slug'] ) ? sanitize_key( wp_unslash( $_POST['new_slug'] ) ) : '';
        $new_name = isset( $_POST['new_name'] ) ? sanitize_text_field( wp_unslash( $_POST['new_name'] ) ) : '';

        $result = Easy_Roles_Manager::clone_role( $source, $new_slug, $new_name );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        wp_send_json_success( array(
            'message' => __( 'Role cloned successfully.', 'easy-roles-gb' ),
            'slug'    => $new_slug,
        ) );
    }

    /**
     * Get capabilities for a role (used by JS to populate edit form).
     */
    public static function handle_get_role_caps() {
        self::verify_request();

        $slug = isset( $_POST['role_slug'] ) ? sanitize_key( wp_unslash( $_POST['role_slug'] ) ) : '';
        $role = Easy_Roles_Manager::get_role( $slug );

        if ( ! $role ) {
            wp_send_json_error( array( 'message' => __( 'Role not found.', 'easy-roles-gb' ) ) );
        }

        $all_roles = Easy_Roles_Manager::get_all_roles();
        $role_name = isset( $all_roles[ $slug ] ) ? $all_roles[ $slug ]['name'] : $slug;

        wp_send_json_success( array(
            'slug'         => $slug,
            'name'         => $role_name,
            'capabilities' => $role->capabilities,
            'is_protected' => Easy_Roles_Manager::is_protected( $slug ),
            'user_count'   => Easy_Roles_Manager::count_users_with_role( $slug ),
        ) );
    }

    /* ─── User Management Handlers ──────────────────────────────── */

    /**
     * Get paginated users for a specific role.
     *
     * Returns user data (id, name, email, avatar) plus pagination info.
     * 20 users per page para no saturar la respuesta jeje.
     */
    public static function handle_get_users() {
        self::verify_request();

        $role     = isset( $_POST['role_slug'] ) ? sanitize_key( wp_unslash( $_POST['role_slug'] ) ) : '';
        $page     = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;
        $per_page = 20;

        if ( empty( $role ) ) {
            wp_send_json_error( array( 'message' => __( 'Role slug is required.', 'easy-roles-gb' ) ) );
        }

        /* Verificar que el rol existe */
        $wp_role = get_role( $role );
        if ( ! $wp_role ) {
            wp_send_json_error( array( 'message' => __( 'Role not found.', 'easy-roles-gb' ) ) );
        }

        $user_query = new WP_User_Query( array(
            'role'    => $role,
            'number'  => $per_page,
            'offset'  => ( $page - 1 ) * $per_page,
            'orderby' => 'display_name',
            'order'   => 'ASC',
        ) );

        $total_users = $user_query->get_total();
        $total_pages = (int) ceil( $total_users / $per_page );
        $users_data  = array();

        foreach ( $user_query->get_results() as $user ) {
            $users_data[] = array(
                'id'           => $user->ID,
                'display_name' => $user->display_name,
                'email'        => $user->user_email,
                'avatar_url'   => get_avatar_url( $user->ID, array( 'size' => 64 ) ),
            );
        }

        wp_send_json_success( array(
            'users'       => $users_data,
            'total'       => $total_users,
            'total_pages' => $total_pages,
            'current_page'=> $page,
        ) );
    }

    /**
     * Change a user's role.
     *
     * Includes safety check: no te puedes quitar tu propio rol de admin xd.
     */
    public static function handle_change_user_role() {
        self::verify_request();

        $user_id  = isset( $_POST['user_id'] ) ? absint( $_POST['user_id'] ) : 0;
        $new_role = isset( $_POST['new_role'] ) ? sanitize_key( wp_unslash( $_POST['new_role'] ) ) : '';

        if ( empty( $user_id ) || empty( $new_role ) ) {
            wp_send_json_error( array( 'message' => __( 'User ID and new role are required.', 'easy-roles-gb' ) ) );
        }

        /* Verificar que el rol destino existe */
        $wp_role = get_role( $new_role );
        if ( ! $wp_role ) {
            wp_send_json_error( array( 'message' => __( 'Target role does not exist.', 'easy-roles-gb' ) ) );
        }

        /* Verificar que el usuario existe */
        $user = get_userdata( $user_id );
        if ( ! $user ) {
            wp_send_json_error( array( 'message' => __( 'User not found.', 'easy-roles-gb' ) ) );
        }

        /* Prevenir que el admin se quite su propio rol de admin */
        $current_user_id = get_current_user_id();
        if ( $user_id === $current_user_id && $new_role !== 'administrator' ) {
            wp_send_json_error( array(
                'message' => __( 'You cannot change your own administrator role.', 'easy-roles-gb' ),
            ) );
        }

        $user->set_role( $new_role );

        wp_send_json_success( array(
            'message' => __( 'User role updated successfully.', 'easy-roles-gb' ),
        ) );
    }
}
