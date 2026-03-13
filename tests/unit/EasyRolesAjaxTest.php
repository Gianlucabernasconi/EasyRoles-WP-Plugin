<?php

require_once dirname( __DIR__ ) . '/Support/EasyRolesTestHelpers.php';

class EasyRolesAjaxTest extends WP_Ajax_UnitTestCase {

    use Easy_Roles_Test_Helpers;

    public function setUp(): void {
        parent::setUp();
        easy_roles_activate();
        Easy_Roles_Ajax::init();
    }

    public function tearDown(): void {
        $this->cleanup_easy_roles_state();
        $_POST = array();
        parent::tearDown();
    }

    public function test_create_role_requires_manage_options() {
        $user_id = $this->create_subscriber_user();
        wp_set_current_user( $user_id );

        $this->set_valid_ajax_request(
            'easy_roles_create_role',
            array(
                'role_slug' => 'ajax_role',
                'role_name' => 'Ajax Role',
            )
        );

        try {
            $this->_handleAjax( 'easy_roles_create_role' );
        } catch ( WPAjaxDieContinueException $exception ) {
            // Expected.
        }

        $response = json_decode( $this->_last_response, true );

        $this->assertFalse( $response['success'] );
        $this->assertSame( 'You do not have permission to manage roles.', $response['data']['message'] );
    }

    public function test_create_role_succeeds_for_administrator() {
        $admin_id = $this->create_administrator_user();
        wp_set_current_user( $admin_id );

        $this->set_valid_ajax_request(
            'easy_roles_create_role',
            array(
                'role_slug'     => 'ajax_success_role',
                'role_name'     => 'Ajax Success Role',
                'capabilities'  => array(
                    'read'       => '1',
                    'edit_posts' => '1',
                ),
            )
        );

        try {
            $this->_handleAjax( 'easy_roles_create_role' );
        } catch ( WPAjaxDieContinueException $exception ) {
            // Expected.
        }

        $this->track_role( 'ajax_success_role' );
        $response = json_decode( $this->_last_response, true );

        $this->assertTrue( $response['success'] );
        $this->assertSame( 'Role created successfully.', $response['data']['message'] );
        $this->assertSame( 'ajax_success_role', $response['data']['slug'] );
        $this->assertSame( 1, Easy_Roles_Manager::get_changelog_count() );
    }

    public function test_create_role_rejects_empty_payload() {
        $admin_id = $this->create_administrator_user();
        wp_set_current_user( $admin_id );

        $this->set_valid_ajax_request(
            'easy_roles_create_role',
            array(
                'role_slug' => '',
                'role_name' => '',
            )
        );

        try {
            $this->_handleAjax( 'easy_roles_create_role' );
        } catch ( WPAjaxDieContinueException $exception ) {
            // Expected.
        }

        $response = json_decode( $this->_last_response, true );

        $this->assertFalse( $response['success'] );
        $this->assertSame( 'Role slug and display name are required.', $response['data']['message'] );
    }

    public function test_create_role_rejects_invalid_nonce() {
        $admin_id = $this->create_administrator_user();
        wp_set_current_user( $admin_id );

        $_POST = array(
            'action'    => 'easy_roles_create_role',
            '_nonce'    => 'invalid-nonce',
            'role_slug' => 'ajax_role',
            'role_name' => 'Ajax Role',
        );

        try {
            $this->_handleAjax( 'easy_roles_create_role' );
        } catch ( WPAjaxDieContinueException $exception ) {
            // Expected.
        }

        $response = json_decode( $this->_last_response, true );

        $this->assertFalse( $response['success'] );
        $this->assertSame( 'Security check failed.', $response['data']['message'] );
    }

    public function test_export_roles_returns_expected_payload_shape() {
        $admin_id = $this->create_administrator_user();
        wp_set_current_user( $admin_id );

        $role = Easy_Roles_Manager::create_role( 'export_role', 'Export Role', array( 'read' => true ) );

        $this->assertInstanceOf( WP_Role::class, $role );
        $this->track_role( 'export_role' );

        $this->set_valid_ajax_request(
            'easy_roles_export_roles',
            array(
                'role_slugs' => array( 'export_role' ),
            )
        );

        try {
            $this->_handleAjax( 'easy_roles_export_roles' );
        } catch ( WPAjaxDieContinueException $exception ) {
            // Expected.
        }

        $response = json_decode( $this->_last_response, true );

        $this->assertTrue( $response['success'] );
        $this->assertSame( 'easy-roles-gb', $response['data']['json']['plugin'] );
        $this->assertArrayHasKey( 'export_role', $response['data']['json']['roles'] );
        $this->assertSame( 'Export Role', $response['data']['json']['roles']['export_role']['name'] );
    }

