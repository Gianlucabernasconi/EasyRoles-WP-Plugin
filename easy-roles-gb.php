<?php
/**
 * Plugin Name:       Easy Roles
 * Plugin URI:        https://gianlucabernasconi.cl/
 * Description:       Easy roles es un plugin ligero y optimizado para crear roles detallados de forma sencilla.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Gianluca Bernasconi
 * Author URI:        https://gianlucabernasconi.cl/
 * License:           GNU AGPLv3
 * Text Domain:       easy-roles-gb
 * Domain Path:       /languages
 * Network:           true
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* ─── Constants ─────────────────────────────────────────────────────── */
define( 'EASY_ROLES_VERSION', '1.0.0' );
define( 'EASY_ROLES_FILE', __FILE__ );
define( 'EASY_ROLES_PATH', plugin_dir_path( __FILE__ ) );
define( 'EASY_ROLES_URL', plugin_dir_url( __FILE__ ) );
define( 'EASY_ROLES_BASENAME', plugin_basename( __FILE__ ) );

/* ─── i18n ──────────────────────────────────────────────────────────── */
add_action( 'plugins_loaded', 'easy_roles_load_textdomain' );

function easy_roles_load_textdomain() {
    load_plugin_textdomain(
        'easy-roles-gb',
        false,
        dirname( EASY_ROLES_BASENAME ) . '/languages'
    );
}

/* ─── Admin-only bootstrap ──────────────────────────────────────────── */
if ( is_admin() ) {
    require_once EASY_ROLES_PATH . 'includes/class-easy-roles-capabilities.php';
    require_once EASY_ROLES_PATH . 'includes/class-easy-roles-manager.php';
    require_once EASY_ROLES_PATH . 'includes/class-easy-roles-woocommerce.php';
    require_once EASY_ROLES_PATH . 'includes/class-easy-roles-admin.php';
    require_once EASY_ROLES_PATH . 'includes/class-easy-roles-ajax.php';

    /* Initialise admin UI */
    add_action( 'plugins_loaded', array( 'Easy_Roles_Admin', 'init' ) );

    /* Initialise AJAX handlers */
    add_action( 'plugins_loaded', array( 'Easy_Roles_Ajax', 'init' ) );
}

/* ─── Activation / Deactivation (top-level per WP guideline) ────────── */
register_activation_hook( __FILE__, 'easy_roles_activate' );
register_deactivation_hook( __FILE__, 'easy_roles_deactivate' );

function easy_roles_activate() {
    /* Store initial plugin version for future migrations */
    if ( ! get_option( 'easy_roles_version' ) ) {
        add_option( 'easy_roles_version', EASY_ROLES_VERSION, '', 'no' );
    }
    /* Track roles the plugin creates so uninstall can clean up */
    if ( ! get_option( 'easy_roles_custom_roles' ) ) {
        add_option( 'easy_roles_custom_roles', array(), '', 'no' );
    }
}

function easy_roles_deactivate() {
    /* Nothing destructive on deactivation — only uninstall removes data */
}
