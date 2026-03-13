<?php

require_once dirname( __DIR__ ) . '/Support/EasyRolesTestHelpers.php';

class Easy_Roles_Test_WP_Die_Exception extends Exception {
}

class EasyRolesAdminTest extends WP_UnitTestCase {

    use Easy_Roles_Test_Helpers;

    public function tearDown(): void {
        $this->cleanup_easy_roles_state();
        wp_dequeue_script( 'easy-roles-admin' );
        wp_dequeue_style( 'easy-roles-admin' );
        parent::tearDown();
    }

    public function test_init_registers_admin_hooks() {
        Easy_Roles_Admin::init();

        $this->assertNotFalse( has_action( 'admin_menu', array( 'Easy_Roles_Admin', 'register_menu' ) ) );
        $this->assertNotFalse( has_action( 'admin_enqueue_scripts', array( 'Easy_Roles_Admin', 'enqueue_assets' ) ) );
    }

    public function test_register_menu_stores_hook_suffix() {
        $admin_id = $this->create_administrator_user();
        wp_set_current_user( $admin_id );

        Easy_Roles_Admin::register_menu();

        global $admin_page_hooks;
        $reflection = new ReflectionClass( 'Easy_Roles_Admin' );
        $property   = $reflection->getProperty( 'hook_suffix' );

        $property->setAccessible( true );
        $hook_suffix = $property->getValue();

        $this->assertArrayHasKey( 'easy-roles', $admin_page_hooks );
        $this->assertNotEmpty( $hook_suffix );
    }

    public function test_enqueue_assets_only_runs_for_plugin_hook() {
        $admin_id = $this->create_administrator_user();
        wp_set_current_user( $admin_id );
        Easy_Roles_Admin::register_menu();

        Easy_Roles_Admin::enqueue_assets( 'dashboard_page_fake' );

        $this->assertFalse( wp_style_is( 'easy-roles-admin', 'enqueued' ) );
        $this->assertFalse( wp_script_is( 'easy-roles-admin', 'enqueued' ) );
    }

    public function test_enqueue_assets_localizes_expected_data_on_plugin_hook() {
        $admin_id = $this->create_administrator_user();
        wp_set_current_user( $admin_id );
        Easy_Roles_Admin::register_menu();

        $reflection = new ReflectionClass( 'Easy_Roles_Admin' );
        $property   = $reflection->getProperty( 'hook_suffix' );
        $property->setAccessible( true );

        Easy_Roles_Admin::enqueue_assets( $property->getValue() );

        global $wp_scripts;
        $data = $wp_scripts->get_data( 'easy-roles-admin', 'data' );

        $this->assertTrue( wp_style_is( 'easy-roles-admin', 'enqueued' ) );
        $this->assertTrue( wp_script_is( 'easy-roles-admin', 'enqueued' ) );
        $this->assertStringContainsString( 'easyRolesData', $data );
        $this->assertStringContainsString( 'ajaxUrl', $data );
        $this->assertStringContainsString( 'capGroups', $data );
    }

    public function test_render_page_blocks_users_without_manage_options() {
        $subscriber_id = $this->create_subscriber_user();
        wp_set_current_user( $subscriber_id );

        add_filter( 'wp_die_handler', array( $this, 'filter_wp_die_handler' ) );

        try {
            Easy_Roles_Admin::render_page();
            $this->fail( 'Expected render_page() to call wp_die().' );
        } catch ( Easy_Roles_Test_WP_Die_Exception $exception ) {
            $this->assertSame( 'You do not have permission to access this page.', $exception->getMessage() );
        }

        remove_filter( 'wp_die_handler', array( $this, 'filter_wp_die_handler' ) );
    }

    public function filter_wp_die_handler() {
        return array( $this, 'handle_wp_die' );
    }

    public function handle_wp_die( $message ) {
        // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Test helper converts wp_die HTML into plain exception text.
        throw new Easy_Roles_Test_WP_Die_Exception( wp_strip_all_tags( $message ) );
    }
}