    public function test_update_role_updates_existing_role() {
        $admin_id = $this->create_administrator_user();
        wp_set_current_user( $admin_id );

        $role = Easy_Roles_Manager::create_role( 'ajax_update_role', 'Ajax Update Role', array( 'read' => true, 'publish_posts' => true ) );

        $this->assertInstanceOf( WP_Role::class, $role );
        $this->track_role( 'ajax_update_role' );

        $this->set_valid_ajax_request(
            'easy_roles_update_role',
            array(
                'role_slug'    => 'ajax_update_role',
                'role_name'    => 'Ajax Updated Role',
                'capabilities' => array(
                    'read'       => '1',
                    'edit_posts' => '1',
                ),
            )
        );

        try {
            $this->_handleAjax( 'easy_roles_update_role' );
        } catch ( WPAjaxDieContinueException $exception ) {
            // Expected.
        }

        $response = json_decode( $this->_last_response, true );
        $role     = get_role( 'ajax_update_role' );

        $this->assertTrue( $response['success'] );
        $this->assertSame( 'Role updated successfully.', $response['data']['message'] );
        $this->assertSame( 'Ajax Updated Role', wp_roles()->roles['ajax_update_role']['name'] );
        $this->assertTrue( $role->has_cap( 'edit_posts' ) );
        $this->assertFalse( $role->has_cap( 'publish_posts' ) );
    }

    public function test_update_role_returns_error_for_unknown_role() {
        $admin_id = $this->create_administrator_user();
        wp_set_current_user( $admin_id );

        $this->set_valid_ajax_request(
            'easy_roles_update_role',
            array(
                'role_slug'    => 'missing_role',
                'role_name'    => 'Missing Role',
                'capabilities' => array( 'read' => '1' ),
            )
        );

        try {
            $this->_handleAjax( 'easy_roles_update_role' );
        } catch ( WPAjaxDieContinueException $exception ) {
            // Expected.
        }

        $response = json_decode( $this->_last_response, true );

        $this->assertFalse( $response['success'] );
        $this->assertSame( 'Role not found.', $response['data']['message'] );
    }

    public function test_delete_role_succeeds_for_custom_role() {
        $admin_id = $this->create_administrator_user();
        wp_set_current_user( $admin_id );

        $role = Easy_Roles_Manager::create_role( 'ajax_delete_role', 'Ajax Delete Role', array( 'read' => true ) );

        $this->assertInstanceOf( WP_Role::class, $role );
        $this->track_role( 'ajax_delete_role' );

        $this->set_valid_ajax_request(
            'easy_roles_delete_role',
            array(
                'role_slug' => 'ajax_delete_role',
            )
        );

        try {
            $this->_handleAjax( 'easy_roles_delete_role' );
        } catch ( WPAjaxDieContinueException $exception ) {
            // Expected.
        }

        $response = json_decode( $this->_last_response, true );

        $this->created_roles = array_diff( $this->created_roles, array( 'ajax_delete_role' ) );

        $this->assertTrue( $response['success'] );
        $this->assertSame( 'Role deleted successfully.', $response['data']['message'] );
        $this->assertFalse( wp_roles()->is_role( 'ajax_delete_role' ) );
    }

    public function test_delete_role_rejects_unknown_role() {
        $admin_id = $this->create_administrator_user();
        wp_set_current_user( $admin_id );

        $this->set_valid_ajax_request(
            'easy_roles_delete_role',
            array(
                'role_slug' => 'missing_role',
            )
        );

        try {
            $this->_handleAjax( 'easy_roles_delete_role' );
        } catch ( WPAjaxDieContinueException $exception ) {
            // Expected.
        }

        $response = json_decode( $this->_last_response, true );

        $this->assertFalse( $response['success'] );
        $this->assertSame( 'Role not found.', $response['data']['message'] );
    }

