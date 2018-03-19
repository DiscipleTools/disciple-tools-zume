<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

class DT_Zume_Hook_User extends DT_Zume_Hook_Base {

    public function hooks_wp_login( $user_login, $user ) {
        dt_write_log(
            [
            'action'      => 'logged_in',
            'object_type' => 'User',
            'user_id'     => $user->ID,
            'object_id'   => $user->ID,
            'object_name' => $user->user_nicename,
             ]
        );
    }

    public function hooks_user_register( $user_id ) {
        $user = get_user_by( 'id', $user_id );

        dt_write_log(
            [
            'action'      => 'created',
            'object_type' => 'User',
            'object_id'   => $user->ID,
            'object_name' => $user->user_nicename,
             ]
        );
    }

    public function hooks_delete_user( $user_id ) {
        $user = get_user_by( 'id', $user_id );

        dt_write_log(
            [
            'action'      => 'deleted',
            'object_type' => 'User',
            'object_id'   => $user->ID,
            'object_name' => $user->user_nicename,
             ]
        );
    }

    public function hooks_profile_update( $user_id ) {
        $user = get_user_by( 'id', $user_id );

        dt_write_log(
            [
            'action'      => 'profile_update',
            'object_type' => 'User',
            'object_id'   => $user->ID,
            'object_name' => $user->user_nicename,
             ]
        );
    }

    public function __construct() {
        add_action( 'wp_login', [ &$this, 'hooks_wp_login' ], 10, 2 );
        add_action( 'delete_user', [ &$this, 'hooks_delete_user' ] );
        add_action( 'user_register', [ &$this, 'hooks_user_register' ] );
        add_action( 'profile_update', [ &$this, 'hooks_profile_update' ] );

        parent::__construct();
    }

}
