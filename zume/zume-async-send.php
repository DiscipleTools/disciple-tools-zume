<?php
/**
 * Zume Send Contact
 * This async file must be loaded from the functions.php file, or else weird things happen. :)
 */

/**
 * Send Post Request
 * $args = [
 *  'method' => 'POST',
 *   'body' => [
 *   'transfer_token' => $site['transfer_token'],
 *   'transfer_record' => $fields,
 *   'zume_foreign_key' => $user_data['zume_foreign_key'],
 *   'zume_language' => $user_data['zume_language'],
 *   'zume_check_sum' => $user_data['zume_check_sum'],
 * ]
 * ];
 *
 * @param $endpoint
 * @param $url
 * @param $args
 *
 * @return array|\WP_Error
 */
function dt_zume_remote_send( $endpoint, $url, $args ) {

    $result = wp_remote_post( 'https://' . $url . '/wp-json/dt-public/v1/zume/' . $endpoint, $args );

    if ( is_wp_error( $result ) ) {
        return new WP_Error( 'failed_remote_get', $result->get_error_message() );
    }
    return $result;
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
                        $send_new_user = new DT_Zume_Send_New_User();
                        $send_new_user->launch(
                            [
                            'user_id'   => get_current_user_id(),
                            ]
                        );
                    } catch ( Exception $e ) {
                        dt_write_log( 'Caught exception: ',  $e->getMessage(), "\n" );
                    }

                    delete_user_meta( get_current_user_id(), 'zume_async_task' );
                    break;
                default:
                    break;
            }
        }

        delete_user_meta( get_current_user_id(), 'zume_async_task' );
    }
}
add_action( 'zume_dashboard_footer', 'dt_zume_async_task_processor' );


/**
 * Class Disciple_Tools_Insert_Location
 */
class DT_Zume_Send_New_User extends Disciple_Tools_Async_Task
{
    protected $action = 'send_new_user';

    protected function prepare_data( $data ) { return $data; }

    public function send()
    {
            // @codingStandardsIgnoreStart
        if( isset( $_POST[ 'action' ] ) && sanitize_key( wp_unslash( $_POST[ 'action' ] ) ) == 'dt_async_'.$this->action && isset( $_POST[ '_nonce' ] ) && $this->verify_async_nonce( sanitize_key( wp_unslash( $_POST[ '_nonce' ] ) ) ) ) {
            $user_id = sanitize_key( wp_unslash( $_POST[0]['user_id'] ) );
            // @codingStandardsIgnoreEnd

            $object = new DT_Zume_Zume();
            $object->send_user_data( $user_id );

        } // end if check
    }

    protected function run_action(){}
}
function dt_load_async_send_new_user()
{
    if ( isset( $_POST['_wp_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wp_nonce'] ) ) ) && isset( $_POST['action'] ) && sanitize_key( wp_unslash( $_POST['action'] ) ) == 'dt_async_send_new_user' ) {
        try {
            $insert_location = new DT_Zume_Send_New_User();
            $insert_location->send();
        } catch ( Exception $e ) {
            dt_write_log( 'Caught exception: ',  $e->getMessage(), "\n" );
        }
    }
}
add_action( 'init', 'dt_load_async_send_new_user' );