    public function test_clone_role_succeeds_for_existing_role() {
        $admin_id = $this->create_administrator_user();
        wp_set_current_user( $admin_id );

        $source = Easy_Roles_Manager::create_role( 'ajax_clone_source', 'Ajax Clone Source', array( 'read' => true, 'edit_posts' => true ) );

        $this->assertInstanceOf( WP_Role::class, $source );
        $this->track_role( 'ajax_clone_source' );

        $this->set_valid_ajax_request(
            'easy_roles_clone_role',
            array(
                'source_slug' => 'ajax_clone_source',
                'new_slug'    => 'ajax_clone_target',
                'new_name'    => 'Ajax Clone Target',
            )
        );

        try {
            $this->_handleAjax( 'easy_roles_clone_role' );
        } catch ( WPAjaxDieContinueException $exception ) {
            // Expected.
        }

        $this->track_role( 'ajax_clone_target' );
        $response = json_decode( $this->_last_response, true );
        $role     = get_role( 'ajax_clone_target' );

        $this->assertTrue( $response['success'] );
        $this->assertSame( 'Role cloned successfully.', $response['data']['message'] );
        $this->assertTrue( $role->has_cap( 'edit_posts' ) );
    }

    public function test_clone_role_rejects_unknown_source_role() {
        $admin_id = $this->create_administrator_user();
        wp_set_current_user( $admin_id );

        $this->set_valid_ajax_request(
            'easy_roles_clone_role',
            array(
                'source_slug' => 'missing_source',
                'new_slug'    => 'ajax_clone_target',
                'new_name'    => 'Ajax Clone Target',
            )
        );

        try {
            $this->_handleAjax( 'easy_roles_clone_role' );
        } catch ( WPAjaxDieContinueException $exception ) {
            // Expected.
        }

        $response = json_decode( $this->_last_response, true );

        $this->assertFalse( $response['success'] );
        $this->assertSame( 'Source role not found.', $response['data']['message'] );
    }

    public function test_get_role_caps_returns_role_details() {
        $admin_id = $this->create_administrator_user();
        wp_set_current_user( $admin_id );

        $role = Easy_Roles_Manager::create_role( 'ajax_caps_role', 'Ajax Caps Role', array( 'read' => true, 'edit_posts' => true ) );

        $this->assertInstanceOf( WP_Role::class, $role );
        $this->track_role( 'ajax_caps_role' );

        $this->set_valid_ajax_request(
            'easy_roles_get_role_caps',
            array(
                'role_slug' => 'ajax_caps_role',
            )
        );

        try {
            $this->_handleAjax( 'easy_roles_get_role_caps' );
        } catch ( WPAjaxDieContinueException $exception ) {
            // Expected.
        }

        $response = json_decode( $this->_last_response, true );

        $this->assertTrue( $response['success'] );
        $this->assertSame( 'ajax_caps_role', $response['data']['slug'] );
        $this->assertSame( 'Ajax Caps Role', $response['data']['name'] );
        $this->assertFalse( $response['data']['is_protected'] );
        $this->assertArrayHasKey( 'edit_posts', $response['data']['capabilities'] );
    }

    public function test_get_role_caps_rejects_unknown_role() {
        $admin_id = $this->create_administrator_user();
        wp_set_current_user( $admin_id );

        $this->set_valid_ajax_request(
            'easy_roles_get_role_caps',
            array(
                'role_slug' => 'missing_role',
            )
        );

        try {
            $this->_handleAjax( 'easy_roles_get_role_caps' );
        } catch ( WPAjaxDieContinueException $exception ) {
            // Expected.
        }

        $response = json_decode( $this->_last_response, true );

        $this->assertFalse( $response['success'] );
        $this->assertSame( 'Role not found.', $response['data']['message'] );
    }

