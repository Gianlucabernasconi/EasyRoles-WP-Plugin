<?php

require_once dirname( __DIR__ ) . '/Support/EasyRolesTestHelpers.php';

class EasyRolesLifecycleTest extends WP_UnitTestCase {

    use Easy_Roles_Test_Helpers;

    public function tearDown(): void {
        $this->cleanup_easy_roles_state();
        parent::tearDown();
    }

    public function test_activation_initializes_plugin_options() {
        delete_option( 'easy_roles_version' );
        delete_option( 'easy_roles_custom_roles' );
        delete_option( 'easy_roles_changelog' );

        easy_roles_activate();

        $this->assertSame( EASY_ROLES_VERSION, get_option( 'easy_roles_version' ) );
        $this->assertSame( array(), get_option( 'easy_roles_custom_roles' ) );
        $this->assertSame( array(), get_option( 'easy_roles_changelog' ) );
    }

    public function test_activation_is_idempotent_for_existing_options() {
        update_option( 'easy_roles_version', '0.9.0' );
        update_option( 'easy_roles_custom_roles', array( 'existing_role' ) );
        update_option( 'easy_roles_changelog', array( array( 'role_slug' => 'existing_role' ) ) );

        easy_roles_activate();

        $this->assertSame( '0.9.0', get_option( 'easy_roles_version' ) );
        $this->assertSame( array( 'existing_role' ), get_option( 'easy_roles_custom_roles' ) );
        $this->assertCount( 1, get_option( 'easy_roles_changelog' ) );
    }

    public function test_deactivation_keeps_plugin_data_intact() {
        easy_roles_activate();
        update_option( 'easy_roles_custom_roles', array( 'saved_role' ) );
        update_option( 'easy_roles_changelog', array( array( 'role_slug' => 'saved_role' ) ) );

        easy_roles_deactivate();

        $this->assertSame( EASY_ROLES_VERSION, get_option( 'easy_roles_version' ) );
        $this->assertSame( array( 'saved_role' ), get_option( 'easy_roles_custom_roles' ) );
        $this->assertCount( 1, get_option( 'easy_roles_changelog' ) );
    }

    public function test_plugin_bootstrap_registers_expected_hooks() {
        $this->assertSame( 10, has_action( 'plugins_loaded', 'easy_roles_load_textdomain' ) );
        $this->assertNotFalse( has_filter( 'activate_' . EASY_ROLES_BASENAME, 'easy_roles_activate' ) );
        $this->assertNotFalse( has_filter( 'deactivate_' . EASY_ROLES_BASENAME, 'easy_roles_deactivate' ) );
        $this->assertSame( EASY_ROLES_VERSION, constant( 'EASY_ROLES_VERSION' ) );
    }

    public function test_uninstall_only_removes_plugin_owned_data() {
        easy_roles_activate();

        $role = Easy_Roles_Manager::create_role( 'owned_cleanup_role', 'Owned Cleanup Role', array( 'read' => true ) );

        $this->assertInstanceOf( WP_Role::class, $role );
        update_option( 'easy_roles_custom_roles', array( 'owned_cleanup_role' ) );
        update_option( 'unrelated_option', 'keep-me' );

        if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
            define( 'WP_UNINSTALL_PLUGIN', true );
        }

        require dirname( __DIR__, 2 ) . '/uninstall.php';

        $this->assertFalse( wp_roles()->is_role( 'owned_cleanup_role' ) );
        $this->assertSame( 'keep-me', get_option( 'unrelated_option' ) );
        delete_option( 'unrelated_option' );
    }

    public function test_uninstall_removes_plugin_options_meta_and_custom_roles() {
        easy_roles_activate();

        $role = Easy_Roles_Manager::create_role( 'cleanup_role', 'Cleanup Role', array( 'read' => true ) );

        $this->assertInstanceOf( WP_Role::class, $role );

        $user_id = $this->create_administrator_user();
        update_user_meta( $user_id, 'easy_roles_lang', 'es' );

        if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
            define( 'WP_UNINSTALL_PLUGIN', true );
        }

        require dirname( __DIR__, 2 ) . '/uninstall.php';

        $this->assertFalse( wp_roles()->is_role( 'cleanup_role' ) );
        $this->assertFalse( get_option( 'easy_roles_version', false ) );
        $this->assertFalse( get_option( 'easy_roles_custom_roles', false ) );
        $this->assertFalse( get_option( 'easy_roles_changelog', false ) );
        $this->assertSame( '', get_user_meta( $user_id, 'easy_roles_lang', true ) );
    }
}
