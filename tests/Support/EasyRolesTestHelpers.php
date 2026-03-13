<?php

trait Easy_Roles_Test_Helpers {

    /**
     * @var int[]
     */
    protected $created_users = array();

    /**
     * @var string[]
     */
    protected $created_roles = array();

    protected function create_administrator_user() {
        $user_id = self::factory()->user->create(
            array(
                'role'       => 'administrator',
                'user_login' => 'admin_' . wp_generate_password( 8, false ),
            )
        );

        $this->created_users[] = $user_id;

        return $user_id;
    }

    protected function create_subscriber_user() {
        $user_id = self::factory()->user->create(
            array(
                'role'       => 'subscriber',
                'user_login' => 'subscriber_' . wp_generate_password( 8, false ),
            )
        );

        $this->created_users[] = $user_id;

        return $user_id;
    }

    protected function track_role( $slug ) {
        if ( ! in_array( $slug, $this->created_roles, true ) ) {
            $this->created_roles[] = $slug;
        }
    }

    protected function cleanup_easy_roles_state() {
        foreach ( $this->created_roles as $slug ) {
            if ( wp_roles()->is_role( $slug ) ) {
                remove_role( $slug );
            }
        }

        $this->created_roles = array();

        foreach ( $this->created_users as $user_id ) {
            wp_delete_user( $user_id );
        }

        $this->created_users = array();

        delete_option( 'easy_roles_custom_roles' );
        delete_option( 'easy_roles_changelog' );
        delete_option( 'easy_roles_version' );
        delete_metadata( 'user', 0, 'easy_roles_lang', '', true );
        wp_set_current_user( 0 );
        wp_cache_delete( 'easy_roles_user_counts', 'easy_roles' );
    }

    protected function set_valid_ajax_request( $action, $data = array() ) {
        $_POST = array_merge(
            array(
                'action' => $action,
                '_nonce' => wp_create_nonce( 'easy_roles_nonce' ),
            ),
            $data
        );
    }
}