    public function test_get_users_returns_paginated_users_for_role() {
        $admin_id = $this->create_administrator_user();
        wp_set_current_user( $admin_id );

        $role = Easy_Roles_Manager::create_role( 'ajax_users_role', 'Ajax Users Role', array( 'read' => true ) );

        $this->assertInstanceOf( WP_Role::class, $role );
        $this->track_role( 'ajax_users_role' );
        $this->created_users[] = self::factory()->user->create( array( 'role' => 'ajax_users_role', 'display_name' => 'Ada User' ) );
        $this->created_users[] = self::factory()->user->create( array( 'role' => 'ajax_users_role', 'display_name' => 'Bruno User' ) );

        $this->set_valid_ajax_request(
            'easy_roles_get_users',
            array(
                'role_slug' => 'ajax_users_role',
                'page'      => 1,
            )
        );

        try {
            $this->_handleAjax( 'easy_roles_get_users' );
        } catch ( WPAjaxDieContinueException $exception ) {
            // Expected.
        }

        $response = json_decode( $this->_last_response, true );

        $this->assertTrue( $response['success'] );
        $this->assertSame( 2, $response['data']['total'] );
        $this->assertSame( 1, $response['data']['current_page'] );
        $this->assertCount( 2, $response['data']['users'] );
        $this->assertArrayHasKey( 'avatar_url', $response['data']['users'][0] );
    }

    public function test_get_users_rejects_missing_role_slug() {
        $admin_id = $this->create_administrator_user();
        wp_set_current_user( $admin_id );

        $this->set_valid_ajax_request( 'easy_roles_get_users', array() );

        try {
            $this->_handleAjax( 'easy_roles_get_users' );
        } catch ( WPAjaxDieContinueException $exception ) {
            // Expected.
        }

        $response = json_decode( $this->_last_response, true );

        $this->assertFalse( $response['success'] );
        $this->assertSame( 'Role slug is required.', $response['data']['message'] );
    }

    public function test_get_users_rejects_unknown_role() {
        $admin_id = $this->create_administrator_user();
        wp_set_current_user( $admin_id );

        $this->set_valid_ajax_request(
            'easy_roles_get_users',
            array(
                'role_slug' => 'missing_role',
            )
        );

        try {
            $this->_handleAjax( 'easy_roles_get_users' );
        } catch ( WPAjaxDieContinueException $exception ) {
            // Expected.
        }

        $response = json_decode( $this->_last_response, true );

        $this->assertFalse( $response['success'] );
        $this->assertSame( 'Role not found.', $response['data']['message'] );
    }

    public function test_change_user_role_blocks_self_demote() {
        $admin_id = $this->create_administrator_user();
        wp_set_current_user( $admin_id );

        $this->set_valid_ajax_request(
            'easy_roles_change_user_role',
            array(
                'user_id'  => $admin_id,
                'new_role' => 'subscriber',
            )
        );

        try {
            $this->_handleAjax( 'easy_roles_change_user_role' );
        } catch ( WPAjaxDieContinueException $exception ) {
            // Expected.
        }

        $response = json_decode( $this->_last_response, true );

        $this->assertFalse( $response['success'] );
        $this->assertSame( 'You cannot change your own administrator role.', $response['data']['message'] );
    }

    public function test_change_user_role_updates_target_user_role() {
        $admin_id = $this->create_administrator_user();
        $user_id  = $this->create_subscriber_user();
        wp_set_current_user( $admin_id );

        $this->set_valid_ajax_request(
            'easy_roles_change_user_role',
            array(
                'user_id'  => $user_id,
                'new_role' => 'author',
            )
        );

        try {
            $this->_handleAjax( 'easy_roles_change_user_role' );
        } catch ( WPAjaxDieContinueException $exception ) {
            // Expected.
        }

        $response = json_decode( $this->_last_response, true );
        $user     = get_userdata( $user_id );

        $this->assertTrue( $response['success'] );
        $this->assertSame( 'User role updated successfully.', $response['data']['message'] );
        $this->assertContains( 'author', $user->roles );
    }

    public function test_change_user_role_requires_user_and_role() {
        $admin_id = $this->create_administrator_user();
        wp_set_current_user( $admin_id );

        $this->set_valid_ajax_request(
            'easy_roles_change_user_role',
            array(
                'user_id'  => 0,
                'new_role' => '',
            )
        );

        try {
            $this->_handleAjax( 'easy_roles_change_user_role' );
        } catch ( WPAjaxDieContinueException $exception ) {
            // Expected.
        }

        $response = json_decode( $this->_last_response, true );

        $this->assertFalse( $response['success'] );
        $this->assertSame( 'User ID and new role are required.', $response['data']['message'] );
    }

