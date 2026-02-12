<?php
/**
 * Easy Roles – WooCommerce Integration
 *
 * Passive detection of WooCommerce — only loads capabilities when WC is active.
 * Scans $wp_roles dynamically so future WC versions are auto-detected.
 *
 * @package EasyRoles
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Easy_Roles_WooCommerce {

    /**
     * Whether WooCommerce is active.
     *
     * @return bool
     */
    public static function is_active() {
        return class_exists( 'WooCommerce' );
    }

    /**
     * Protected WooCommerce role slugs that cannot be deleted.
     *
     * @return string[]
     */
    public static function get_protected_roles() {
        return array( 'shop_manager', 'customer' );
    }

    /**
     * WooCommerce capabilities map (from guia.md).
     *
     * @return array<string, array{label_es: string, label_en: string, icon: string, caps: array}>
     */
    public static function get_wc_capabilities() {

        return array(
            'woocommerce_management' => array(
                'label_es'      => 'WooCommerce – Gestión',
                'label_en'      => 'WooCommerce – Management',
                'icon'          => 'dashicons-store',
                'group_desc_es' => 'Permisos centrales de la tienda: acceso al menú de WooCommerce, gestión de pedidos, visualización de informes de ventas, administración de cupones y configuración global de la tienda (moneda, impuestos, envíos, pasarelas de pago).',
                'group_desc_en' => 'Core store permissions: WooCommerce menu access, order management, sales reports viewing, coupon administration, and global store configuration (currency, taxes, shipping, payment gateways).',
                'caps'          => array(
                    'manage_woocommerce' => array(
                        'desc_es' => 'Capacidad central de gestión de la tienda: abre el menú WooCommerce y da acceso a la mayoría de pantallas internas de administración.',
                        'desc_en' => 'Core store management capability: opens the WooCommerce menu and gives access to most internal admin screens.',
                    ),
                    'manage_woocommerce_orders' => array(
                        'desc_es' => 'Permite gestionar pedidos: verlos, cambiarles el estado, añadir notas, actualizar detalles de facturación/envío y procesar reembolsos.',
                        'desc_en' => 'Allows managing orders: viewing, changing status, adding notes, updating billing/shipping details, and processing refunds.',
                    ),
                    'view_woocommerce_reports' => array(
                        'desc_es' => 'Permite acceder a los informes/analytics de WooCommerce, incluyendo ventas, ingresos, impuestos, stock y clientes.',
                        'desc_en' => 'Allows accessing WooCommerce reports/analytics, including sales, revenue, taxes, stock, and customers.',
                    ),
                    'manage_woocommerce_coupons' => array(
                        'desc_es' => 'Permite crear, editar y borrar cupones de descuento usados en el proceso de compra.',
                        'desc_en' => 'Allows creating, editing, and deleting discount coupons used in the checkout process.',
                    ),
                    'manage_woocommerce_settings' => array(
                        'desc_es' => 'Permite cambiar ajustes globales de WooCommerce: moneda, impuestos, métodos de pago, métodos de envío, páginas de tienda, etc.',
                        'desc_en' => 'Allows changing global WooCommerce settings: currency, taxes, payment methods, shipping methods, store pages, etc.',
                    ),
                ),
            ),

            'woocommerce_products' => array(
                'label_es'      => 'WooCommerce – Productos',
                'label_en'      => 'WooCommerce – Products',
                'icon'          => 'dashicons-products',
                'group_desc_es' => 'Controla la gestión del catálogo de productos: crear y editar productos (simples, variables, agrupados), modificar precios e inventario, publicarlos en la tienda, eliminarlos y acceder a productos privados.',
                'group_desc_en' => 'Controls product catalog management: creating and editing products (simple, variable, grouped), modifying prices and inventory, publishing them in the store, deleting them, and accessing private products.',
                'caps'          => array(
                    'edit_products' => array(
                        'desc_es' => 'Permite crear y editar productos (simples, variables, agrupados, etc.), modificando precios, inventario, descripciones y atributos.',
                        'desc_en' => 'Allows creating and editing products (simple, variable, grouped, etc.), modifying prices, inventory, descriptions, and attributes.',
                    ),
                    'delete_products' => array(
                        'desc_es' => 'Permite borrar productos del catálogo, enviándolos a la papelera o borrándolos de forma permanente.',
                        'desc_en' => 'Allows deleting products from the catalog, moving them to trash or permanently.',
                    ),
                    'publish_products' => array(
                        'desc_es' => 'Permite publicar productos para que sean visibles y comprables en la tienda.',
                        'desc_en' => 'Allows publishing products so they are visible and purchasable in the store.',
                    ),
                    'read_private_products' => array(
                        'desc_es' => 'Permite ver productos marcados como privados, útiles para catálogos restringidos.',
                        'desc_en' => 'Allows viewing products marked as private, useful for restricted catalogs.',
                    ),
                ),
            ),

            'woocommerce_orders' => array(
                'label_es'      => 'WooCommerce – Pedidos',
                'label_en'      => 'WooCommerce – Orders',
                'icon'          => 'dashicons-clipboard',
                'group_desc_es' => 'Gestiona los pedidos de la tienda: editar detalles (dirección, items, totales), ver el historial de pedidos, cambiar estados y eliminar pedidos de la base de datos. La eliminación es una acción crítica que debería reservarse a administradores.',
                'group_desc_en' => 'Manages store orders: editing details (address, items, totals), viewing order history, changing statuses, and deleting orders from the database. Deletion is a critical action that should be reserved for administrators.',
                'caps'          => array(
                    'edit_shop_order' => array(
                        'desc_es' => 'Permite editar objetos de tipo "shop_order": contenidos de un pedido, dirección del cliente, items, totales, etc.',
                        'desc_en' => 'Allows editing "shop_order" objects: order contents, customer address, items, totals, etc.',
                    ),
                    'read_shop_order' => array(
                        'desc_es' => 'Permite ver pedidos en el panel. En un rol de gestión, implica acceso a todos los pedidos de la tienda.',
                        'desc_en' => 'Allows viewing orders in the panel. For management roles, implies access to all store orders.',
                    ),
                    'delete_shop_order' => array(
                        'desc_es' => 'Permite borrar pedidos de la base de datos, acción crítica que suele reservarse a administradores.',
                        'desc_en' => 'Allows deleting orders from the database, a critical action usually reserved for administrators.',
                    ),
                    'view_woocommerce_orders' => array(
                        'desc_es' => 'Permite ver el historial de pedidos asociados a su usuario.',
                        'desc_en' => 'Allows viewing order history associated with the user.',
                    ),
                ),
            ),

            'woocommerce_coupons' => array(
                'label_es'      => 'WooCommerce – Cupones',
                'label_en'      => 'WooCommerce – Coupons',
                'icon'          => 'dashicons-tickets-alt',
                'group_desc_es' => 'Administra los cupones de descuento de la tienda: crear y editar cupones con condiciones de uso, límites, tipos de descuento y productos aplicables. También permite ver listados y eliminar cupones.',
                'group_desc_en' => 'Manages store discount coupons: creating and editing coupons with usage conditions, limits, discount types, and applicable products. Also allows viewing listings and deleting coupons.',
                'caps'          => array(
                    'edit_shop_coupon' => array(
                        'desc_es' => 'Permite editar cupones existentes: condiciones, límites de uso, tipos de descuento y productos aplicables.',
                        'desc_en' => 'Allows editing existing coupons: conditions, usage limits, discount types, and applicable products.',
                    ),
                    'read_shop_coupon' => array(
                        'desc_es' => 'Permite ver el listado de cupones y sus detalles.',
                        'desc_en' => 'Allows viewing the coupon list and their details.',
                    ),
                    'delete_shop_coupon' => array(
                        'desc_es' => 'Permite eliminar cupones para que no puedan seguir utilizándose.',
                        'desc_en' => 'Allows deleting coupons so they can no longer be used.',
                    ),
                ),
            ),

            'woocommerce_webhooks' => array(
                'label_es'      => 'WooCommerce – Webhooks',
                'label_en'      => 'WooCommerce – Webhooks',
                'icon'          => 'dashicons-rest-api',
                'group_desc_es' => 'Gestiona las notificaciones automáticas (webhooks) que WooCommerce envía a servicios externos cuando ocurren ciertos eventos (nuevo pedido, producto actualizado, etc.). Permite crear, editar, ver y eliminar webhooks.',
                'group_desc_en' => 'Manages automatic notifications (webhooks) that WooCommerce sends to external services when certain events occur (new order, product updated, etc.). Allows creating, editing, viewing, and deleting webhooks.',
                'caps'          => array(
                    'edit_shop_webhook' => array(
                        'desc_es' => 'Permite crear y editar webhooks de WooCommerce, configurando eventos y endpoints externos.',
                        'desc_en' => 'Allows creating and editing WooCommerce webhooks, configuring events and external endpoints.',
                    ),
                    'read_shop_webhook' => array(
                        'desc_es' => 'Permite ver webhooks configurados, su estado y detalles de envío.',
                        'desc_en' => 'Allows viewing configured webhooks, their status, and delivery details.',
                    ),
                    'delete_shop_webhook' => array(
                        'desc_es' => 'Permite borrar webhooks, deteniendo el envío de notificaciones a los endpoints configurados.',
                        'desc_en' => 'Allows deleting webhooks, stopping notifications to configured endpoints.',
                    ),
                ),
            ),

            'woocommerce_customer' => array(
                'label_es'      => 'WooCommerce – Cliente',
                'label_en'      => 'WooCommerce – Customer',
                'icon'          => 'dashicons-businessman',
                'group_desc_es' => 'Permisos del rol Cliente (comprador): permite actualizar los datos de su propia cuenta como nombre, email, contraseña, y direcciones de envío y facturación desde la página "Mi Cuenta".',
                'group_desc_en' => 'Customer role permissions: allows updating own account data such as name, email, password, and shipping and billing addresses from the "My Account" page.',
                'caps'          => array(
                    'edit_account' => array(
                        'desc_es' => 'Permite actualizar los datos de su cuenta: nombre, dirección de email, contraseña, direcciones de envío y facturación.',
                        'desc_en' => 'Allows updating account data: name, email address, password, shipping and billing addresses.',
                    ),
                ),
            ),
        );
    }

    /**
     * Scan $wp_roles for any WC-specific caps not in our static map.
     *
     * @return array<string, bool>
     */
    public static function get_extra_wc_caps() {

        global $wp_roles;

        if ( ! isset( $wp_roles ) ) {
            $wp_roles = wp_roles();
        }

        $known = array();
        foreach ( self::get_wc_capabilities() as $group ) {
            foreach ( array_keys( $group['caps'] ) as $cap ) {
                $known[] = $cap;
            }
        }

        $wc_caps = array();
        $wc_prefixes = array( 'manage_woocommerce', 'view_woocommerce', 'edit_shop', 'read_shop', 'delete_shop', 'publish_shop', 'edit_product', 'delete_product', 'publish_product', 'read_private_product' );

        foreach ( $wp_roles->roles as $role_data ) {
            if ( ! isset( $role_data['capabilities'] ) || ! is_array( $role_data['capabilities'] ) ) {
                continue;
            }
            foreach ( array_keys( $role_data['capabilities'] ) as $cap ) {
                if ( in_array( $cap, $known, true ) ) {
                    continue;
                }
                foreach ( $wc_prefixes as $prefix ) {
                    if ( strpos( $cap, $prefix ) === 0 || strpos( $cap, 'woocommerce' ) !== false ) {
                        $wc_caps[ $cap ] = true;
                        break;
                    }
                }
            }
        }

        return $wc_caps;
    }
}
