<?php

require_once dirname( __DIR__ ) . '/Support/EasyRolesTestHelpers.php';

class EasyRolesCapabilitiesTest extends WP_UnitTestCase {

    use Easy_Roles_Test_Helpers;

    public function tearDown(): void {
        $this->cleanup_easy_roles_state();
        parent::tearDown();
    }

    public function test_get_grouped_capabilities_returns_expected_core_groups() {
        $groups = Easy_Roles_Capabilities::get_grouped_capabilities();

        $this->assertArrayHasKey( 'users', $groups );
        $this->assertArrayHasKey( 'posts', $groups );
        $this->assertArrayHasKey( 'administration', $groups );
        $this->assertArrayHasKey( 'read', $groups['users']['caps'] );
        $this->assertArrayHasKey( 'manage_options', $groups['administration']['caps'] );
    }

    public function test_get_flat_capabilities_returns_translated_description_map() {
        $flat_caps = Easy_Roles_Capabilities::get_flat_capabilities();

        $this->assertArrayHasKey( 'read', $flat_caps );
        $this->assertSame( 'Minimum capability to access the admin area and profile section; implies being able to log in.', $flat_caps['read'] );
    }

    public function test_get_extra_registered_caps_returns_caps_not_present_in_static_map() {
        $role = Easy_Roles_Manager::create_role(
            'custom_cap_role',
            'Custom Cap Role',
            array(
                'read'              => true,
                'manage_crm_portal' => true,
            )
        );

        $this->assertInstanceOf( WP_Role::class, $role );
        $this->track_role( 'custom_cap_role' );

        $extra_caps = Easy_Roles_Capabilities::get_extra_registered_caps();

        $this->assertArrayHasKey( 'manage_crm_portal', $extra_caps );
        $this->assertArrayNotHasKey( 'read', $extra_caps );
    }
}