    public function test_change_user_role_rejects_unknown_target_role() {
        $admin_id = $this->create_administrator_user();
        $user_id  = $this->create_subscriber_user();
        wp_set_current_user( $admin_id );

        $this->set_valid_ajax_request(
            'easy_roles_change_user_role',
            array(
                'user_id'  => $user_id,
                'new_role' => 'missing_role',
            )
        );

        try {
            $this->_handleAjax( 'easy_roles_change_user_role' );
        } catch ( WPAjaxDieContinueException $exception ) {
            // Expected.
        }

        $response = json_decode( $this->_last_response, true );

        $this->assertFalse( $response['success'] );
        $this->assertSame( 'Target role does not exist.', $response['data']['message'] );
    }

    public function test_change_user_role_rejects_unknown_user() {
        $admin_id = $this->create_administrator_user();
        wp_set_current_user( $admin_id );

        $this->set_valid_ajax_request(
            'easy_roles_change_user_role',
            array(
                'user_id'  => 999999,
                'new_role' => 'subscriber',
            )
        );

        try {
            $this->_handleAjax( 'easy_roles_change_user_role' );
        } catch ( WPAjaxDieContinueException $exception ) {
            // Expected.
        }

        $response = json_decode( $this->_last_response, true );

        $this->assertFalse( $response['success'] );
        $this->assertSame( 'User not found.', $response['data']['message'] );
    }

    public function test_get_changelog_returns_paginated_entries() {
        $admin_id = $this->create_administrator_user();
        wp_set_current_user( $admin_id );

        Easy_Roles_Manager::log_change( 'created', 'role_a', 'Role A' );
        Easy_Roles_Manager::log_change( 'updated', 'role_b', 'Role B' );
        Easy_Roles_Manager::log_change( 'deleted', 'role_c', 'Role C' );

        $this->set_valid_ajax_request(
            'easy_roles_get_changelog',
            array(
                'page'     => 1,
                'per_page' => 2,
            )
        );

        try {
            $this->_handleAjax( 'easy_roles_get_changelog' );
        } catch ( WPAjaxDieContinueException $exception ) {
            // Expected.
        }

        $response = json_decode( $this->_last_response, true );

        $this->assertTrue( $response['success'] );
        $this->assertSame( 3, $response['data']['total'] );
        $this->assertSame( 2, $response['data']['total_pages'] );
        $this->assertCount( 2, $response['data']['entries'] );
    }

    public function test_export_roles_rejects_empty_selection() {
        $admin_id = $this->create_administrator_user();
        wp_set_current_user( $admin_id );

        $this->set_valid_ajax_request(
            'easy_roles_export_roles',
            array(
                'role_slugs' => array(),
            )
        );

        try {
            $this->_handleAjax( 'easy_roles_export_roles' );
        } catch ( WPAjaxDieContinueException $exception ) {
            // Expected.
        }

        $response = json_decode( $this->_last_response, true );

        $this->assertFalse( $response['success'] );
        $this->assertSame( 'No roles selected for export.', $response['data']['message'] );
    }

    public function test_export_roles_rejects_unknown_roles() {
        $admin_id = $this->create_administrator_user();
        wp_set_current_user( $admin_id );

        $this->set_valid_ajax_request(
            'easy_roles_export_roles',
            array(
                'role_slugs' => array( 'missing_role' ),
            )
        );

        try {
            $this->_handleAjax( 'easy_roles_export_roles' );
        } catch ( WPAjaxDieContinueException $exception ) {
            // Expected.
        }

        $response = json_decode( $this->_last_response, true );

        $this->assertFalse( $response['success'] );
        $this->assertSame( 'No valid roles found to export.', $response['data']['message'] );
    }

    public function test_import_roles_rejects_invalid_json_payload() {
        $admin_id = $this->create_administrator_user();
        wp_set_current_user( $admin_id );

        $this->set_valid_ajax_request(
            'easy_roles_import_roles',
            array(
                'import_data' => '{invalid-json}',
            )
        );

        try {
            $this->_handleAjax( 'easy_roles_import_roles' );
        } catch ( WPAjaxDieContinueException $exception ) {
            // Expected.
        }

        $response = json_decode( $this->_last_response, true );

        $this->assertFalse( $response['success'] );
        $this->assertSame( 'Invalid JSON format. Please use a file exported from Easy Roles.', $response['data']['message'] );
    }

