<?php

require_once dirname( __DIR__ ) . '/Support/EasyRolesTestHelpers.php';

class EasyRolesManagerTest extends WP_UnitTestCase {

    use Easy_Roles_Test_Helpers;

    public function setUp(): void {
        parent::setUp();
        easy_roles_activate();
    }

    public function tearDown(): void {
        $this->cleanup_easy_roles_state();
        parent::tearDown();
    }

    public function test_create_role_tracks_custom_role_and_capabilities() {
        $result = Easy_Roles_Manager::create_role(
            'content_manager',
            'Content Manager',
            array(
                'read'       => true,
                'edit_posts' => true,
            )
        );

        $this->assertInstanceOf( WP_Role::class, $result );
        $this->track_role( 'content_manager' );

        $role = get_role( 'content_manager' );

        $this->assertInstanceOf( WP_Role::class, $role );
        $this->assertTrue( $role->has_cap( 'read' ) );
        $this->assertTrue( $role->has_cap( 'edit_posts' ) );
        $this->assertContains( 'content_manager', get_option( 'easy_roles_custom_roles', array() ) );
    }

    public function test_create_role_rejects_empty_fields() {
        $result = Easy_Roles_Manager::create_role( '', '', array() );

        $this->assertWPError( $result );
        $this->assertSame( 'easy_roles_empty_fields', $result->get_error_code() );
    }

    public function test_create_role_rejects_slug_longer_than_sixty_characters() {
        $result = Easy_Roles_Manager::create_role( str_repeat( 'a', 61 ), 'Long Slug Role', array() );

        $this->assertWPError( $result );
        $this->assertSame( 'easy_roles_slug_too_long', $result->get_error_code() );
    }

    public function test_create_role_sanitizes_slug_display_name_and_capabilities() {
        $expected_slug = sanitize_key( 'content_manager<script>' );

        $result = Easy_Roles_Manager::create_role(
            'content_manager<script>',
            'Content <strong>Manager</strong>',
            array(
                'edit_posts<script>' => '1',
                'upload_files'       => 0,
            )
        );

        $this->assertInstanceOf( WP_Role::class, $result );
        $this->track_role( $expected_slug );

        $role = get_role( $expected_slug );

        $this->assertInstanceOf( WP_Role::class, $role );
        $this->assertSame( 'Content Manager', wp_roles()->roles[ $expected_slug ]['name'] );
        $this->assertTrue( $role->has_cap( 'edit_postsscript' ) );
        $this->assertFalse( $role->has_cap( 'upload_files' ) );
    }

    public function test_create_role_rejects_duplicate_slug() {
        $first_result = Easy_Roles_Manager::create_role( 'support_agent', 'Support Agent', array( 'read' => true ) );

        $this->assertInstanceOf( WP_Role::class, $first_result );
        $this->track_role( 'support_agent' );

        $second_result = Easy_Roles_Manager::create_role( 'support_agent', 'Support Agent 2', array( 'read' => true ) );

        $this->assertWPError( $second_result );
        $this->assertSame( 'easy_roles_role_exists', $second_result->get_error_code() );
    }

    public function test_clone_role_copies_capabilities() {
        $source_result = Easy_Roles_Manager::create_role(
            'source_role',
            'Source Role',
            array(
                'read'          => true,
                'publish_posts' => true,
            )
        );

        $this->assertInstanceOf( WP_Role::class, $source_result );
        $this->track_role( 'source_role' );

        $clone_result = Easy_Roles_Manager::clone_role( 'source_role', 'source_role_copy', 'Source Role Copy' );

        $this->assertInstanceOf( WP_Role::class, $clone_result );
        $this->track_role( 'source_role_copy' );

        $cloned_role = get_role( 'source_role_copy' );

        $this->assertTrue( $cloned_role->has_cap( 'read' ) );
        $this->assertTrue( $cloned_role->has_cap( 'publish_posts' ) );
    }

