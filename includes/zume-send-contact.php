<?php
/**
 * Zume Send Contact
 * This async file must be loaded from the functions.php file, or else weird things happen. :)
 */


/**
 * Class Disciple_Tools_Insert_Location
 */
class DT_Zume_Send_New_User extends Disciple_Tools_Async_Task
{
    protected $action = 'send_new_user';

    /**
     * Prepare data for the asynchronous request
     *
     * @throws Exception If for any reason the request should not happen.
     *
     * @param array $data An array of data sent to the hook
     *
     * @return array
     */
    protected function prepare_data( $data )
    {
        return $data;
    }

    /**
     * Insert Locations
     */
    public function send_new_user()
    {
        /**
         * Nonce validation is done through a custom nonce process inside DT_Zume_Send_New_User
         * to allow for asynchronous processing. This is a valid nonce but is not recognized by the WP standards checker.
         *
         */
        // @codingStandardsIgnoreLine
        if( isset( $_POST[ 'action' ] ) && sanitize_key( wp_unslash( $_POST[ 'action' ] ) ) == 'dt_async_'.$this->action && isset( $_POST[ '_nonce' ] ) && $this->verify_async_nonce( sanitize_key( wp_unslash( $_POST[ '_nonce' ] ) ) ) ) {

            dt_write_log( 'Send New User Info' );
//            dt_write_log( $_POST[0] );

            // send new user as contact to the disciple tools system


        } // end if check
    }

    /**
     * Run the async task action
     * Used when loading long running process with add_action
     * Not used when directly using launch().
     */
    protected function run_action(){
        dt_write_log( __METHOD__ );
        dt_write_log( 'Made it here' );
    }
}

/**
 * This hook function listens for the prepared async process on every page load.
 */
function dt_load_async_send_new_user()
{
    if ( isset( $_POST['_wp_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wp_nonce'] ) ) ) && isset( $_POST['action'] ) && sanitize_key( wp_unslash( $_POST['action'] ) ) == 'dt_async_send_new_user' ) {
        try {
            $insert_location = new DT_Zume_Send_New_User();
            $insert_location->send_new_user();
        } catch ( Exception $e ) {
            dt_write_log( 'Caught exception: ',  $e->getMessage(), "\n" );
        }
    }
}
add_action( 'init', 'dt_load_async_send_new_user' );