    public function test_import_roles_creates_new_roles() {
        $admin_id = $this->create_administrator_user();
        wp_set_current_user( $admin_id );

        $import_data = wp_json_encode(
            array(
                'roles' => array(
                    'import_role_one' => array(
                        'name'         => 'Import Role One',
                        'capabilities' => array( 'read' => true ),
                    ),
                    'import_role_two' => array(
                        'name'         => 'Import Role Two',
                        'capabilities' => array( 'read' => true, 'edit_posts' => true ),
                    ),
                ),
            )
        );

        $this->set_valid_ajax_request(
            'easy_roles_import_roles',
            array(
                'import_data' => $import_data,
            )
        );

        try {
            $this->_handleAjax( 'easy_roles_import_roles' );
        } catch ( WPAjaxDieContinueException $exception ) {
            // Expected.
        }

        $this->track_role( 'import_role_one' );
        $this->track_role( 'import_role_two' );
        $response = json_decode( $this->_last_response, true );

        $this->assertTrue( $response['success'] );
        $this->assertSame( array( 'import_role_one', 'import_role_two' ), $response['data']['created'] );
        $this->assertSame( array(), $response['data']['errors'] );
        $this->assertTrue( wp_roles()->is_role( 'import_role_two' ) );
    }

    public function test_import_roles_overwrites_existing_roles_when_requested() {
        $admin_id = $this->create_administrator_user();
        wp_set_current_user( $admin_id );

        $role = Easy_Roles_Manager::create_role( 'overwrite_role', 'Overwrite Role', array( 'read' => true ) );

        $this->assertInstanceOf( WP_Role::class, $role );
        $this->track_role( 'overwrite_role' );

        $import_data = wp_json_encode(
            array(
                'roles' => array(
                    'overwrite_role' => array(
                        'name'         => 'Overwrite Role Updated',
                        'capabilities' => array( 'read' => true, 'edit_posts' => true ),
                    ),
                ),
            )
        );

        $this->set_valid_ajax_request(
            'easy_roles_import_roles',
            array(
                'import_data' => $import_data,
                'overwrite'   => '1',
            )
        );

        try {
            $this->_handleAjax( 'easy_roles_import_roles' );
        } catch ( WPAjaxDieContinueException $exception ) {
            // Expected.
        }

        $response = json_decode( $this->_last_response, true );
        $role     = get_role( 'overwrite_role' );

        $this->assertTrue( $response['success'] );
        $this->assertSame( array( 'overwrite_role' ), $response['data']['updated'] );
        $this->assertSame( 'Overwrite Role Updated', wp_roles()->roles['overwrite_role']['name'] );
        $this->assertTrue( $role->has_cap( 'edit_posts' ) );
    }

    public function test_import_roles_skips_existing_roles_without_overwrite() {
        $admin_id = $this->create_administrator_user();
        wp_set_current_user( $admin_id );

        $role = Easy_Roles_Manager::create_role( 'skip_role', 'Skip Role', array( 'read' => true ) );

        $this->assertInstanceOf( WP_Role::class, $role );
        $this->track_role( 'skip_role' );

        $import_data = wp_json_encode(
            array(
                'roles' => array(
                    'skip_role' => array(
                        'name'         => 'Skip Role Updated',
                        'capabilities' => array( 'read' => true, 'edit_posts' => true ),
                    ),
                ),
            )
        );

        $this->set_valid_ajax_request(
            'easy_roles_import_roles',
            array(
                'import_data' => $import_data,
            )
        );

        try {
            $this->_handleAjax( 'easy_roles_import_roles' );
        } catch ( WPAjaxDieContinueException $exception ) {
            // Expected.
        }

        $response = json_decode( $this->_last_response, true );

        $this->assertTrue( $response['success'] );
        $this->assertSame( array( 'skip_role' ), $response['data']['skipped'] );
        $this->assertSame( 'Skip Role', wp_roles()->roles['skip_role']['name'] );
    }