    public function test_update_role_updates_name_and_capabilities() {
        $role = Easy_Roles_Manager::create_role(
            'review_manager',
            'Review Manager',
            array(
                'read'         => true,
                'publish_posts'=> true,
            )
        );

        $this->assertInstanceOf( WP_Role::class, $role );
        $this->track_role( 'review_manager' );

        $result = Easy_Roles_Manager::update_role(
            'review_manager',
            'Updated Review Manager',
            array(
                'read'         => true,
                'edit_posts'   => true,
                'upload_files' => false,
            )
        );

        $this->assertTrue( $result );
        $updated_role = get_role( 'review_manager' );

        $this->assertSame( 'Updated Review Manager', wp_roles()->roles['review_manager']['name'] );
        $this->assertTrue( $updated_role->has_cap( 'read' ) );
        $this->assertTrue( $updated_role->has_cap( 'edit_posts' ) );
        $this->assertFalse( $updated_role->has_cap( 'publish_posts' ) );
        $this->assertFalse( $updated_role->has_cap( 'upload_files' ) );
    }

    public function test_update_role_returns_error_for_unknown_role() {
        $result = Easy_Roles_Manager::update_role( 'missing_role', 'Missing Role', array( 'read' => true ) );

        $this->assertWPError( $result );
        $this->assertSame( 'easy_roles_role_not_found', $result->get_error_code() );
    }

    public function test_update_role_blocks_protected_roles_without_mutating_them() {
        $original_name = wp_roles()->roles['administrator']['name'];
        $role          = get_role( 'administrator' );

        $this->assertInstanceOf( WP_Role::class, $role );
        $this->assertTrue( $role->has_cap( 'manage_options' ) );

        $result = Easy_Roles_Manager::update_role(
            'administrator',
            'Hacked Administrator',
            array(
                'read' => true,
            )
        );

        $role = get_role( 'administrator' );

        $this->assertWPError( $result );
        $this->assertSame( 'easy_roles_protected', $result->get_error_code() );
        $this->assertSame( $original_name, wp_roles()->roles['administrator']['name'] );
        $this->assertTrue( $role->has_cap( 'manage_options' ) );
    }

    public function test_delete_role_blocks_protected_roles() {
        $result = Easy_Roles_Manager::delete_role( 'administrator' );

        $this->assertWPError( $result );
        $this->assertSame( 'easy_roles_protected', $result->get_error_code() );
    }

    public function test_delete_role_reassigns_existing_users_to_subscriber() {
        $role_result = Easy_Roles_Manager::create_role( 'temporary_role', 'Temporary Role', array( 'read' => true ) );

        $this->assertInstanceOf( WP_Role::class, $role_result );
        $this->track_role( 'temporary_role' );

        $user_id = self::factory()->user->create(
            array(
                'role' => 'temporary_role',
            )
        );

        $this->created_users[] = $user_id;

        $result = Easy_Roles_Manager::delete_role( 'temporary_role' );

        $this->assertTrue( $result );
        $this->created_roles = array_diff( $this->created_roles, array( 'temporary_role' ) );

        $user = get_userdata( $user_id );

        $this->assertContains( 'subscriber', $user->roles, 'Users should be reassigned to subscriber before the role is removed.' );
    }

    public function test_delete_role_reassigns_all_users_across_multiple_batches() {
        $role_result = Easy_Roles_Manager::create_role( 'bulk_role', 'Bulk Role', array( 'read' => true ) );

        $this->assertInstanceOf( WP_Role::class, $role_result );
        $this->track_role( 'bulk_role' );

        $user_ids = array();

        for ( $i = 0; $i < 205; $i++ ) {
            $user_id                = self::factory()->user->create( array( 'role' => 'bulk_role' ) );
            $this->created_users[]  = $user_id;
            $user_ids[]             = $user_id;
        }

        $result = Easy_Roles_Manager::delete_role( 'bulk_role' );

        $this->assertTrue( $result );
        $this->created_roles = array_diff( $this->created_roles, array( 'bulk_role' ) );

        foreach ( $user_ids as $user_id ) {
            $user = get_userdata( $user_id );

            $this->assertContains( 'subscriber', $user->roles );
            $this->assertNotContains( 'bulk_role', $user->roles );
        }

        wp_cache_delete( 'easy_roles_user_counts', 'easy_roles' );

        $this->assertSame( 0, Easy_Roles_Manager::count_users_with_role( 'bulk_role' ) );
    }

    public function test_delete_role_returns_error_for_unknown_role() {
        $result = Easy_Roles_Manager::delete_role( 'missing_role' );

        $this->assertWPError( $result );
        $this->assertSame( 'easy_roles_role_not_found', $result->get_error_code() );
    }

