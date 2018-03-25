<?php
/**
 * Zume Send Contact
 * This async file must be loaded from the functions.php file, or else weird things happen. :)
 */

/**
 * Function checker for async post requests
 * This runs on every page load looking for an async post request
 */
function dt_load_async_send()
{
    // check for create new contact
    if ( isset( $_POST['_wp_nonce'] )
    && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wp_nonce'] ) ) )
    && isset( $_POST['action'] )
    && sanitize_key( wp_unslash( $_POST['action'] ) ) == 'dt_async_send_new_user' ) {
        try {
            $insert_location = new DT_Zume_Send_New_Contact();
            $insert_location->send();
        } catch ( Exception $e ) {
            dt_write_log( 'Caught exception: ',  $e->getMessage(), "\n" );
        }
    }

    // check for update contact
    if ( isset( $_POST['_wp_nonce'] )
    && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wp_nonce'] ) ) )
    && isset( $_POST['action'] )
    && sanitize_key( wp_unslash( $_POST['action'] ) ) == 'dt_async_send_update_contact' ) {
        try {
            $send = new DT_Zume_Send_Update_Contact();
            $send->send();
        } catch ( Exception $e ) {
            dt_write_log( 'Caught exception: ',  $e->getMessage(), "\n" );
        }
    }

    // check for contact last login
    if ( isset( $_POST['_wp_nonce'] )
    && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wp_nonce'] ) ) )
    && isset( $_POST['action'] )
    && sanitize_key( wp_unslash( $_POST['action'] ) ) == 'dt_async_send_contact_last_login' ) {
        try {
            $send = new DT_Zume_Send_Last_Login();
            $send->send();
        } catch ( Exception $e ) {
            dt_write_log( 'Caught exception: ',  $e->getMessage(), "\n" );
        }
    }

}
add_action( 'init', 'dt_load_async_send' );


/**
 * Class Disciple_Tools_Insert_Location
 */
class DT_Zume_Send_New_Contact extends Disciple_Tools_Async_Task
{
    protected $action = 'send_new_contact';

    protected function prepare_data( $data ) { return $data; }

    public function send()
    {
            // @codingStandardsIgnoreStart
        if( isset( $_POST[ 'action' ] )
            && sanitize_key( wp_unslash( $_POST[ 'action' ] ) ) == 'dt_async_'.$this->action
            && isset( $_POST[ '_nonce' ] )
            && $this->verify_async_nonce( sanitize_key( wp_unslash( $_POST[ '_nonce' ] ) ) ) ) {

            $user_id = sanitize_key( wp_unslash( $_POST[0]['user_id'] ) );
            // @codingStandardsIgnoreEnd

            $object = new DT_Zume_Zume();
            $object->send_new_contact( $user_id );

        } // end if check
        return;
    }

    protected function run_action(){}
}

/**
 * Class Disciple_Tools_Insert_Location
 */
class DT_Zume_Send_Update_Contact extends Disciple_Tools_Async_Task
{
    protected $action = 'send_update_contact';

    protected function prepare_data( $data ) { return $data; }

    public function send()
    {
        // @codingStandardsIgnoreStart
        if( isset( $_POST[ 'action' ] )
        && sanitize_key( wp_unslash( $_POST[ 'action' ] ) ) == 'dt_async_'.$this->action
        && isset( $_POST[ '_nonce' ] )
        && $this->verify_async_nonce( sanitize_key( wp_unslash( $_POST[ '_nonce' ] ) ) ) ) {

            $user_id = sanitize_key( wp_unslash( $_POST[0]['user_id'] ) );
            // @codingStandardsIgnoreEnd

            $object = new DT_Zume_Zume();
            $object->send_update_contact( $user_id );

        } // end if check
        return;
    }

    protected function run_action(){}
}

/**
 * Class Disciple_Tools_Insert_Location
 */
class DT_Zume_Send_Last_Login extends Disciple_Tools_Async_Task
{
    protected $action = 'send_contact_last_login';

    protected function prepare_data( $data ) { return $data; }

    public function send()
    {
        // @codingStandardsIgnoreStart
        if( isset( $_POST[ 'action' ] )
        && sanitize_key( wp_unslash( $_POST[ 'action' ] ) ) == 'dt_async_'.$this->action
        && isset( $_POST[ '_nonce' ] )
        && $this->verify_async_nonce( sanitize_key( wp_unslash( $_POST[ '_nonce' ] ) ) ) ) {

            $user_id = sanitize_key( wp_unslash( $_POST[0]['user_id'] ) );
            // @codingStandardsIgnoreEnd

            $object = new DT_Zume_Zume();
            $object->send_contact_last_login( $user_id );

        } // end if check
        return;
    }

    protected function run_action(){}
}


