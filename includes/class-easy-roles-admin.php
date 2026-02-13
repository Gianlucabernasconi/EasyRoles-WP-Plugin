<?php
/**
 * Easy Roles – Admin UI
 *
 * Registers the admin menu page and renders the role management interface.
 * Scripts and styles are loaded ONLY on this plugin's page.
 *
 * @package EasyRoles
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Easy_Roles_Admin {

    /** @var string Admin page hook suffix for conditional loading. */
    private static $hook_suffix = '';

    /**
     * Initialise admin hooks.
     */
    public static function init() {
        add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
    }

    /**
     * Register menu page under manage_options.
     */
    public static function register_menu() {
        self::$hook_suffix = add_menu_page(
            __( 'Easy Roles', 'easy-roles-gb' ),
            __( 'Easy Roles', 'easy-roles-gb' ),
            'manage_options',
            'easy-roles',
            array( __CLASS__, 'render_page' ),
            'dashicons-groups',
            71
        );
    }

    /**
     * Enqueue assets ONLY on the plugin page.
     *
     * @param string $hook Current admin page hook.
     */
    public static function enqueue_assets( $hook ) {

        if ( $hook !== self::$hook_suffix ) {
            return;
        }

        $setup = self::get_language_setup();
        $is_es = $setup['is_es'];
        $t     = $setup['strings'];

        wp_enqueue_style(
            'easy-roles-admin',
            EASY_ROLES_URL . 'assets/css/easy-roles-admin.css',
            array(),
            EASY_ROLES_VERSION
        );

        wp_enqueue_script(
            'easy-roles-admin',
            EASY_ROLES_URL . 'assets/js/easy-roles-admin.js',
            array(),
            EASY_ROLES_VERSION,
            true
        );

        wp_localize_script( 'easy-roles-admin', 'easyRolesData', array(
            'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
            'nonce'         => wp_create_nonce( 'easy_roles_nonce' ),
            'strings'       => $t,
            'currentUserId' => get_current_user_id(),
            'allRoles'      => self::get_roles_for_js(),
        ) );
    }

    /**
     * Get centralized language setup and strings.
     *
     * @return array
     */
    private static function get_language_setup() {
        $user_id = get_current_user_id();
        $saved_lang = get_user_meta( $user_id, 'easy_roles_lang', true );
        
        if ( ! $saved_lang ) {
            $saved_lang = ( strpos( determine_locale(), 'es' ) === 0 ) ? 'es' : 'en';
        }
        
        $is_es = ( $saved_lang === 'es' );

        $strings = array(
            'en' => array(
                'wc_detected' => 'WooCommerce detected',
                'intro_title' => 'What is Easy Roles?',
                'intro_p1'    => 'Easy Roles allows you to manage user roles and permissions on your WordPress site in a simple and visual way. A "role" is like a job title (e.g., Editor, Author) and each role has "capabilities" — permissions that define what a user with that role can and cannot do on the site.',
                'intro_p2'    => 'For example, you can create a role called "Content Manager" that can edit and publish posts but cannot install plugins or change the site theme. This way, each user only has access to what they need.',
                'step_1_title'=> 'View your roles',
                'step_1_desc' => 'In the "Roles" tab you can see all existing roles, how many users each one has, and their permissions.',
                'step_2_title'=> 'Create or clone',
                'step_2_desc' => 'Create a new role from scratch or clone an existing one to use it as a starting point and then adjust permissions.',
                'step_3_title'=> 'Edit permissions',
                'step_3_desc' => 'Click "Edit" on any role to change its name or enable/disable specific permissions by checking or unchecking boxes.',
                'roles_tab'   => 'Roles',
                'create_tab'  => 'Create Role',
                'edit_tab'    => 'Edit Role',
                'about_screen'=> 'About this screen',
                'about_text'  => 'Below you can see all the roles defined on your site. Roles with a lock icon are protected (they are part of WordPress or WooCommerce and cannot be deleted). Custom roles can be edited, cloned, or deleted. When you delete a custom role, its users will be reassigned to the "Subscriber" role.',
                'custom'      => 'Custom',
                'capabilities'=> 'Capabilities',
                'users'       => 'Users',
                'edit_btn'    => 'Edit',
                'clone_btn'   => 'Clone',
                'delete_btn'  => 'Delete',
                'role_name'   => 'Role Name',
                'role_slug'   => 'Role Slug (ID)',
                'search_perm' => 'Search permissions...',
                'select_all'  => 'Select All',
                'deselect_all'=> 'Deselect All',
                'create_role' => 'Create Role',
                'save_changes'=> 'Save Changes',
                'back_roles'  => 'Back to Roles',
                'select_group'=> 'Select group',
                'deselect_group'=> 'Deselect group',
                'permissions' => 'Permissions',
                'perm_desc'   => 'Select the permissions you want to assign to this role. You can use the search bar to find specific capabilities.',
                'name_desc'   => 'The name that will be displayed in the WordPress interface (e.g., "Store Manager").',
                'slug_desc'   => 'A unique internal identifier. Use only lowercase letters, numbers, and underscores (e.g., "store_manager").',
                'help_create' => 'Creating a new role',
                'help_create_text' => 'Enter a name and a unique ID (slug) for the new role. Then, select the permissions below. If you want to start with a predefined set of permissions, it is easier to clone an existing role from the main screen.',
                'help_edit'   => 'Editing a role',
                'help_edit_text' => 'Modify the role name or permissions. Remember that changing permissions will affect all users assigned to this role immediately. Be careful when removing "read" or "edit" capabilities.',
                'edit_protected_warn' => 'This is a protected role. You can only view its capabilities.',
                'default_wp'  => 'WordPress default',
                'custom_wc'   => 'Custom WC',
                'confirm_btn' => 'Confirm',
                'cancel_btn'  => 'Cancel',
                // JS specific
                'confirmDelete' => 'Are you sure you want to delete this role? Users with this role will be reassigned to Subscriber.',
                'confirmUpdate' => 'Save changes to this role?',
                'roleCreated'   => 'Role created successfully.',
                'roleUpdated'   => 'Role updated successfully.',
                'roleDeleted'   => 'Role deleted successfully.',
                'roleCloned'    => 'Role cloned successfully.',
                'errorOccurred' => 'An error occurred. Please try again.',
                'nameRequired'  => 'Please enter a role name.',
                'slugRequired'  => 'Please enter a role slug.',
                'protected'     => 'Protected',
                'guide_tab'     => 'Role Guide',
                'learn_more'    => 'Learn detailed guide &rarr;',

                'legend_title'  => 'Permission Types Legend',
                'legend_key'    => 'KEY',
                'legend_key_desc' => 'Essential access. "read" and "edit_posts" are VITAL to see the admin panel structure.',
                'legend_anchor' => 'ANCHOR',
                'legend_anchor_desc' => 'System anchors. They open main menus or sections (e.g., "upload_files" enables Media).',
                'visibility_warn_title' => '🚨 Visibility Warning',
                'visibility_warn_read'  => 'You are saving a role <strong>without "read"</strong>.',
                'visibility_warn_edit'  => 'You are saving a role <strong>without "edit_posts"</strong>.',
                'visibility_warn_both'  => 'You are saving a role <strong>without "read" and "edit_posts"</strong>.',
                'visibility_warn_desc'  => 'This may cause users to see a <span style="color:#d63638; font-weight:bold;">COMPLETELY EMPTY DASHBOARD</span> or be redirected. Are you sure?',
                'legend_action' => 'ACTION',
                'legend_action_desc' => 'Specific tasks. They usually need an Anchor to be visible (e.g., "install_plugins" needs to see the Plugins menu).',

                /* User Management */
                'users_tab'          => 'Users',
                'users_panel_title'  => 'Users with role',
                'users_panel_desc'   => 'Here you can see all users assigned to this role and change their role if needed.',
                'search_users'       => 'Search users...',
                'change_role'        => 'Change role',
                'no_users'           => 'No users with this role.',
                'role_changed'       => 'User role updated successfully.',
                'self_demote_warn'   => 'You cannot change your own administrator role.',
                'confirm_change_role'=> 'Change the role of this user?',
                'user_email'         => 'Email',
                'current_role'       => 'Current role',
                'page_of'            => 'Page %1 of %2',
                'view_users'         => 'View users',
                'all_roles_label'    => 'All Roles',
                'select_role_prompt' => 'Select a role to see its users',
                'developed_by'       => 'Developed with 💚 by <a href="https://www.gianlucabernasconi.cl" target="_blank" rel="noopener noreferrer" title="Visit Gianluca Bernasconi\'s website – Full Stack Developer">Gianluca Bernasconi</a>',

            ),
            'es' => array(
                'wc_detected' => 'WooCommerce detectado',
                'intro_title' => '¿Qué es Easy Roles?',
                'intro_p1'    => 'Easy Roles te permite gestionar los roles de usuario y permisos en tu sitio WordPress de forma sencilla y visual. Un "rol" es como un cargo (ej. Editor, Autor) y cada rol tiene "capacidades" — permisos que definen qué puede y qué no puede hacer un usuario con ese rol en el sitio.',
                'intro_p2'    => 'Por ejemplo, puedes crear un rol llamado "Gestor de Contenidos" que pueda editar y publicar entradas pero no instalar plugins ni cambiar el tema del sitio. Así, cada usuario tiene acceso solo a lo que necesita.',
                'step_1_title'=> 'Ver tus roles',
                'step_1_desc' => 'En la pestaña "Roles" puedes ver todos los roles existentes, cuántos usuarios tiene cada uno y sus permisos.',
                'step_2_title'=> 'Crear o clonar',
                'step_2_desc' => 'Crea un nuevo rol desde cero o clona uno existente para usarlo como punto de partida y luego ajustar permisos.',
                'step_3_title'=> 'Editar permisos',
                'step_3_desc' => 'Haz clic en "Editar" en cualquier rol para cambiar su nombre o activar/desactivar permisos específicos marcando o desmarcando casillas.',
                'roles_tab'   => 'Roles',
                'create_tab'  => 'Crear Rol',
                'edit_tab'    => 'Editar Rol',
                'about_screen'=> 'Sobre esta pantalla',
                'about_text'  => 'Abajo puedes ver todos los roles definidos en tu sitio. Los roles con un candado están protegidos (son parte de WordPress o WooCommerce y no se pueden borrar). Los roles personalizados pueden editarse, clonarse o borrarse. Al borrar un rol personalizado, sus usuarios serán reasignados al rol "Suscriptor".',
                'custom'      => 'Personalizado',
                'capabilities'=> 'Permisos',
                'users'       => 'Usuarios',
                'edit_btn'    => 'Editar',
                'clone_btn'   => 'Clonar',
                'delete_btn'  => 'Borrar',
                'role_name'   => 'Nombre del Rol',
                'role_slug'   => 'Slug del Rol (ID)',
                'search_perm' => 'Buscar permisos...',
                'select_all'  => 'Seleccionar todo',
                'deselect_all'=> 'Deseleccionar todo',
                'create_role' => 'Crear Rol',
                'save_changes'=> 'Guardar Cambios',
                'back_roles'  => 'Volver a Roles',
                'select_group'=> 'Seleccionar grupo',
                'deselect_group'=> 'Deseleccionar grupo',
                'permissions' => 'Permisos',
                'perm_desc'   => 'Selecciona los permisos que deseas asignar a este rol. Puedes usar el buscador para encontrar capacidades específicas.',
                'name_desc'   => 'El nombre que se mostrará en la interfaz de WordPress (ej. "Gestor de la Tienda").',
                'slug_desc'   => 'Un identificador interno único. Usa solo letras minúsculas, números y guiones bajos (ej. "tienda_gestor").',
                'help_create' => 'Creando un nuevo rol',
                'help_create_text' => 'Introduce un nombre y un ID único (slug) para el nuevo rol. Luego, selecciona los permisos abajo. Si quieres empezar con un conjunto de permisos predefinidos, es más fácil clonar un rol existente desde la pantalla principal.',
                'help_edit'   => 'Editando un rol',
                'help_edit_text' => 'Modifica el nombre del rol o sus permisos. Recuerda que cambiar permisos afectará a todos los usuarios asignados a este rol inmediatamente. Ten cuidado al quitar permisos básicos como "read" o "edit".',
                'edit_protected_warn' => 'Este es un rol protegido. Solo puedes ver sus capacidades.',
                'default_wp'  => 'Por defecto de WordPress',
                'custom_wc'   => 'Personalizado WC',
                'confirm_btn' => 'Confirmar',
                'cancel_btn'  => 'Cancelar',
                // JS specific
                'confirmDelete' => '¿Estás seguro de que quieres borrar este rol? Los usuarios con este rol serán reasignados a Suscriptor.',
                'confirmUpdate' => '¿Guardar los cambios en este rol?',
                'roleCreated'   => 'Rol creado correctamente.',
                'roleUpdated'   => 'Rol actualizado correctamente.',
                'roleDeleted'   => 'Rol borrado correctamente.',
                'roleCloned'    => 'Rol clonado correctamente.',
                'errorOccurred' => 'Ocurrió un error. Por favor intenta de nuevo.',
                'nameRequired'  => 'Por favor introduce un nombre para el rol.',
                'slugRequired'  => 'Por favor introduce un slug (ID) para el rol.',
                'protected'     => 'Protegido',
                'guide_tab'     => 'Guía de Roles',
                'learn_more'    => 'Ver guía detallada &rarr;',

                'legend_title'  => 'Guía de tipos de permisos',
                'legend_key'    => 'LLAVE',
                'legend_key_desc' => 'Acceso esencial. "read" y "edit_posts" son VITALES para ver la estructura del menú.',
                'legend_anchor' => 'ANCLA',
                'legend_anchor_desc' => 'Pilares del sistema. Abren menús o secciones principales (ej. "upload_files" activa Medios).',
                'visibility_warn_title' => '🚨 Aviso de Visibilidad',
                'visibility_warn_read'  => 'Estás guardando un rol <strong>sin el permiso "read"</strong>.',
                'visibility_warn_edit'  => 'Estás guardando un rol <strong>sin el permiso "edit_posts"</strong>.',
                'visibility_warn_both'  => 'Estás guardando un rol <strong>sin los permisos "read" ni "edit_posts"</strong>.',
                'visibility_warn_desc'  => 'Esto causará que el usuario vea un <span style="color:#d63638; font-weight:bold;">ESCRITORIO TOTALMENTE VACÍO</span> o sea redirigido. ¿Estás seguro?',
                'legend_action' => 'ACCIÓN',
                'legend_action_desc' => 'Tareas específicas. Suelen necesitar un Ancla para ser visibles (ej. "borrar plugins" requiere ver el menú de Plugins).',

                /* Gestión de Usuarios */
                'users_tab'          => 'Usuarios',
                'users_panel_title'  => 'Usuarios con el rol',
                'users_panel_desc'   => 'Aquí puedes ver todos los usuarios asignados a este rol y cambiar su rol si lo necesitas.',
                'search_users'       => 'Buscar usuarios...',
                'change_role'        => 'Cambiar rol',
                'no_users'           => 'No hay usuarios con este rol.',
                'role_changed'       => 'Rol del usuario actualizado correctamente.',
                'self_demote_warn'   => 'No puedes cambiar tu propio rol de administrador.',
                'confirm_change_role'=> '¿Cambiar el rol de este usuario?',
                'user_email'         => 'Email',
                'current_role'       => 'Rol actual',
                'page_of'            => 'Página %1 de %2',
                'view_users'         => 'Ver usuarios',
                'all_roles_label'    => 'Todos los Roles',
                'select_role_prompt' => 'Selecciona un rol para ver sus usuarios',
                'developed_by'       => 'Desarrollado con 💚 por <a href="https://www.gianlucabernasconi.cl" target="_blank" rel="noopener noreferrer" title="Visitar el sitio web de Gianluca Bernasconi – Desarrollador Full Stack">Gianluca Bernasconi</a>',

            ),
        );

        return array(
            'is_es'   => $is_es,
            'strings' => $strings[ $is_es ? 'es' : 'en' ]
        );
    }

    /**
     * Get details for specific capabilities to explain their relationships.
     *
     * @return array
     */
    private static function get_capability_relationships() {
        return array(
            'read' => array(
                'type'  => 'LLAVE',
                'class' => 'key',
                'desc'  => 'Puerta mínima de acceso a /wp-admin/. Sin esto, el usuario no entra al panel.',
            ),
            'edit_posts' => array(
                'type'  => 'LLAVE',
                'class' => 'key',
                'desc'  => 'Pilar de visibilidad. Sin esto, WordPress oculta casi todo el menú lateral.',
            ),
            'manage_options' => array(
                'type'  => 'ANCLA',
                'class' => 'anchor',
                'desc'  => 'Llave maestra de Ajustes y configuración de plugins.',
            ),
            'activate_plugins' => array(
                'type'  => 'ANCLA',
                'class' => 'anchor',
                'desc'  => 'Activa el menú Plugins. Necesario para instalar o borrar.',
            ),
            'install_plugins' => array(
                'type'  => 'ACCIÓN',
                'class' => 'action',
                'desc'  => 'Requiere activate_plugins para ver la pantalla.',
            ),
            'delete_plugins' => array(
                'type'  => 'ACCIÓN',
                'class' => 'action',
                'desc'  => 'Requiere activate_plugins para ver la pantalla.',
            ),
            'edit_theme_options' => array(
                'type'  => 'ANCLA',
                'class' => 'anchor',
                'desc'  => 'Controla menús, widgets y personalizador en Apariencia.',
            ),
            'switch_themes' => array(
                'type'  => 'ANCLA',
                'class' => 'anchor',
                'desc'  => 'Permite cambiar el tema. Suele ir junto a edit_theme_options.',
            ),
            'list_users' => array(
                'type'  => 'ANCLA',
                'class' => 'anchor',
                'desc'  => 'Activa el menú Usuarios. Necesario para editar o borrar usuarios.',
            ),
            'upload_files' => array(
                'type'  => 'ANCLA',
                'class' => 'anchor',
                'desc'  => 'Habilita la Biblioteca de Medios y subida de archivos.',
            ),
            'moderate_comments' => array(
                'type'  => 'ANCLA',
                'class' => 'anchor',
                'desc'  => 'Habilita el menú y moderación de Comentarios.',
            ),
            'manage_woocommerce' => array(
                'type'  => 'LLAVE',
                'class' => 'key',
                'desc'  => 'Acceso maestro a WooCommerce. Sin esto no ves el menú principal de la tienda.',
            ),
            'edit_products' => array(
                'type'  => 'ANCLA',
                'class' => 'anchor',
                'desc'  => 'Habilita el menú Productos. Necesario para ver y editar productos.',
            ),
            'view_woocommerce_reports' => array(
                'type'  => 'ACCIÓN',
                'class' => 'action',
                'desc'  => 'Ver informes y analíticas. Requiere acceso a la tienda (manage_woocommerce).',
            ),
            'manage_woocommerce_orders' => array(
                'type'  => 'ACCIÓN',
                'class' => 'action',
                'desc'  => 'Gestión completa de pedidos. Suele requerir manage_woocommerce.',
            ),
            'manage_categories' => array(
                'type'  => 'ACCIÓN',
                'class' => 'action',
                'desc'  => 'Controla categorías de entradas. Requiere edit_posts.',
            ),
        );
    }

    /**
     * Render the main plugin page.
     */
    public static function render_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have permission to access this page.', 'easy-roles-gb' ) );
        }

        // --- Language Logic ---
        $user_id = get_current_user_id();

        // Check for language switch request
        if ( isset( $_GET['er_lang'] ) && in_array( $_GET['er_lang'], array( 'en', 'es' ) ) ) {
            update_user_meta( $user_id, 'easy_roles_lang', sanitize_text_field( $_GET['er_lang'] ) );
            ?>
            <script>window.location.href = "<?php echo esc_url( remove_query_arg( 'er_lang' ) ); ?>";</script>
            <?php
            exit;
        }

        $setup = self::get_language_setup();
        $is_es = $setup['is_es'];
        $t     = $setup['strings'];

        // Try to switch locale for translations if available
        if ( $is_es && determine_locale() !== 'es_ES' ) {
            switch_to_locale( 'es_ES' );
        } elseif ( ! $is_es && determine_locale() !== 'en_US' ) {
            switch_to_locale( 'en_US' );
        }

        $label_key     = $is_es ? 'label_es' : 'label_en';
        $desc_key      = $is_es ? 'desc_es' : 'desc_en';

        // Fetch data after locale switch
        $roles         = Easy_Roles_Manager::get_all_roles();
        $protected     = Easy_Roles_Manager::get_protected_roles();
        $cap_groups    = Easy_Roles_Capabilities::get_grouped_capabilities();
        $wc_active     = class_exists( 'Easy_Roles_WooCommerce' ) && Easy_Roles_WooCommerce::is_active();
        $wc_groups     = $wc_active ? Easy_Roles_WooCommerce::get_wc_capabilities() : array();
        
        // Flatten WC caps for easy check
        $wc_caps_flat = array();
        if ( ! empty( $wc_groups ) ) {
            foreach ( $wc_groups as $g ) {
                if ( ! empty( $g['caps'] ) ) {
                    $wc_caps_flat = array_merge( $wc_caps_flat, $g['caps'] );
                }
            }
        }

        $extra_caps    = Easy_Roles_Capabilities::get_extra_registered_caps();
        $user_counts   = count_users();
        
        // URLs for switcher
        $url_es = add_query_arg( 'er_lang', 'es' );
        $url_en = add_query_arg( 'er_lang', 'en' );

        ?>
        <div class="wrap easy-roles-wrap">
            <header class="easy-roles-header-modern">
                <div class="easy-roles-brand">
                    <div class="easy-roles-brand__icon">
                        <span class="dashicons dashicons-groups"></span>
                    </div>
                    <div class="easy-roles-brand__info">
                        <h1>Easy Roles</h1>
                        <span class="easy-roles-version">v<?php echo esc_html( EASY_ROLES_VERSION ); ?></span>
                    </div>
                </div>
                
                <div class="easy-roles-actions">
                    <button type="button" class="easy-roles-btn easy-roles-btn--primary" id="er-header-create">
                        <span class="dashicons dashicons-plus-alt2"></span>
                        <?php echo esc_html( $t['create_role'] ); ?>
                    </button>

                    <?php if ( $wc_active ) : ?>
                        <span class="easy-roles-pill easy-roles-pill--wc" title="WooCommerce Detected">
                            <span class="dashicons dashicons-store"></span>
                            <?php echo esc_html( $t['wc_detected'] ); ?>
                        </span>
                    <?php endif; ?>

                    <div class="easy-roles-lang-switch">
                        <a href="<?php echo esc_url( $url_en ); ?>" class="easy-roles-lang-btn <?php echo ! $is_es ? 'active' : ''; ?>">EN</a>
                        <span class="easy-roles-lang-sep">/</span>
                        <a href="<?php echo esc_url( $url_es ); ?>" class="easy-roles-lang-btn <?php echo $is_es ? 'active' : ''; ?>">ES</a>
                    </div>
                </div>
            </header>

            <!-- =========================================================
                 WELCOME / INTRO SECTION
            ========================================================== -->
            <!-- Dismissible Onboarding -->
            <div id="er-onboarding" class="easy-roles-onboarding" style="display:none;">
                <div class="easy-roles-onboarding__content">
                    <h2><?php echo esc_html( $t['intro_title'] ); ?></h2>
                    <p><?php echo esc_html( $t['intro_p1'] ); ?></p>
                    <p><?php echo esc_html( $t['intro_p2'] ); ?></p>
                    
                    <div class="easy-roles-intro__steps">
                        <div class="easy-roles-step">
                            <div class="easy-roles-step__icon"><span class="dashicons dashicons-visibility"></span></div>
                            <h3><?php echo esc_html( $t['step_1_title'] ); ?></h3>
                            <p><?php echo esc_html( $t['step_1_desc'] ); ?></p>
                        </div>
                        <div class="easy-roles-step">
                            <div class="easy-roles-step__icon"><span class="dashicons dashicons-admin-page"></span></div>
                            <h3><?php echo esc_html( $t['step_2_title'] ); ?></h3>
                            <p><?php echo esc_html( $t['step_2_desc'] ); ?></p>
                        </div>
                        <div class="easy-roles-step">
                            <div class="easy-roles-step__icon"><span class="dashicons dashicons-edit"></span></div>
                            <h3><?php echo esc_html( $t['step_3_title'] ); ?></h3>
                            <p><?php echo esc_html( $t['step_3_desc'] ); ?></p>
                        </div>
                    </div>

                    <button type="button" class="easy-roles-btn easy-roles-btn--primary" id="er-dismiss-intro">
                        <?php esc_html_e( 'Entendido', 'easy-roles-gb' ); ?>
                    </button>
                </div>
            </div>

            <!-- TABS NAVIGATION -->
            <nav class="easy-roles-tabs" role="tablist">
                <button type="button"
                        class="easy-roles-tab easy-roles-tab--active"
                        role="tab"
                        aria-selected="true"
                        aria-controls="er-panel-roles"
                        id="er-tab-roles">
                    <span class="dashicons dashicons-groups"></span>
                    <?php echo esc_html( $t['roles_tab'] ); ?>
                </button>
                <button type="button"
                        class="easy-roles-tab"
                        role="tab"
                        aria-selected="false"
                        aria-controls="er-panel-create"
                        id="er-tab-create">
                    <span class="dashicons dashicons-plus-alt2"></span>
                    <?php echo esc_html( $t['create_tab'] ); ?>
                </button>
                <!-- Edit tab is hidden initially, shown when editing -->
                <button type="button"
                        class="easy-roles-tab"
                        role="tab"
                        aria-selected="false"
                        aria-controls="er-panel-edit"
                        id="er-tab-edit"
                        style="display:none;">
                    <span class="dashicons dashicons-edit"></span>
                    <?php echo esc_html( $t['edit_tab'] ); ?>
                </button>
                <button type="button"
                        class="easy-roles-tab"
                        role="tab"
                        aria-selected="false"
                        aria-controls="er-panel-guide"
                        id="er-tab-guide">
                    <span class="dashicons dashicons-book"></span>
                    <?php echo esc_html( $t['guide_tab'] ); ?>
                </button>
                <button type="button"
                        class="easy-roles-tab"
                        role="tab"
                        aria-selected="false"
                        aria-controls="er-panel-users"
                        id="er-tab-users">
                    <span class="dashicons dashicons-admin-users"></span>
                    <?php echo esc_html( $t['users_tab'] ); ?>
                </button>
            </nav>

            <!-- =========================================================
                 PANEL: ROLES LIST
            ========================================================== -->
            <section class="easy-roles-panel easy-roles-panel--active"
                     role="tabpanel"
                     id="er-panel-roles"
                     aria-labelledby="er-tab-roles">
                
                <div class="easy-roles-panel-header">
                    <div class="easy-roles-help-tip">
                        <span class="dashicons dashicons-editor-help"></span>
                        <div class="easy-roles-help-tip__content">
                            <strong><?php echo esc_html( $t['about_screen'] ); ?></strong>
                            <p><?php echo esc_html( $t['about_text'] ); ?></p>
                        </div>
                    </div>
                </div>

                <div class="easy-roles-grid">
                    <?php
                    // Check if we have protected role overriding
                    foreach ( $roles as $slug => $role_data ) :
                        $is_prot    = in_array( $slug, $protected, true );
                        $is_wc      = $wc_active && class_exists('Easy_Roles_WooCommerce') && in_array( $slug, Easy_Roles_WooCommerce::get_protected_roles(), true );
                        $r_caps     = isset( $role_data['capabilities'] ) ? $role_data['capabilities'] : array();

                        // Check if custom role has WC caps
                        $is_custom_wc = false;
                        if ( ! $is_prot && ! $is_wc && ! empty( $wc_caps_flat ) ) {
                            foreach ( $r_caps as $c => $val ) {
                                if ( $val && isset( $wc_caps_flat[ $c ] ) ) {
                                    $is_custom_wc = true;
                                    break;
                                }
                            }
                        }
                        $cap_count  = isset( $role_data['capabilities'] ) ? count( $role_data['capabilities'] ) : 0;
                        $usr_count  = isset( $user_counts['avail_roles'][ $slug ] ) ? (int) $user_counts['avail_roles'][ $slug ] : 0;

                        /* Determine card type class */
                        /* Determine card type for filtering */
                        if ( $is_wc ) {
                            $card_type = 'wc';
                            $card_class = 'easy-roles-card--wc';
                        } elseif ( $is_prot ) {
                            $card_type = 'native';
                            $card_class = 'easy-roles-card--protected';
                        } elseif ( $is_custom_wc ) {
                             $card_type = 'wc'; // Custom but WC related
                             $card_class = 'easy-roles-card--wc';
                        } else {
                            $card_type = 'custom';
                            $card_class = 'easy-roles-card--custom';
                        }
                    ?>
                    <div class="easy-roles-card <?php echo esc_attr( $card_class ); ?>"
                         data-role="<?php echo esc_attr( $slug ); ?>"
                         data-type="<?php echo esc_attr( $card_type ); ?>">
                        <div class="easy-roles-card__body">
                            <div class="easy-roles-card__header">
                                <h3 class="easy-roles-card__name">
                                    <?php echo esc_html( translate_user_role( $role_data['name'] ) ); ?>
                                </h3>
                                <?php if ( $is_wc ) : ?>
                                    <span class="easy-roles-pill easy-roles-pill--wc">
                                        <span class="dashicons dashicons-store"></span> WooCommerce
                                    </span>
                                <?php elseif ( $is_prot ) : ?>
                                    <span class="easy-roles-pill easy-roles-pill--prot">
                                        <span class="dashicons dashicons-lock"></span>
                                        <?php echo esc_html( $t['default_wp'] ); ?>
                                    </span>
                                <?php elseif ( $is_custom_wc ) : ?>
                                    <span class="easy-roles-pill easy-roles-pill--custom-wc">
                                        <span class="dashicons dashicons-store"></span>
                                        <?php echo esc_html( $t['custom_wc'] ); ?>
                                    </span>
                                <?php else : ?>
                                    <span class="easy-roles-pill easy-roles-pill--custom"><?php echo esc_html( $t['custom'] ); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="easy-roles-card__meta">
                                <span class="easy-roles-card__slug"><?php echo esc_html( $slug ); ?></span>
                                <span class="easy-roles-card__stats">
                                    <span title="<?php echo esc_attr( $t['capabilities'] ); ?>">
                                        <span class="dashicons dashicons-shield"></span> <?php echo (int) $cap_count; ?>
                                    </span>
                                    <span title="<?php echo esc_attr( $t['users'] ); ?>"
                                          class="easy-roles-card__user-link"
                                          data-role="<?php echo esc_attr( $slug ); ?>"
                                          role="button"
                                          tabindex="0"
                                          aria-label="<?php echo esc_attr( sprintf( '%s: %d %s', $role_data['name'], $usr_count, $t['users'] ) ); ?>">
                                        <span class="dashicons dashicons-admin-users"></span> <?php echo (int) $usr_count; ?>
                                    </span>
                                </span>
                            </div>
                        </div>
                        <div class="easy-roles-card__actions">
                            <button type="button"
                                    class="easy-roles-btn easy-roles-btn--edit"
                                    data-action="edit"
                                    data-role="<?php echo esc_attr( $slug ); ?>"
                                    aria-label="<?php echo esc_attr( sprintf( __( 'Edit %s', 'easy-roles-gb' ), $role_data['name'] ) ); ?>">
                                <span class="dashicons dashicons-edit"></span>
                                <?php echo esc_html( $t['edit_btn'] ); ?>
                            </button>
                            <button type="button"
                                    class="easy-roles-btn easy-roles-btn--clone"
                                    data-action="clone"
                                    data-role="<?php echo esc_attr( $slug ); ?>"
                                    aria-label="<?php echo esc_attr( sprintf( __( 'Clone %s', 'easy-roles-gb' ), $role_data['name'] ) ); ?>">
                                <span class="dashicons dashicons-admin-page"></span>
                                <?php echo esc_html( $t['clone_btn'] ); ?>
                            </button>
                            <?php if ( ! $is_prot ) : ?>
                            <button type="button"
                                    class="easy-roles-btn easy-roles-btn--delete"
                                    data-action="delete"
                                    data-role="<?php echo esc_attr( $slug ); ?>"
                                    aria-label="<?php echo esc_attr( sprintf( __( 'Delete %s', 'easy-roles-gb' ), $role_data['name'] ) ); ?>">
                                <span class="dashicons dashicons-trash"></span>
                                <?php echo esc_html( $t['delete_btn'] ); ?>
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <!-- New Role "Ghost" Card -->
                    <div class="easy-roles-card easy-roles-card--add-new" id="er-card-create" role="button" tabindex="0" title="<?php echo esc_attr( $t['create_role'] ); ?>">
                        <span class="dashicons dashicons-plus"></span>
                        <h3><?php echo esc_html( $t['create_role'] ); ?></h3>
                    </div>
                </div>
            </section>

            <!-- =========================================================
                 PANEL: CREATE ROLE
            ========================================================== -->
            <section class="easy-roles-panel"
                     role="tabpanel"
                     id="er-panel-create"
                     aria-labelledby="er-tab-create">

                <form id="er-form-create" class="easy-roles-form" novalidate>
                    <div class="easy-roles-form__header">
                        <div class="easy-roles-form__title-group">
                            <button type="button" class="easy-roles-btn easy-roles-btn--back" title="<?php echo esc_attr( $t['back_roles'] ); ?>">
                                <span class="dashicons dashicons-arrow-left-alt2"></span>
                            </button>
                            <h2><?php echo esc_html( $t['create_tab'] ); ?></h2>
                        </div>
                    </div>

                    <div class="easy-roles-help">
                        <span class="dashicons dashicons-info-outline"></span>
                        <div class="easy-roles-help__content">
                            <span class="easy-roles-help__title"><?php echo esc_html( $t['help_create'] ); ?></span>
                            <span class="easy-roles-help__text">
                                <?php echo esc_html( $t['help_create_text'] ); ?>
                            </span>
                        </div>
                    </div>

                    <?php self::render_legend_box( $t ); ?>

                    <div class="easy-roles-form__fields">
                        <div class="easy-roles-field">
                            <label for="er-create-name"><?php echo esc_html( $t['role_name'] ); ?></label>
                            <input type="text"
                                   id="er-create-name"
                                   name="role_name"
                                   class="regular-text"
                                   placeholder="<?php esc_attr_e( 'e.g. Content Manager', 'easy-roles-gb' ); ?>"
                                   required
                                   aria-required="true"
                                   maxlength="100">
                            <p class="description"><?php echo esc_html( $t['name_desc'] ); ?></p>
                        </div>
                        <div class="easy-roles-field">
                            <label for="er-create-slug"><?php echo esc_html( $t['role_slug'] ); ?></label>
                            <input type="text"
                                   id="er-create-slug"
                                   name="role_slug"
                                   class="regular-text"
                                   placeholder="<?php esc_attr_e( 'e.g. content_manager', 'easy-roles-gb' ); ?>"
                                   required
                                   aria-required="true"
                                   pattern="[a-z0-9_]+"
                                   maxlength="60">
                            <p class="description"><?php echo esc_html( $t['slug_desc'] ); ?></p>
                        </div>
                    </div>

                    <!-- Capabilities selector -->
                    <h3 class="easy-roles-section-title"><?php echo esc_html( $t['permissions'] ); ?></h3>
                    <p class="easy-roles-section-subtitle">
                        <?php echo esc_html( $t['perm_desc'] ); ?>
                    </p>

                    <div class="easy-roles-caps-toolbar">
                        <div class="easy-roles-search">
                            <span class="dashicons dashicons-search"></span>
                            <input type="search"
                                   id="er-create-search"
                                   class="easy-roles-search__input"
                                   placeholder="<?php echo esc_attr( $t['search_perm'] ); ?>"
                                   aria-label="<?php echo esc_attr( $t['search_perm'] ); ?>">
                        </div>
                        <div class="easy-roles-bulk-actions">
                            <button type="button" class="easy-roles-btn easy-roles-btn--select-all" data-form="er-form-create">
                                <?php echo esc_html( $t['select_all'] ); ?>
                            </button>
                            <button type="button" class="easy-roles-btn easy-roles-btn--deselect-all" data-form="er-form-create">
                                <?php echo esc_html( $t['deselect_all'] ); ?>
                            </button>
                        </div>
                    </div>

                    <?php self::render_capability_groups( $cap_groups, $wc_groups, $extra_caps, $label_key, $desc_key, 'create', $t ); ?>

                    <div class="easy-roles-form__footer">
                        <button type="submit" class="easy-roles-btn easy-roles-btn--submit">
                            <span class="dashicons dashicons-plus-alt2"></span>
                            <?php echo esc_html( $t['create_role'] ); ?>
                        </button>
                    </div>
                </form>
            </section>

            <!-- =========================================================
                 PANEL: EDIT ROLE
            ========================================================== -->
            <section class="easy-roles-panel"
                     role="tabpanel"
                     id="er-panel-edit"
                     aria-labelledby="er-tab-edit">
                
                <?php self::render_edit_role_panel( $cap_groups, $wc_groups, $extra_caps, $t, $label_key, $desc_key ); ?>
            </section>

            <?php self::render_users_panel( $t, $roles ); ?>

            <?php self::render_guide_panel( $t ); ?>

            <footer class="easy-roles-admin-footer">
                <p><?php echo $t['developed_by']; ?></p>
            </footer>

            <?php self::render_modal_template( $t ); ?>
        </div>
        <?php
    }

    /**
     * Render capability groups (shared between Create and Edit panels).
     *
     * @param array  $cap_groups  Core WP capability groups.
     * @param array  $wc_groups   WooCommerce capability groups.
     * @param array  $extra_caps  Extra/unknown capabilities.
     * @param string $label_key   Label locale key.
     * @param string $desc_key    Description locale key.
     * @param string $context     'create' or 'edit'.
     */
    private static function render_capability_groups( $cap_groups, $wc_groups, $extra_caps, $label_key, $desc_key, $context, $t ) {
        ?>
        <div class="easy-roles-cap-groups" data-context="<?php echo esc_attr( $context ); ?>">
            <?php
            /* Core WP groups */
            foreach ( $cap_groups as $group_id => $group ) {
                self::render_single_group( $group_id, $group, $label_key, $desc_key, $context, $t );
            }

            /* WooCommerce groups */
            if ( ! empty( $wc_groups ) ) {
                foreach ( $wc_groups as $group_id => $group ) {
                    self::render_single_group( $group_id, $group, $label_key, $desc_key, $context, $t );
                }
            }

            /* Extra / unknown capabilities */
            if ( ! empty( $extra_caps ) ) {
                $extra_group = array(
                    'label_es'      => 'Otras Capacidades',
                    'label_en'      => 'Other Capabilities',
                    'icon'          => 'dashicons-admin-generic',
                    'group_desc_es' => 'Capacidades registradas por plugins o temas de terceros que no forman parte del núcleo de WordPress ni de WooCommerce. Revisa con cuidado antes de asignarlas.',
                    'group_desc_en' => 'Capabilities registered by third-party plugins or themes that are not part of WordPress core or WooCommerce. Review carefully before assigning them.',
                    'caps'          => array(),
                );

                foreach ( $extra_caps as $cap_name ) {
                    $extra_group['caps'][ $cap_name ] = array(
                        'label_es' => $cap_name,
                        'label_en' => $cap_name,
                        'desc_es'  => '',
                        'desc_en'  => '',
                    );
                }

                self::render_single_group( 'extra', $extra_group, $label_key, $desc_key, $context, $t );
            }
            ?>
        </div>
        <?php
    }

    /**
     * Render a single capability group with collapsible header.
     *
     * @param string $group_id Group identifier.
     * @param array  $group    Group data.
     * @param string $label_key Locale key for label.
     * @param string $desc_key  Locale key for description.
     * @param string $context   'create' or 'edit'.
     */
    private static function render_single_group( $group_id, $group, $label_key, $desc_key, $context, $t ) {
        $caps          = isset( $group['caps'] ) ? $group['caps'] : array();
        $cap_count     = count( $caps );
        if ( $cap_count === 0 ) {
            return;
        }

        $desc_group_key = str_replace( 'label_', 'group_desc_', $label_key );
        $group_desc     = isset( $group[ $desc_group_key ] ) ? $group[ $desc_group_key ] : '';
        $prefix         = 'er_group_' . $context . '_' . $group_id;

        // Quick translation based on key
        $is_es = ( $label_key === 'label_es' );
        $txt_sel_group = $is_es ? 'Seleccionar grupo' : 'Select group';
        $txt_des_group = $is_es ? 'Deseleccionar grupo' : 'Deselect group';
        $txt_selected  = $is_es ? 'seleccionados' : 'selected';
        
        // Fetch relationship data for extra explanations
        $relationships = self::get_capability_relationships();
        ?>
        <div class="easy-roles-group easy-roles-group--collapsed" data-group="<?php echo esc_attr( $group_id ); ?>">
            <button type="button"
                    class="easy-roles-group__header easy-roles-group__toggle"
                    aria-expanded="false"
                    aria-controls="<?php echo esc_attr( $prefix ); ?>_list">
                <span class="easy-roles-group__title">
                    <span class="dashicons dashicons-<?php echo esc_attr( $group['icon'] ); ?>"></span>
                    <?php echo esc_html( $group[ $label_key ] ); ?>
                </span>
                <span class="easy-roles-group__count">(<?php echo (int) $cap_count; ?>)</span>
                <span class="easy-roles-group__checked" data-checked="0">
                    — <span class="easy-roles-group__checked-num">0</span> <?php echo esc_html( $txt_selected ); ?>
                </span>
                <span class="dashicons dashicons-arrow-up-alt2 easy-roles-group__arrow"></span>
            </button>

            <div class="easy-roles-group__caps" id="<?php echo esc_attr( $prefix ); ?>_list">
                <?php if ( ! empty( $group_desc ) ) : ?>
                <p class="easy-roles-group__desc">
                    <span class="dashicons dashicons-info-outline"></span>
                    <?php echo esc_html( $group_desc ); ?>
                </p>
                <?php endif; ?>

                <div class="easy-roles-group__bulk">
                    <button type="button" class="easy-roles-btn easy-roles-btn--group-select" data-group="<?php echo esc_attr( $group_id ); ?>">
                        <?php echo esc_html( $txt_sel_group ); ?>
                    </button>
                    <button type="button" class="easy-roles-btn easy-roles-btn--group-deselect" data-group="<?php echo esc_attr( $group_id ); ?>">
                        <?php echo esc_html( $txt_des_group ); ?>
                    </button>
                </div>

                <?php foreach ( $caps as $cap_name => $cap_meta ) :
                $name_key     = str_replace( 'label_', 'name_', $label_key );
                $display_name = isset( $cap_meta[ $name_key ] ) ? $cap_meta[ $name_key ] : $cap_name;
                
                // Check for relationship info
                $rel_info  = isset( $relationships[ $cap_name ] ) ? $relationships[ $cap_name ] : null;
                $cap_class = 'easy-roles-cap';
                
                if ( $rel_info && 'key' === $rel_info['class'] ) {
                    $cap_class .= ' easy-roles-cap--highlight';
                }
                ?>
                <label class="<?php echo esc_attr( $cap_class ); ?>"
                       data-cap="<?php echo esc_attr( $cap_name ); ?>"
                       title="<?php echo esc_attr( $cap_meta[ $desc_key ] ); ?>">
                    <input type="checkbox"
                           name="capabilities[<?php echo esc_attr( $cap_name ); ?>]"
                           value="1"
                           class="easy-roles-cap__check"
                           id="<?php echo esc_attr( $context . '_cap_' . $cap_name ); ?>">
                    <span class="easy-roles-cap__info">
                        <span class="easy-roles-cap__name">
                            <?php echo esc_html( $display_name ); ?>
                            <?php if ( $display_name !== $cap_name ) : ?>
                                <span class="easy-roles-cap__slug-hint"><?php echo esc_html( $cap_name ); ?></span>
                            <?php endif; ?>
                            
                            <?php /* New Visual Badges Logic */ ?>
                            <?php if ( $rel_info ) : 
                                $badge_text = $rel_info['type']; // Fallback
                                if ( 'key' === $rel_info['class'] ) $badge_text = $t['legend_key'];
                                if ( 'anchor' === $rel_info['class'] ) $badge_text = $t['legend_anchor'];
                                if ( 'action' === $rel_info['class'] ) $badge_text = $t['legend_action'];
                            ?>
                                <span class="easy-roles-badge easy-roles-badge--<?php echo esc_attr( $rel_info['class'] ); ?>">
                                    <?php echo esc_html( $badge_text ); ?>
                                </span>
                            <?php endif; ?>
                        </span>
                        
                        <?php /* Description / Explanation */ ?>
                        <span class="easy-roles-cap__desc">
                            <?php echo esc_html( $cap_meta[ $desc_key ] ); ?>
                            <?php if ( $rel_info ) : ?>
                                <span class="easy-roles-cap__extra-desc" <?php echo ( 'key' === $rel_info['class'] ) ? 'style="color: #d63638; font-weight: 600;"' : ''; ?>>
                                    <span class="dashicons dashicons-arrow-right-alt2" style="font-size:12px;width:12px;height:12px;vertical-align:text-top;margin-top:2px;"></span>
                                    <?php echo esc_html( $rel_info['desc'] ); ?>
                                </span>
                            <?php endif; ?>
                        </span>
                    </span>
                </label>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render the "Edit Role" panel.
     */
    public static function render_edit_role_panel( $cap_groups, $wc_groups, $extra_caps, $t, $label_key, $desc_key ) {
        ?>
        <form id="er-form-edit" class="easy-roles-form" novalidate>
            <input type="hidden" id="er-edit-slug" name="role_slug" value="">

            <div class="easy-roles-form__header">
                <div class="easy-roles-form__title-group">
                    <button type="button" class="easy-roles-btn easy-roles-btn--back" id="er-edit-back">
                        <span class="dashicons dashicons-arrow-left-alt"></span>
                        <?php echo esc_html( $t['back_roles'] ); ?>
                    </button>
                    <h2><?php echo esc_html( $t['edit_tab'] ); ?>: <span id="er-edit-title"></span></h2>
                    <span id="er-edit-badge"></span>
                </div>
            </div>

            <div class="easy-roles-help">
                <span class="dashicons dashicons-info-outline"></span>
                <div class="easy-roles-help__content">
                    <span class="easy-roles-help__title"><?php echo esc_html( $t['help_edit'] ); ?></span>
                    <span class="easy-roles-help__text"><?php echo esc_html( $t['help_edit_text'] ); ?></span>
                    <br>
                    <span id="er-edit-user-count" class="easy-roles-user-count-badge"></span>
                </div>
            </div>

            <?php self::render_legend_box( $t ); ?>

            <div id="er-edit-field-name" class="easy-roles-field">
                <label for="er-edit-name"><?php echo esc_html( $t['role_name'] ); ?></label>
                <input type="text"
                       id="er-edit-name"
                       name="role_name"
                       class="regular-text"
                       required
                       maxlength="100">
            </div>
            
            <div id="er-edit-prot-msg" style="display:none;" class="notice notice-warning inline">
                <p><?php echo esc_html( $t['edit_protected_warn'] ); ?></p>
            </div>

            <h3 class="easy-roles-section-title"><?php echo esc_html( $t['permissions'] ); ?></h3>

            <div class="easy-roles-caps-toolbar">
                <div class="easy-roles-search">
                    <span class="dashicons dashicons-search"></span>
                    <input type="search"
                           id="er-edit-search"
                           class="easy-roles-search__input"
                           placeholder="<?php echo esc_attr( $t['search_perm'] ); ?>">
                </div>
                <div class="easy-roles-bulk-actions">
                    <button type="button" class="easy-roles-btn easy-roles-btn--select-all" data-form="er-form-edit">
                        <?php echo esc_html( $t['select_all'] ); ?>
                    </button>
                    <button type="button" class="easy-roles-btn easy-roles-btn--deselect-all" data-form="er-form-edit">
                        <?php echo esc_html( $t['deselect_all'] ); ?>
                    </button>
                </div>
            </div>

            <?php self::render_capability_groups( $cap_groups, $wc_groups, $extra_caps, $label_key, $desc_key, 'edit', $t ); ?>

            <div class="easy-roles-form__footer">
                <button type="button" class="easy-roles-btn easy-roles-btn--back" id="er-edit-back-2">
                    <span class="dashicons dashicons-arrow-left-alt"></span>
                    <?php echo esc_html( $t['back_roles'] ); ?>
                </button>
                <button type="submit" class="easy-roles-btn easy-roles-btn--submit">
                    <span class="dashicons dashicons-yes-alt"></span>
                    <?php echo esc_html( $t['save_changes'] ); ?>
                </button>
            </div>
        </form>
        <?php
    }

    /**
     * Render the "Guide" panel (placeholder, content filled by JS).
     */
    public static function render_guide_panel( $t ) {
        ?>
        <section class="easy-roles-panel"
                 role="tabpanel"
                 id="er-panel-guide"
                 aria-labelledby="er-tab-guide">
            <div class="easy-roles-form__header">
                <div class="easy-roles-form__title-group">
                    <button type="button" class="easy-roles-btn easy-roles-btn--back" onclick="document.getElementById('er-tab-roles').click()">
                        <span class="dashicons dashicons-arrow-left-alt2"></span>
                        <?php echo esc_html( $t['back_roles'] ); ?>
                    </button>
                    <h2><?php echo esc_html( $t['guide_tab'] ); ?></h2>
                </div>
            </div>
            <div id="er-guide-content">
                <!-- Guide content will be injected here -->
            </div>
        </section>
        <?php
    }

    /**
     * Render a didactic legend box explaining the badge types.
     *
     * @param array $t Translations.
     */
    private static function render_legend_box( $t ) {
        ?>
        <div class="easy-roles-legend">
            <div class="easy-roles-legend__header">
                <span class="dashicons dashicons-welcome-learn-more"></span>
                <strong><?php echo esc_html( $t['legend_title'] ); ?></strong>
            </div>
            <div class="easy-roles-legend__grid">
                <div class="easy-roles-legend__item">
                <span class="easy-roles-badge easy-roles-badge--key"><?php echo esc_html( $t['legend_key'] ); ?></span>
                <p style="color: #d63638; font-weight: 500;"><?php echo esc_html( $t['legend_key_desc'] ); ?></p>
            </div>
                <div class="easy-roles-legend__item">
                    <span class="easy-roles-badge easy-roles-badge--anchor"><?php echo esc_html( $t['legend_anchor'] ); ?></span>
                    <p><?php echo esc_html( $t['legend_anchor_desc'] ); ?></p>
                </div>
                <div class="easy-roles-legend__item">
                    <span class="easy-roles-badge easy-roles-badge--action"><?php echo esc_html( $t['legend_action'] ); ?></span>
                    <p><?php echo esc_html( $t['legend_action_desc'] ); ?></p>
                </div>
            </div>
            <div class="easy-roles-legend__footer">
                <button type="button" class="easy-roles-btn-link" id="er-link-guide-<?php echo uniqid(); ?>" onclick="document.getElementById('er-tab-guide').click()">
                    <?php echo $t['learn_more']; ?>
                </button>
            </div>
        </div>
        <?php
    }

    /**
     * Render Generic Modal Template.

     *
     * @param array $t Translations.
     */
    private static function render_modal_template( $t ) {
        ?>
        <div class="easy-roles-modal-overlay" id="er-modal-overlay" style="display:none;" aria-hidden="true">
            <div class="easy-roles-modal" role="dialog" aria-modal="true" aria-labelledby="er-modal-title">
                <div class="easy-roles-modal__header">
                    <span id="er-modal-icon" class="dashicons"></span>
                    <h2 id="er-modal-title"></h2>
                    <button type="button" class="easy-roles-modal__close" aria-label="<?php esc_attr_e( 'Close', 'easy-roles-gb' ); ?>">
                        <span class="dashicons dashicons-no-alt"></span>
                    </button>
                </div>
                <div class="easy-roles-modal__body">
                    <p id="er-modal-message"></p>
                </div>
                <div class="easy-roles-modal__footer">
                    <button type="button" class="easy-roles-btn easy-roles-btn--secondary" id="er-modal-cancel">
                        <?php echo esc_html( $t['cancel_btn'] ); ?>
                    </button>
                    <button type="button" class="easy-roles-btn easy-roles-btn--primary" id="er-modal-confirm">
                        <?php echo esc_html( $t['confirm_btn'] ); ?>
                    </button>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Get roles as simple slug => name array for JS.
     *
     * Esto es para el dropdown de cambio de rol en el panel de usuarios.
     *
     * @return array<string, string>
     */
    private static function get_roles_for_js() {
        $all_roles = Easy_Roles_Manager::get_all_roles();
        $result    = array();

        foreach ( $all_roles as $slug => $role_data ) {
            $result[ $slug ] = translate_user_role( $role_data['name'] );
        }

        return $result;
    }

    /**
     * Render the Users management panel.
     *
     * Este panel tiene dos modos jeje:
     * 1. Sin rol seleccionado: muestra un dropdown para elegir rol.
     * 2. Con rol seleccionado: carga usuarios via AJAX.
     *
     * @param array $t     Translations.
     * @param array $roles All roles data.
     */
    private static function render_users_panel( $t, $roles ) {
        ?>
        <section class="easy-roles-panel"
                 role="tabpanel"
                 id="er-panel-users"
                 aria-labelledby="er-tab-users">

            <div class="easy-roles-form__header">
                <div class="easy-roles-form__title-group">
                    <button type="button" class="easy-roles-btn easy-roles-btn--back" id="er-users-back">
                        <span class="dashicons dashicons-arrow-left-alt2"></span>
                    </button>
                    <h2>
                        <span class="dashicons dashicons-admin-users" style="color: var(--er-primary); margin-right: 0.5rem;"></span>
                        <?php echo esc_html( $t['users_tab'] ); ?>
                        <span id="er-users-role-badge" class="easy-roles-pill easy-roles-pill--custom" style="margin-left: 0.75rem; display: none;"></span>
                    </h2>
                </div>
            </div>

            <!-- Role Picker (siempre visible como selector principal) -->
            <div class="easy-roles-users-toolbar" id="er-users-toolbar">
                <div class="easy-roles-users-role-picker">
                    <label for="er-users-role-select" class="screen-reader-text">
                        <?php echo esc_html( $t['all_roles_label'] ); ?>
                    </label>
                    <select id="er-users-role-select" class="easy-roles-users-select">
                        <option value=""><?php echo esc_html( $t['select_role_prompt'] ); ?></option>
                        <?php foreach ( $roles as $slug => $role_data ) : ?>
                            <option value="<?php echo esc_attr( $slug ); ?>">
                                <?php echo esc_html( translate_user_role( $role_data['name'] ) ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="easy-roles-users-search" id="er-users-search-wrap" style="display: none;">
                    <span class="dashicons dashicons-search"></span>
                    <input type="text"
                           id="er-users-search"
                           class="easy-roles-search__input"
                           placeholder="<?php echo esc_attr( $t['search_users'] ); ?>"
                           aria-label="<?php echo esc_attr( $t['search_users'] ); ?>">
                </div>
            </div>

            <!-- Help text -->
            <div class="easy-roles-help" id="er-users-help">
                <span class="dashicons dashicons-info-outline"></span>
                <div class="easy-roles-help__content">
                    <span class="easy-roles-help__text">
                        <?php echo esc_html( $t['users_panel_desc'] ); ?>
                    </span>
                </div>
            </div>

            <!-- Users list container (populated by JS via AJAX) -->
            <div id="er-users-list" class="easy-roles-users-list">
                <!-- Placeholder: JS will render users here -->
                <div class="easy-roles-users-empty" id="er-users-empty">
                    <span class="dashicons dashicons-groups" style="font-size: 3rem; width: 3rem; height: 3rem; color: var(--er-border); margin-bottom: 1rem;"></span>
                    <p><?php echo esc_html( $t['select_role_prompt'] ); ?></p>
                </div>
            </div>

            <!-- Pagination (hidden initially, shown by JS) -->
            <div class="easy-roles-users-pagination" id="er-users-pagination" style="display: none;">
                <button type="button" class="easy-roles-btn" id="er-users-prev" disabled>
                    <span class="dashicons dashicons-arrow-left-alt2"></span>
                </button>
                <span id="er-users-page-info" class="easy-roles-users-page-info"></span>
                <button type="button" class="easy-roles-btn" id="er-users-next">
                    <span class="dashicons dashicons-arrow-right-alt2"></span>
                </button>
            </div>

        </section>
        <?php
    }
}