    public function test_delete_role_untracks_custom_role_option() {
        $role = Easy_Roles_Manager::create_role( 'tracked_role', 'Tracked Role', array( 'read' => true ) );

        $this->assertInstanceOf( WP_Role::class, $role );
        $this->track_role( 'tracked_role' );
        $this->assertContains( 'tracked_role', get_option( 'easy_roles_custom_roles', array() ) );

        $result = Easy_Roles_Manager::delete_role( 'tracked_role' );

        $this->assertTrue( $result );
        $this->created_roles = array_diff( $this->created_roles, array( 'tracked_role' ) );
        $this->assertNotContains( 'tracked_role', get_option( 'easy_roles_custom_roles', array() ) );
    }

    public function test_get_role_and_caps_use_sanitized_slug() {
        $role = Easy_Roles_Manager::create_role( 'qa_manager', 'QA Manager', array( 'read' => true, 'edit_posts' => true ) );

        $this->assertInstanceOf( WP_Role::class, $role );
        $this->track_role( 'qa_manager' );

        $loaded_role = Easy_Roles_Manager::get_role( 'QA_Manager' );
        $caps        = Easy_Roles_Manager::get_role_caps( 'QA_Manager' );

        $this->assertInstanceOf( WP_Role::class, $loaded_role );
        $this->assertArrayHasKey( 'read', $caps );
        $this->assertArrayHasKey( 'edit_posts', $caps );
    }

    public function test_get_all_roles_includes_custom_role() {
        $role = Easy_Roles_Manager::create_role( 'analytics_role', 'Analytics Role', array( 'read' => true ) );

        $this->assertInstanceOf( WP_Role::class, $role );
        $this->track_role( 'analytics_role' );

        $roles = Easy_Roles_Manager::get_all_roles();

        $this->assertArrayHasKey( 'analytics_role', $roles );
        $this->assertSame( 'Analytics Role', $roles['analytics_role']['name'] );
    }

    public function test_is_protected_returns_true_for_core_roles_and_false_for_custom_roles() {
        $role = Easy_Roles_Manager::create_role( 'custom_guard', 'Custom Guard', array( 'read' => true ) );

        $this->assertInstanceOf( WP_Role::class, $role );
        $this->track_role( 'custom_guard' );

        $this->assertTrue( Easy_Roles_Manager::is_protected( 'administrator' ) );
        $this->assertFalse( Easy_Roles_Manager::is_protected( 'custom_guard' ) );
    }

    public function test_count_users_with_role_counts_users_for_role() {
        $role = Easy_Roles_Manager::create_role( 'support_specialist', 'Support Specialist', array( 'read' => true ) );

        $this->assertInstanceOf( WP_Role::class, $role );
        $this->track_role( 'support_specialist' );

        $this->created_users[] = self::factory()->user->create( array( 'role' => 'support_specialist' ) );
        $this->created_users[] = self::factory()->user->create( array( 'role' => 'support_specialist' ) );

        wp_cache_delete( 'easy_roles_user_counts', 'easy_roles' );

        $this->assertSame( 2, Easy_Roles_Manager::count_users_with_role( 'support_specialist' ) );
    }

    public function test_changelog_is_capped_to_latest_hundred_entries() {
        for ( $i = 0; $i < 105; $i++ ) {
            Easy_Roles_Manager::log_change( 'created', 'role_' . $i, 'Role ' . $i );
        }

        $log = get_option( 'easy_roles_changelog', array() );

        $this->assertCount( 100, $log );
        $this->assertSame( 'role_104', $log[0]['role_slug'] );
        $this->assertSame( 'role_5', $log[99]['role_slug'] );
    }

    public function test_get_changelog_returns_requested_slice() {
        Easy_Roles_Manager::log_change( 'created', 'role_a', 'Role A' );
        Easy_Roles_Manager::log_change( 'updated', 'role_b', 'Role B' );
        Easy_Roles_Manager::log_change( 'deleted', 'role_c', 'Role C' );

        $entries = Easy_Roles_Manager::get_changelog( 2, 1 );

        $this->assertCount( 2, $entries );
        $this->assertSame( 'role_b', $entries[0]['role_slug'] );
        $this->assertSame( 'role_a', $entries[1]['role_slug'] );
    }

    public function test_get_changelog_count_returns_total_entries() {
        Easy_Roles_Manager::log_change( 'created', 'role_a', 'Role A' );
        Easy_Roles_Manager::log_change( 'updated', 'role_b', 'Role B' );

        $this->assertSame( 2, Easy_Roles_Manager::get_changelog_count() );
    }
}
