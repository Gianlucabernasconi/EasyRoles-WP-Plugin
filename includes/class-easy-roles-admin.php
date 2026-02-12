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
            'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
            'nonce'     => wp_create_nonce( 'easy_roles_nonce' ),
            'strings'   => array(
                'confirmDelete'  => __( 'Are you sure you want to delete this role? Users with this role will be reassigned to Subscriber.', 'easy-roles-gb' ),
                'confirmUpdate'  => __( 'Save changes to this role?', 'easy-roles-gb' ),
                'roleCreated'    => __( 'Role created successfully.', 'easy-roles-gb' ),
                'roleUpdated'    => __( 'Role updated successfully.', 'easy-roles-gb' ),
                'roleDeleted'    => __( 'Role deleted successfully.', 'easy-roles-gb' ),
                'roleCloned'     => __( 'Role cloned successfully.', 'easy-roles-gb' ),
                'errorOccurred'  => __( 'An error occurred. Please try again.', 'easy-roles-gb' ),
                'slugRequired'   => __( 'Please enter a role slug.', 'easy-roles-gb' ),
                'nameRequired'   => __( 'Please enter a role name.', 'easy-roles-gb' ),
                'cloneSlug'      => __( 'Enter a slug for the cloned role:', 'easy-roles-gb' ),
                'cloneName'      => __( 'Enter a name for the cloned role:', 'easy-roles-gb' ),
                'loading'        => __( 'Loading…', 'easy-roles-gb' ),
                'noResults'      => __( 'No capabilities match your search.', 'easy-roles-gb' ),
                'selectAll'      => __( 'Select all', 'easy-roles-gb' ),
                'deselectAll'    => __( 'Deselect all', 'easy-roles-gb' ),
                'protected'      => __( 'Protected', 'easy-roles-gb' ),
                'custom'         => __( 'Custom', 'easy-roles-gb' ),
                'users'          => __( 'users', 'easy-roles-gb' ),
            ),
        ) );
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
            // Helper param to prevent redirect loop if needed, using js history replace state is cleaner but simple redirect works
             ?>
            <script>window.location.href = "<?php echo esc_url( remove_query_arg( 'er_lang' ) ); ?>";</script>
            <?php
            exit;
        }

        // Determine current language
        $saved_lang = get_user_meta( $user_id, 'easy_roles_lang', true );
        if ( ! $saved_lang ) {
            $saved_lang = ( strpos( determine_locale(), 'es' ) === 0 ) ? 'es' : 'en';
        }
        
        $is_es = ( $saved_lang === 'es' );
        
        // Try to switch locale for translations if available
        if ( $is_es && determine_locale() !== 'es_ES' ) {
            switch_to_locale( 'es_ES' );
        } elseif ( ! $is_es && determine_locale() !== 'en_US' ) {
            switch_to_locale( 'en_US' );
        }

        $label_key     = $is_es ? 'label_es' : 'label_en';
        $desc_key      = $is_es ? 'desc_es' : 'desc_en';

        // Manual Translations Array (to force switch without .mo files)
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
                'custom_wc'   => 'WooCommerce Custom',
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
                'custom_wc'   => 'Personalizado WooCommerce',
            ),
        );
        $t = $strings[ $is_es ? 'es' : 'en' ];


        // Fetch data after locale switch
        $roles         = Easy_Roles_Manager::get_all_roles();
        $protected     = Easy_Roles_Manager::get_protected_roles();
        $cap_groups    = Easy_Roles_Capabilities::get_grouped_capabilities();
        $wc_active     = class_exists( 'Easy_Roles_WooCommerce' ) && Easy_Roles_WooCommerce::is_active();
        $wc_groups     = $wc_active ? Easy_Roles_WooCommerce::get_wc_capabilities() : array();
        $extra_caps    = Easy_Roles_Capabilities::get_extra_registered_caps();
        $user_counts   = count_users();
        
        // URLs for switcher
        $url_es = add_query_arg( 'er_lang', 'es' );
        $url_en = add_query_arg( 'er_lang', 'en' );

        ?>
        <div class="wrap easy-roles-wrap">
            <header class="easy-roles-header">
                <div class="easy-roles-header__title">
                    <span class="dashicons dashicons-groups"></span>
                    <h1><?php esc_html_e( 'Easy Roles', 'easy-roles-gb' ); ?></h1>
                    <span class="easy-roles-version"><?php echo esc_html( 'v' . EASY_ROLES_VERSION ); ?></span>
                </div>
                
                <div class="easy-roles-header__actions" style="display:flex; gap:1rem; align-items:center;">
                    <?php if ( $wc_active ) : ?>
                        <span class="easy-roles-pill easy-roles-pill--wc">
                            <span class="dashicons dashicons-store"></span>
                            <?php echo esc_html( $t['wc_detected'] ); ?>
                        </span>
                    <?php endif; ?>

                    <div class="easy-roles-lang-switch">
                        <a href="<?php echo esc_url( $url_en ); ?>" class="easy-roles-lang-btn <?php echo ! $is_es ? 'active' : ''; ?>">EN</a>
                        <span class="easy-roles-lang-sep">|</span>
                        <a href="<?php echo esc_url( $url_es ); ?>" class="easy-roles-lang-btn <?php echo $is_es ? 'active' : ''; ?>">ES</a>
                    </div>
                </div>
            </header>

            <!-- =========================================================
                 WELCOME / INTRO SECTION
            ========================================================== -->
            <div class="easy-roles-intro">
                <h2 class="easy-roles-intro__title">
                    <span class="dashicons dashicons-lightbulb"></span>
                    <?php echo esc_html( $t['intro_title'] ); ?>
                </h2>
                <p class="easy-roles-intro__text">
                    <?php echo esc_html( $t['intro_p1'] ); ?>
                </p>
                <p class="easy-roles-intro__text">
                    <?php echo esc_html( $t['intro_p2'] ); ?>
                </p>

                <div class="easy-roles-intro__steps">
                    <div class="easy-roles-intro__step">
                        <span class="easy-roles-intro__step-num">1</span>
                        <div class="easy-roles-intro__step-content">
                            <span class="easy-roles-intro__step-title">
                                <?php echo esc_html( $t['step_1_title'] ); ?>
                            </span>
                            <span class="easy-roles-intro__step-desc">
                                <?php echo esc_html( $t['step_1_desc'] ); ?>
                            </span>
                        </div>
                    </div>
                    <div class="easy-roles-intro__step">
                        <span class="easy-roles-intro__step-num">2</span>
                        <div class="easy-roles-intro__step-content">
                            <span class="easy-roles-intro__step-title">
                                <?php echo esc_html( $t['step_2_title'] ); ?>
                            </span>
                            <span class="easy-roles-intro__step-desc">
                                <?php echo esc_html( $t['step_2_desc'] ); ?>
                            </span>
                        </div>
                    </div>
                    <div class="easy-roles-intro__step">
                        <span class="easy-roles-intro__step-num">3</span>
                        <div class="easy-roles-intro__step-content">
                            <span class="easy-roles-intro__step-title">
                                <?php echo esc_html( $t['step_3_title'] ); ?>
                            </span>
                            <span class="easy-roles-intro__step-desc">
                                <?php echo esc_html( $t['step_3_desc'] ); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- =========================================================
                 TABS
            ========================================================== -->
            <nav class="easy-roles-tabs" role="tablist" aria-label="<?php esc_attr_e( 'Plugin navigation', 'easy-roles-gb' ); ?>">
                <button class="easy-roles-tab easy-roles-tab--active"
                        role="tab"
                        aria-selected="true"
                        aria-controls="er-panel-roles"
                        id="er-tab-roles"
                        type="button">
                    <span class="dashicons dashicons-list-view"></span>
                    <?php echo esc_html( $t['roles_tab'] ); ?>
                </button>
                <button class="easy-roles-tab"
                        role="tab"
                        aria-selected="false"
                        aria-controls="er-panel-create"
                        id="er-tab-create"
                        type="button">
                    <span class="dashicons dashicons-plus-alt2"></span>
                    <?php echo esc_html( $t['create_tab'] ); ?>
                </button>
                <button class="easy-roles-tab easy-roles-tab--hidden"
                        role="tab"
                        aria-selected="false"
                        aria-controls="er-panel-edit"
                        id="er-tab-edit"
                        type="button">
                    <span class="dashicons dashicons-edit"></span>
                    <?php echo esc_html( $t['edit_tab'] ); ?>
                </button>
            </nav>

            <!-- =========================================================
                 PANEL: ROLES LIST
            ========================================================== -->
            <section class="easy-roles-panel easy-roles-panel--active"
                     role="tabpanel"
                     id="er-panel-roles"
                     aria-labelledby="er-tab-roles">

                <div class="easy-roles-help">
                    <span class="dashicons dashicons-info-outline"></span>
                    <div class="easy-roles-help__content">
                        <span class="easy-roles-help__title"><?php echo esc_html( $t['about_screen'] ); ?></span>
                        <span class="easy-roles-help__text">
                            <?php echo esc_html( $t['about_text'] ); ?>
                        </span>
                    </div>
                </div>

                <div class="easy-roles-cards" id="er-roles-grid">
                    <?php
                    // Flatten WC caps for custom role detection
                    $wc_caps_flat = array();
                    if ( ! empty( $wc_groups ) ) {
                        foreach ( $wc_groups as $g ) {
                            if ( isset( $g['caps'] ) ) {
                                foreach ( $g['caps'] as $c => $d ) {
                                    $wc_caps_flat[ $c ] = true;
                                }
                            }
                        }
                    }

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
                        if ( $is_wc ) {
                            $card_class = 'easy-roles-card--wc';
                        } elseif ( $is_prot ) {
                            $card_class = 'easy-roles-card--protected';
                        } else {
                            $card_class = 'easy-roles-card--custom';
                        }
                    ?>
                    <div class="easy-roles-card <?php echo esc_attr( $card_class ); ?>"
                         data-role="<?php echo esc_attr( $slug ); ?>">
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
                                    <span class="easy-roles-pill easy-roles-pill--wc">
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
                                    <span title="<?php echo esc_attr( $t['users'] ); ?>">
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
                        <h2><?php echo esc_html( $t['create_tab'] ); ?></h2>
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

                    <?php self::render_capability_groups( $cap_groups, $wc_groups, $extra_caps, $label_key, $desc_key, 'create' ); ?>

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

                <form id="er-form-edit" class="easy-roles-form" novalidate>
                    <input type="hidden" name="role_slug" id="er-edit-slug" value="">

                    <div class="easy-roles-form__header">
                        <h2>
                            <?php echo esc_html( $t['edit_tab'] ); ?>:
                            <span id="er-edit-title" class="easy-roles-edit-title"></span>
                        </h2>
                        <div class="easy-roles-edit-meta">
                            <span id="er-edit-badge"></span>
                            <span id="er-edit-user-count"></span>
                        </div>
                    </div>

                    <div class="easy-roles-help">
                        <span class="dashicons dashicons-info-outline"></span>
                        <div class="easy-roles-help__content">
                            <span class="easy-roles-help__title"><?php echo esc_html( $t['help_edit'] ); ?></span>
                            <span class="easy-roles-help__text">
                                <?php echo esc_html( $t['help_edit_text'] ); ?>
                            </span>
                        </div>
                    </div>

                    <div class="easy-roles-form__fields">
                        <div class="easy-roles-field">
                            <label for="er-edit-name"><?php echo esc_html( $t['role_name'] ); ?></label>
                            <input type="text"
                                   id="er-edit-name"
                                   name="role_name"
                                   class="regular-text"
                                   required
                                   aria-required="true"
                                   maxlength="100">
                            <p class="description"><?php echo esc_html( $t['name_desc'] ); ?></p>
                        </div>
                    </div>

                    <h3 class="easy-roles-section-title"><?php echo esc_html( $t['permissions'] ); ?></h3>
                    <p class="easy-roles-section-subtitle">
                        <?php echo esc_html( $t['perm_desc'] ); ?>
                    </p>

                    <div class="easy-roles-caps-toolbar">
                        <div class="easy-roles-search">
                            <span class="dashicons dashicons-search"></span>
                            <input type="search"
                                   id="er-edit-search"
                                   class="easy-roles-search__input"
                                   placeholder="<?php echo esc_attr( $t['search_perm'] ); ?>"
                                   aria-label="<?php echo esc_attr( $t['search_perm'] ); ?>">
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

                    <?php self::render_capability_groups( $cap_groups, $wc_groups, $extra_caps, $label_key, $desc_key, 'edit' ); ?>

                    <div class="easy-roles-form__footer">
                        <button type="button" class="easy-roles-btn easy-roles-btn--back" id="er-edit-back">
                            <span class="dashicons dashicons-arrow-left-alt"></span>
                            <?php echo esc_html( $t['back_roles'] ); ?>
                        </button>
                        <button type="submit" class="easy-roles-btn easy-roles-btn--submit">
                            <span class="dashicons dashicons-yes-alt"></span>
                            <?php echo esc_html( $t['save_changes'] ); ?>
                        </button>
                    </div>
                </form>
            </section>

            <!-- Toast container -->
            <div class="easy-roles-toast" id="er-toast" role="alert" aria-live="polite"></div>
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
    private static function render_capability_groups( $cap_groups, $wc_groups, $extra_caps, $label_key, $desc_key, $context ) {
        ?>
        <div class="easy-roles-cap-groups" data-context="<?php echo esc_attr( $context ); ?>">
            <?php
            /* Core WP groups */
            foreach ( $cap_groups as $group_id => $group ) {
                self::render_single_group( $group_id, $group, $label_key, $desc_key, $context );
            }

            /* WooCommerce groups */
            if ( ! empty( $wc_groups ) ) {
                foreach ( $wc_groups as $group_id => $group ) {
                    self::render_single_group( $group_id, $group, $label_key, $desc_key, $context );
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
                foreach ( array_keys( $extra_caps ) as $cap ) {
                    $extra_group['caps'][ $cap ] = array(
                        'desc_es' => 'Capacidad registrada por un plugin o tema de terceros.',
                        'desc_en' => 'Capability registered by a third-party plugin or theme.',
                    );
                }
                self::render_single_group( 'other', $extra_group, $label_key, $desc_key, $context );
            }
            ?>
            <p class="easy-roles-no-results" style="display:none;">
                <?php esc_html_e( 'No capabilities match your search.', 'easy-roles-gb' ); ?>
            </p>
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
    private static function render_single_group( $group_id, $group, $label_key, $desc_key, $context ) {
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
                    $is_read   = ( 'read' === $cap_name );
                    $cap_class = 'easy-roles-cap';
                    if ( $is_read ) {
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
                            <?php echo esc_html( $cap_name ); ?>
                            <?php if ( $is_read ) : ?>
                                <span class="easy-roles-badge easy-roles-badge--prot" style="margin-left:0.5rem; font-size:0.65rem; padding:0.1rem 0.4rem;">
                                    <?php echo $label_key === 'label_es' ? 'Esencial' : 'Essential'; ?>
                                </span>
                            <?php endif; ?>
                        </span>
                        <span class="easy-roles-cap__desc"><?php echo esc_html( $cap_meta[ $desc_key ] ); ?></span>
                    </span>
                </label>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }
}
