<?php
/**
 * Easy Roles – Capabilities Reference
 *
 * Static map of every WordPress (and WooCommerce) capability with its
 * human-readable description, sourced from guia.md.
 *
 * @package EasyRoles
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Easy_Roles_Capabilities {

    /**
     * Grouped capabilities with descriptions (from guia.md).
     *
     * @return array<string, array<string, array{desc_es: string, desc_en: string}>>
     */
    public static function get_grouped_capabilities() {

        $groups = array();

        /* ── Users ──────────────────────────────────────────────── */
        $groups['users'] = array(
            'label_es'      => 'Usuarios',
            'label_en'      => 'Users',
            'icon'          => 'dashicons-admin-users',
            'group_desc_es' => 'Define quién puede ver, crear, editar, eliminar y cambiar el rol de otros usuarios del sitio. Incluye el acceso básico al panel de administración (read) y la capacidad de promover usuarios a otros roles.',
            'group_desc_en' => 'Defines who can view, create, edit, delete, and change the role of other site users. Includes basic admin panel access (read) and the ability to promote users to other roles.',
            'caps'          => array(
                'read' => array(
                    'desc_es' => 'Capacidad mínima para acceder al área de administración y a la sección de perfil; implica poder iniciar sesión.',
                    'desc_en' => 'Minimum capability to access the admin area and profile section; implies being able to log in.',
                ),
                'list_users' => array(
                    'desc_es' => 'Permite acceder al listado de usuarios del sitio y ver sus datos básicos.',
                    'desc_en' => 'Allows accessing the site user list and viewing their basic data.',
                ),
                'create_users' => array(
                    'desc_es' => 'Permite crear nuevos usuarios en una instalación de sitio único.',
                    'desc_en' => 'Allows creating new users in a single-site installation.',
                ),
                'add_users' => array(
                    'desc_es' => 'Permite añadir usuarios (por ejemplo asignar usuarios existentes a un sitio en Multisite).',
                    'desc_en' => 'Allows adding users (e.g., assigning existing users to a site in Multisite).',
                ),
                'edit_users' => array(
                    'desc_es' => 'Permite editar datos de otros usuarios (nombre, email, rol, etc.).',
                    'desc_en' => 'Allows editing other users\' data (name, email, role, etc.).',
                ),
                'delete_users' => array(
                    'desc_es' => 'Permite borrar usuarios del sitio, normalmente reasignando o eliminando su contenido.',
                    'desc_en' => 'Allows deleting users from the site, typically reassigning or removing their content.',
                ),
                'promote_users' => array(
                    'desc_es' => 'Permite cambiar el rol de otros usuarios, otorgando o retirando permisos.',
                    'desc_en' => 'Allows changing other users\' role, granting or revoking permissions.',
                ),
                'remove_users' => array(
                    'desc_es' => 'Permite retirar usuarios de un sitio (por ejemplo en Multisite) sin borrar su cuenta global.',
                    'desc_en' => 'Allows removing users from a site (e.g., in Multisite) without deleting their global account.',
                ),
            ),
        );

        /* ── Posts ───────────────────────────────────────────────── */
        $groups['posts'] = array(
            'label_es'      => 'Entradas',
            'label_en'      => 'Posts',
            'icon'          => 'dashicons-admin-post',
            'group_desc_es' => 'Controla los permisos sobre las entradas del blog: crear, editar, publicar y eliminar posts propios o de otros usuarios. Ideal para definir qué nivel de acceso tiene cada rol sobre el contenido editorial.',
            'group_desc_en' => 'Controls permissions over blog posts: creating, editing, publishing, and deleting own or others\' posts. Ideal for defining each role\'s access level to editorial content.',
            'caps'          => array(
                'edit_posts' => array(
                    'desc_es' => 'Permite crear y editar posts propios (borradores y publicados).',
                    'desc_en' => 'Allows creating and editing own posts (drafts and published).',
                ),
                'edit_others_posts' => array(
                    'desc_es' => 'Permite editar posts creados por otros usuarios.',
                    'desc_en' => 'Allows editing posts created by other users.',
                ),
                'edit_published_posts' => array(
                    'desc_es' => 'Permite editar posts ya publicados.',
                    'desc_en' => 'Allows editing already published posts.',
                ),
                'edit_private_posts' => array(
                    'desc_es' => 'Permite editar posts con visibilidad privada.',
                    'desc_en' => 'Allows editing posts with private visibility.',
                ),
                'publish_posts' => array(
                    'desc_es' => 'Permite cambiar posts de estado borrador a publicado.',
                    'desc_en' => 'Allows changing posts from draft to published status.',
                ),
                'delete_posts' => array(
                    'desc_es' => 'Permite borrar entradas propias del usuario.',
                    'desc_en' => 'Allows deleting own posts.',
                ),
                'delete_others_posts' => array(
                    'desc_es' => 'Permite borrar entradas (posts) creadas por otros usuarios.',
                    'desc_en' => 'Allows deleting posts created by other users.',
                ),
                'delete_published_posts' => array(
                    'desc_es' => 'Permite borrar posts que ya están publicados.',
                    'desc_en' => 'Allows deleting already published posts.',
                ),
                'delete_private_posts' => array(
                    'desc_es' => 'Permite borrar posts que estén marcados como privados.',
                    'desc_en' => 'Allows deleting posts marked as private.',
                ),
                'read_private_posts' => array(
                    'desc_es' => 'Permite ver posts con visibilidad privada.',
                    'desc_en' => 'Allows viewing posts with private visibility.',
                ),
            ),
        );

        /* ── Pages ──────────────────────────────────────────────── */
        $groups['pages'] = array(
            'label_es'      => 'Páginas',
            'label_en'      => 'Pages',
            'icon'          => 'dashicons-admin-page',
            'group_desc_es' => 'Controla los permisos sobre las páginas estáticas del sitio (Inicio, Contacto, etc.): crear, editar, publicar y eliminar. Las páginas suelen formar la estructura principal del sitio web.',
            'group_desc_en' => 'Controls permissions over static site pages (Home, Contact, etc.): creating, editing, publishing, and deleting. Pages typically form the main structure of the website.',
            'caps'          => array(
                'edit_pages' => array(
                    'desc_es' => 'Permite crear y editar páginas propias.',
                    'desc_en' => 'Allows creating and editing own pages.',
                ),
                'edit_others_pages' => array(
                    'desc_es' => 'Permite editar páginas creadas por otros usuarios (contenido, título, slug, estado, etc.).',
                    'desc_en' => 'Allows editing pages created by other users (content, title, slug, status, etc.).',
                ),
                'edit_published_pages' => array(
                    'desc_es' => 'Permite editar páginas ya publicadas (contenido en producción).',
                    'desc_en' => 'Allows editing already published pages (production content).',
                ),
                'edit_private_pages' => array(
                    'desc_es' => 'Permite editar páginas con visibilidad privada.',
                    'desc_en' => 'Allows editing pages with private visibility.',
                ),
                'publish_pages' => array(
                    'desc_es' => 'Permite cambiar páginas de estado borrador a publicado.',
                    'desc_en' => 'Allows changing pages from draft to published status.',
                ),
                'delete_pages' => array(
                    'desc_es' => 'Permite borrar páginas propias, moviéndolas a la papelera o eliminándolas definitivamente.',
                    'desc_en' => 'Allows deleting own pages, moving them to trash or permanently.',
                ),
                'delete_others_pages' => array(
                    'desc_es' => 'Permite borrar páginas creadas por otros usuarios, no solo las propias.',
                    'desc_en' => 'Allows deleting pages created by other users, not just own.',
                ),
                'delete_published_pages' => array(
                    'desc_es' => 'Permite borrar páginas que ya están publicadas (visibles en el sitio).',
                    'desc_en' => 'Allows deleting pages that are already published (visible on the site).',
                ),
                'delete_private_pages' => array(
                    'desc_es' => 'Permite borrar páginas que estén marcadas como privadas.',
                    'desc_en' => 'Allows deleting pages marked as private.',
                ),
                'read_private_pages' => array(
                    'desc_es' => 'Permite ver páginas con visibilidad privada.',
                    'desc_en' => 'Allows viewing pages with private visibility.',
                ),
            ),
        );

        /* ── Media & Content ────────────────────────────────────── */
        $groups['media'] = array(
            'label_es'      => 'Medios y Contenido',
            'label_en'      => 'Media & Content',
            'icon'          => 'dashicons-admin-media',
            'group_desc_es' => 'Gestiona la subida de archivos a la biblioteca de medios, la moderación de comentarios, las categorías de contenido y las herramientas de importación/exportación. También incluye el permiso de HTML sin filtrar.',
            'group_desc_en' => 'Manages file uploads to the media library, comment moderation, content categories, and import/export tools. Also includes the unfiltered HTML permission.',
            'caps'          => array(
                'upload_files' => array(
                    'desc_es' => 'Permite subir archivos (imágenes, documentos, etc.) a la biblioteca de medios.',
                    'desc_en' => 'Allows uploading files (images, documents, etc.) to the media library.',
                ),
                'manage_categories' => array(
                    'desc_es' => 'Permite crear, editar y eliminar categorías de posts.',
                    'desc_en' => 'Allows creating, editing, and deleting post categories.',
                ),
                'manage_links' => array(
                    'desc_es' => 'Permite gestionar los antiguos "Enlaces/Blogroll" (funcionalidad legacy).',
                    'desc_en' => 'Allows managing the old "Links/Blogroll" (legacy feature).',
                ),
                'moderate_comments' => array(
                    'desc_es' => 'Permite aprobar, rechazar, editar, marcar como spam o borrar comentarios desde el panel.',
                    'desc_en' => 'Allows approving, rejecting, editing, marking as spam, or deleting comments.',
                ),
                'unfiltered_html' => array(
                    'desc_es' => 'Permite guardar contenido HTML sin pasar por los filtros de saneamiento (por ejemplo scripts, iframes, etc.).',
                    'desc_en' => 'Allows saving HTML content without sanitization filters (e.g., scripts, iframes, etc.).',
                ),
                'export' => array(
                    'desc_es' => 'Permite usar la herramienta de exportación de contenido para generar un archivo (por ejemplo XML) con datos del sitio.',
                    'desc_en' => 'Allows using the content export tool to generate a file (e.g., XML) with site data.',
                ),
                'import' => array(
                    'desc_es' => 'Permite usar la herramienta de importación para traer contenido desde otros sitios o plataformas.',
                    'desc_en' => 'Allows using the import tool to bring content from other sites or platforms.',
                ),
            ),
        );



        /* ── Appearance ─────────────────────────────────────────── */
        $groups['appearance'] = array(
            'label_es'      => 'Apariencia',
            'label_en'      => 'Appearance',
            'icon'          => 'dashicons-admin-appearance',
            'group_desc_es' => 'Controla todo lo visual del sitio: cambiar el tema activo, personalizar menús, widgets, cabecera y fondo, instalar/actualizar/eliminar temas, y editar archivos de temas directamente desde el panel.',
            'group_desc_en' => 'Controls everything visual on the site: switching the active theme, customizing menus, widgets, header and background, installing/updating/deleting themes, and editing theme files directly from the panel.',
            'caps'          => array(
                'switch_themes' => array(
                    'desc_es' => 'Permite cambiar el tema activo del sitio.',
                    'desc_en' => 'Allows switching the active site theme.',
                ),
                'edit_theme_options' => array(
                    'desc_es' => 'Permite cambiar ajustes del tema: menús, widgets, cabecera, fondo y demás opciones expuestas por el theme.',
                    'desc_en' => 'Allows changing theme settings: menus, widgets, header, background, and other theme options.',
                ),
                'customize' => array(
                    'desc_es' => 'Permite acceder al Personalizador (Customizer) para modificar opciones visuales en tiempo real.',
                    'desc_en' => 'Allows accessing the Customizer to modify visual options in real time.',
                ),
                'edit_dashboard' => array(
                    'desc_es' => 'Permite modificar y configurar los widgets del escritorio y su disposición.',
                    'desc_en' => 'Allows modifying and configuring dashboard widgets and their layout.',
                ),
                'install_themes' => array(
                    'desc_es' => 'Permite instalar nuevos temas desde el repositorio o subiendo un archivo ZIP.',
                    'desc_en' => 'Allows installing new themes from the repository or by uploading a ZIP file.',
                ),
                'update_themes' => array(
                    'desc_es' => 'Permite actualizar temas instalados a sus versiones más recientes.',
                    'desc_en' => 'Allows updating installed themes to their latest versions.',
                ),
                'delete_themes' => array(
                    'desc_es' => 'Permite borrar temas instalados (si no están en uso).',
                    'desc_en' => 'Allows deleting installed themes (if not in use).',
                ),
                'edit_themes' => array(
                    'desc_es' => 'Permite editar archivos de temas usando el editor interno de WordPress.',
                    'desc_en' => 'Allows editing theme files using the WordPress internal editor.',
                ),
            ),
        );

        /* ── Plugins ────────────────────────────────────────────── */
        $groups['plugins'] = array(
            'label_es'      => 'Plugins',
            'label_en'      => 'Plugins',
            'icon'          => 'dashicons-admin-plugins',
            'group_desc_es' => 'Gestiona las extensiones del sitio: activar/desactivar plugins, instalar nuevos desde el repositorio o por archivo ZIP, actualizar a versiones recientes, eliminar plugins inactivos y editar sus archivos desde el editor integrado.',
            'group_desc_en' => 'Manages site extensions: activating/deactivating plugins, installing new ones from the repository or via ZIP, updating to recent versions, deleting inactive plugins, and editing their files from the built-in editor.',
            'caps'          => array(
                'activate_plugins' => array(
                    'desc_es' => 'Permite activar y desactivar plugins en el sitio, controlando qué funcionalidades están disponibles.',
                    'desc_en' => 'Allows activating and deactivating plugins on the site.',
                ),
                'install_plugins' => array(
                    'desc_es' => 'Permite instalar nuevos plugins desde el repositorio o subiendo un archivo ZIP.',
                    'desc_en' => 'Allows installing new plugins from the repository or by uploading a ZIP file.',
                ),
                'update_plugins' => array(
                    'desc_es' => 'Permite actualizar plugins instalados a sus versiones más recientes.',
                    'desc_en' => 'Allows updating installed plugins to their latest versions.',
                ),
                'delete_plugins' => array(
                    'desc_es' => 'Permite borrar plugins instalados (si no están activos o según permisos).',
                    'desc_en' => 'Allows deleting installed plugins (if not active or per permissions).',
                ),
                'edit_plugins' => array(
                    'desc_es' => 'Permite editar archivos de plugins usando el editor interno de WordPress.',
                    'desc_en' => 'Allows editing plugin files using the WordPress internal editor.',
                ),
                'upload_plugins' => array(
                    'desc_es' => 'Permite subir archivos ZIP de plugins al sistema de archivos.',
                    'desc_en' => 'Allows uploading plugin ZIP files to the filesystem.',
                ),
            ),
        );

        /* ── Administration ─────────────────────────────────────── */
        $groups['administration'] = array(
            'label_es'      => 'Administración',
            'label_en'      => 'Administration',
            'icon'          => 'dashicons-admin-settings',
            'group_desc_es' => 'Permisos de nivel superior: acceder y modificar los ajustes globales del sitio (Ajustes > General, Escritura, Lectura, etc.), ejecutar actualizaciones del core de WordPress, editar archivos del sistema y eliminar el sitio.',
            'group_desc_en' => 'Top-level permissions: accessing and modifying global site settings (Settings > General, Writing, Reading, etc.), running WordPress core updates, editing system files, and deleting the site.',
            'caps'          => array(
                'manage_options' => array(
                    'desc_es' => 'Permite acceder y modificar la mayoría de las opciones del sitio en el menú Ajustes (opciones globales y sensibles).',
                    'desc_en' => 'Allows accessing and modifying most site options in the Settings menu (global and sensitive options).',
                ),
                'update_core' => array(
                    'desc_es' => 'Permite ejecutar la actualización del núcleo de WordPress desde el panel de actualizaciones.',
                    'desc_en' => 'Allows running WordPress core updates from the updates panel.',
                ),
                'edit_files' => array(
                    'desc_es' => 'Permite editar archivos del sistema (de temas o plugins) desde el panel, siempre que el editor esté habilitado.',
                    'desc_en' => 'Allows editing system files (themes or plugins) from the panel, if the editor is enabled.',
                ),
                'delete_site' => array(
                    'desc_es' => 'Permite eliminar por completo el sitio actual (en contextos que lo soportan).',
                    'desc_en' => 'Allows completely deleting the current site (in supported contexts).',
                ),
            ),
        );

        /* ── Multisite ──────────────────────────────────────────── */
        $groups['multisite'] = array(
            'label_es'      => 'Multisite (Red)',
            'label_en'      => 'Multisite (Network)',
            'icon'          => 'dashicons-networking',
            'group_desc_es' => 'Exclusivas de instalaciones WordPress Multisite. Permiten crear y eliminar sitios de la red, gestionar usuarios/temas/plugins a nivel de red, configurar opciones globales y ejecutar actualizaciones de la red completa.',
            'group_desc_en' => 'Exclusive to WordPress Multisite installations. Allow creating and deleting network sites, managing users/themes/plugins at network level, configuring global options, and running full network updates.',
            'caps'          => array(
                'create_sites' => array(
                    'desc_es' => 'Permite crear nuevos sitios dentro de una instalación Multisite.',
                    'desc_en' => 'Allows creating new sites within a Multisite installation.',
                ),
                'delete_sites' => array(
                    'desc_es' => 'Autoriza eliminar sitios completos de la red Multisite.',
                    'desc_en' => 'Allows deleting complete sites from the Multisite network.',
                ),
                'manage_network' => array(
                    'desc_es' => 'Otorga acceso al panel de administración de red, donde se controlan todos los sitios, usuarios, temas, plugins y opciones globales.',
                    'desc_en' => 'Grants access to the network admin panel, where all sites, users, themes, plugins, and global options are managed.',
                ),
                'manage_sites' => array(
                    'desc_es' => 'Permite gestionar la configuración de cada sitio de la red (URL, estado, idioma, opciones básicas).',
                    'desc_en' => 'Allows managing each network site\'s configuration (URL, status, language, basic options).',
                ),
                'manage_network_users' => array(
                    'desc_es' => 'Autoriza añadir, editar y asignar usuarios a nivel de red.',
                    'desc_en' => 'Allows adding, editing, and assigning users at network level.',
                ),
                'manage_network_plugins' => array(
                    'desc_es' => 'Permite activar o desactivar plugins para toda la red.',
                    'desc_en' => 'Allows activating or deactivating plugins for the entire network.',
                ),
                'manage_network_themes' => array(
                    'desc_es' => 'Permite habilitar o deshabilitar temas disponibles para los sitios de la red.',
                    'desc_en' => 'Allows enabling or disabling themes available for network sites.',
                ),
                'manage_network_options' => array(
                    'desc_es' => 'Autoriza modificar las opciones globales de la red (registro, límites de subida, dominios, etc.).',
                    'desc_en' => 'Allows modifying global network options (registration, upload limits, domains, etc.).',
                ),
                'upload_themes' => array(
                    'desc_es' => 'Permite subir archivos ZIP de temas desde la administración de red.',
                    'desc_en' => 'Allows uploading theme ZIP files from network administration.',
                ),
                'upgrade_network' => array(
                    'desc_es' => 'Autoriza la ejecución de procesos de actualización del core y la base de datos a nivel de red.',
                    'desc_en' => 'Allows running core and database update processes at network level.',
                ),
                'setup_network' => array(
                    'desc_es' => 'Capacidad usada para configurar y convertir una instalación simple en Multisite.',
                    'desc_en' => 'Capability used to configure and convert a single installation into Multisite.',
                ),
            ),
        );

        return $groups;
    }

    /**
     * Flat map: cap_name => description (in current locale).
     *
     * @return array<string, string>
     */
    public static function get_flat_capabilities() {

        $locale = determine_locale();
        $is_es  = ( strpos( $locale, 'es' ) === 0 );
        $key    = $is_es ? 'desc_es' : 'desc_en';
        $flat   = array();

        foreach ( self::get_grouped_capabilities() as $group ) {
            foreach ( $group['caps'] as $cap => $meta ) {
                $flat[ $cap ] = $meta[ $key ];
            }
        }

        /* Merge WooCommerce caps if WC is active */
        if ( class_exists( 'Easy_Roles_WooCommerce' ) ) {
            $wc_groups = Easy_Roles_WooCommerce::get_wc_capabilities();
            foreach ( $wc_groups as $group ) {
                foreach ( $group['caps'] as $cap => $meta ) {
                    $flat[ $cap ] = $meta[ $key ];
                }
            }
        }

        return $flat;
    }

    /**
     * Scan $wp_roles for any capabilities not already in our map.
     * Returns them labelled as "Other / Unknown".
     *
     * @return array<string, bool>
     */
    public static function get_extra_registered_caps() {

        global $wp_roles;

        if ( ! isset( $wp_roles ) ) {
            $wp_roles = wp_roles();
        }

        $known = array_keys( self::get_flat_capabilities() );
        $all   = array();

        foreach ( $wp_roles->roles as $role_data ) {
            if ( isset( $role_data['capabilities'] ) && is_array( $role_data['capabilities'] ) ) {
                foreach ( array_keys( $role_data['capabilities'] ) as $cap ) {
                    $all[ $cap ] = true;
                }
            }
        }

        return array_diff_key( $all, array_flip( $known ) );
    }
}
