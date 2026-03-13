<?php

require_once dirname( __DIR__ ) . '/Support/EasyRolesTestHelpers.php';

if ( ! class_exists( 'WooCommerce' ) ) {
    class WooCommerce {
    }
}

class EasyRolesWooCommerceTest extends WP_UnitTestCase {

    use Easy_Roles_Test_Helpers;

    public function tearDown(): void {
        $this->cleanup_easy_roles_state();
        parent::tearDown();
    }

    public function test_is_active_returns_true_when_woocommerce_class_exists() {
        $this->assertTrue( Easy_Roles_WooCommerce::is_active() );
    }

    public function test_get_protected_roles_returns_expected_woocommerce_roles() {
        $this->assertSame( array( 'shop_manager', 'customer' ), Easy_Roles_WooCommerce::get_protected_roles() );
    }

    public function test_get_wc_capabilities_returns_expected_groups_and_caps() {
        $groups = Easy_Roles_WooCommerce::get_wc_capabilities();

        $this->assertArrayHasKey( 'woocommerce_management', $groups );
        $this->assertArrayHasKey( 'woocommerce_products', $groups );
        $this->assertArrayHasKey( 'manage_woocommerce', $groups['woocommerce_management']['caps'] );
        $this->assertArrayHasKey( 'edit_products', $groups['woocommerce_products']['caps'] );
    }

    public function test_get_extra_wc_caps_returns_unknown_woocommerce_caps() {
        $role = Easy_Roles_Manager::create_role(
            'wc_extra_role',
            'WC Extra Role',
            array(
                'read'                           => true,
                'manage_woocommerce_custom_tab' => true,
            )
        );

        $this->assertInstanceOf( WP_Role::class, $role );
        $this->track_role( 'wc_extra_role' );

        $extra_wc_caps = Easy_Roles_WooCommerce::get_extra_wc_caps();

        $this->assertArrayHasKey( 'manage_woocommerce_custom_tab', $extra_wc_caps );
    }
}
