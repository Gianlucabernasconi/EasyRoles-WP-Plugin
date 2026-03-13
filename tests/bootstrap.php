<?php

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir && ! empty( getenv( 'HOME' ) ) ) {
    $_tests_dir = rtrim( getenv( 'HOME' ), '/\\' ) . '/.local/share/wordpress-tests-lib';
}

if ( ! $_tests_dir && ! empty( getenv( 'LOCALAPPDATA' ) ) ) {
    $_tests_dir = rtrim( getenv( 'LOCALAPPDATA' ), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! $_tests_dir ) {
    $_tests_dir = '/tmp/wordpress-tests-lib';
}

require_once $_tests_dir . '/includes/functions.php';

function easy_roles_manually_load_plugin() {
    require dirname( __DIR__ ) . '/easy-roles-gb.php';

    if ( ! class_exists( 'Easy_Roles_Capabilities' ) ) {
        require_once dirname( __DIR__ ) . '/includes/class-easy-roles-capabilities.php';
    }

    if ( ! class_exists( 'Easy_Roles_Manager' ) ) {
        require_once dirname( __DIR__ ) . '/includes/class-easy-roles-manager.php';
    }

    if ( ! class_exists( 'Easy_Roles_WooCommerce' ) ) {
        require_once dirname( __DIR__ ) . '/includes/class-easy-roles-woocommerce.php';
    }

    if ( ! class_exists( 'Easy_Roles_Admin' ) ) {
        require_once dirname( __DIR__ ) . '/includes/class-easy-roles-admin.php';
    }

    if ( ! class_exists( 'Easy_Roles_Ajax' ) ) {
        require_once dirname( __DIR__ ) . '/includes/class-easy-roles-ajax.php';
    }

    Easy_Roles_Ajax::init();
}
tests_add_filter( 'muplugins_loaded', 'easy_roles_manually_load_plugin' );

require $_tests_dir . '/includes/bootstrap.php';
require_once $_tests_dir . '/includes/testcase-ajax.php';
