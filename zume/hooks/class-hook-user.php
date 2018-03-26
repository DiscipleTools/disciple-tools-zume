<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

class DT_Zume_Hook_User extends DT_Zume_Hook_Base {

    public function hooks_user_register( $user_id ) {
        $new_key = DT_Zume_Zume::get_foreign_key( $user_id );
        add_user_meta( $user_id, 'zume_foreign_key', $new_key, true ); // leave update task to be ran on next logon
    }

    public function hooks_profile_update( $user_id ) {
//        try {
//            $send_new_user = new DT_Zume_Send_Update_Contact();
//            $send_new_user->launch(
//                [
//                    'user_id'   => $user_id,
//                ]
//            );
//        } catch ( Exception $e ) {
//            dt_write_log( '@' . __METHOD__ );
//            dt_write_log( 'Caught exception: ',  $e->getMessage(), "\n" );
//        }
    }

    public function hooks_wp_login( $user_login, $user ) {
        dt_zume_async_task_processor();
        dt_write_log( '@' . __METHOD__ );
        try {
            $send_new_user = new DT_Zume_Send_Last_Login();
            $send_new_user->launch(
            [
            'user_id'   => $user->ID,
            ]
            );
        } catch ( Exception $e ) {
            dt_write_log( '@' . __METHOD__ );
            dt_write_log( 'Caught exception: ',  $e->getMessage(), "\n" );
        }
    }

    public function __construct() {
        add_action( 'user_register', [ &$this, 'hooks_user_register' ], 99, 1 );
        add_action( 'profile_update', [ &$this, 'hooks_profile_update' ], 10, 1 );
        add_action( 'wp_login', [ &$this, 'hooks_wp_login' ], 10, 2 );

        parent::__construct();
    }

}

/**
 * Temporarily store a need for update status.
 * Used especially in the context of registration and logout, so the process runs on next login
 *
 */
function dt_zume_async_task_processor() {
    $tasks = get_user_meta( get_current_user_id(), 'zume_async_task' );
    if ( ! empty( $tasks ) ) {
        foreach ( $tasks as $task ) {
            switch ( $task ) {
                case 'registration':
                    try {
                        $send_new_user = new DT_Zume_Send_New_Contact();
                        $send_new_user->launch(
                        [
                        'user_id'   => get_current_user_id(),
                        ]
                        );
                    } catch ( Exception $e ) {
                        dt_write_log( 'Caught exception: ',  $e->getMessage(), "\n" );
                    }
                    break;
                default:
                    break;
            }
        }

        delete_user_meta( get_current_user_id(), 'zume_async_task' ); // clean tasks
    }
    return;
}
add_action( 'zume_dashboard_footer', 'dt_zume_async_task_processor' );
