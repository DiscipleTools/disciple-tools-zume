<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

class DT_Zume_Hook_User extends DT_Zume_Hook_Base {

    public function hooks_user_register( $user_id ) {
        add_user_meta( $user_id, 'zume_async_task', 'registration', false );
        dt_write_log('@' . __METHOD__ );
    }

    public function hooks_profile_update( $user_id ) {
        // send updated information with new contact information

        $user = get_user_by( 'id', $user_id );

        dt_write_log( [
            'task' => 'New User Registered',
            'user_id' => $user->ID,
            'user_object' => $user,
            'user_meta' => dt_zume_get_user_meta( $user_id ),
        ]);
    }

    public function hooks_wp_login( $user_login, $user ) {
        dt_write_log('@' . __METHOD__ );
    }

    public function __construct() {
        add_action( 'user_register', [ &$this, 'hooks_user_register' ], 99, 1 );
        add_action( 'profile_update', [ &$this, 'hooks_profile_update' ] );
        add_action( 'wp_login', [ &$this, 'hooks_wp_login' ], 10, 2 );

        parent::__construct();
    }

}