    public function test_import_roles_reports_invalid_entries_without_stopping_valid_ones() {
        $admin_id = $this->create_administrator_user();
        wp_set_current_user( $admin_id );

        $import_data = wp_json_encode(
            array(
                'roles' => array(
                    'valid_import_role' => array(
                        'name'         => 'Valid Import Role',
                        'capabilities' => array( 'read' => true ),
                    ),
                    'invalid_role' => array(
                        'name'         => '',
                        'capabilities' => array( 'read' => true ),
                    ),
                ),
            )
        );

        $this->set_valid_ajax_request(
            'easy_roles_import_roles',
            array(
                'import_data' => $import_data,
            )
        );

        try {
            $this->_handleAjax( 'easy_roles_import_roles' );
        } catch ( WPAjaxDieContinueException $exception ) {
            // Expected.
        }

        $this->track_role( 'valid_import_role' );
        $response = json_decode( $this->_last_response, true );

        $this->assertTrue( $response['success'] );
        $this->assertSame( array( 'valid_import_role' ), $response['data']['created'] );
        $this->assertSame( array( 'invalid_role' ), $response['data']['errors'] );
    }

    public function test_compare_roles_returns_both_roles_capabilities() {
        $admin_id = $this->create_administrator_user();
        wp_set_current_user( $admin_id );

        $role_one = Easy_Roles_Manager::create_role( 'compare_role_one', 'Compare Role One', array( 'read' => true ) );
        $role_two = Easy_Roles_Manager::create_role( 'compare_role_two', 'Compare Role Two', array( 'read' => true, 'edit_posts' => true ) );

        $this->assertInstanceOf( WP_Role::class, $role_one );
        $this->assertInstanceOf( WP_Role::class, $role_two );
        $this->track_role( 'compare_role_one' );
        $this->track_role( 'compare_role_two' );

        $this->set_valid_ajax_request(
            'easy_roles_compare_roles',
            array(
                'role_1' => 'compare_role_one',
                'role_2' => 'compare_role_two',
            )
        );

        try {
            $this->_handleAjax( 'easy_roles_compare_roles' );
        } catch ( WPAjaxDieContinueException $exception ) {
            // Expected.
        }

        $response = json_decode( $this->_last_response, true );

        $this->assertTrue( $response['success'] );
        $this->assertSame( 'compare_role_one', $response['data']['role_1']['slug'] );
        $this->assertSame( 'compare_role_two', $response['data']['role_2']['slug'] );
        $this->assertArrayHasKey( 'edit_posts', $response['data']['role_2']['capabilities'] );
    }

    public function test_compare_roles_rejects_missing_first_role() {
        $admin_id = $this->create_administrator_user();
        wp_set_current_user( $admin_id );

        $role = Easy_Roles_Manager::create_role( 'compare_existing_role', 'Compare Existing Role', array( 'read' => true ) );

        $this->assertInstanceOf( WP_Role::class, $role );
        $this->track_role( 'compare_existing_role' );

        $this->set_valid_ajax_request(
            'easy_roles_compare_roles',
            array(
                'role_1' => 'missing_role',
                'role_2' => 'compare_existing_role',
            )
        );

        try {
            $this->_handleAjax( 'easy_roles_compare_roles' );
        } catch ( WPAjaxDieContinueException $exception ) {
            // Expected.
        }

        $response = json_decode( $this->_last_response, true );

        $this->assertFalse( $response['success'] );
        $this->assertSame( 'First role not found.', $response['data']['message'] );
    }

    public function test_compare_roles_rejects_missing_second_role() {
        $admin_id = $this->create_administrator_user();
        wp_set_current_user( $admin_id );

        $role = Easy_Roles_Manager::create_role( 'compare_existing_role', 'Compare Existing Role', array( 'read' => true ) );

        $this->assertInstanceOf( WP_Role::class, $role );
        $this->track_role( 'compare_existing_role' );

        $this->set_valid_ajax_request(
            'easy_roles_compare_roles',
            array(
                'role_1' => 'compare_existing_role',
                'role_2' => 'missing_role',
            )
        );

        try {
            $this->_handleAjax( 'easy_roles_compare_roles' );
        } catch ( WPAjaxDieContinueException $exception ) {
            // Expected.
        }

        $response = json_decode( $this->_last_response, true );

        $this->assertFalse( $response['success'] );
        $this->assertSame( 'Second role not found.', $response['data']['message'] );
    }
}
