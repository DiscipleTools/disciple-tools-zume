<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Class DT_Zume_Hooks
 */
class DT_Zume_Hooks
{

    private static $_instance = null;

    public static function instance()
    {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    /**
     * Build hook classes
     */
    public function __construct()
    {
        new DT_Zume_Hook_Field_Updates();
        new DT_Zume_Hook_User();
        new DT_Zume_Hook_Groups();
    }
}
DT_Zume_Hooks::instance();

/**
 * Empty class for now..
 * Class DT_Zume_Hook_Base
 */
abstract class DT_Zume_Hook_Base
{
    public function __construct()
    {
    }
}

/**
 * Class DT_Zume_Hook_Groups
 */
class DT_Zume_Hook_Groups extends DT_Zume_Hook_Base {

    public function hooks_session_complete( $zume_group_key, $zume_session, $current_user_id ) {
        dt_write_log( '@' . __METHOD__ );
        if ( get_option( 'zume_session_complete_transfer_level' ) == $zume_session ) {
            // check if
        }
    }

    public function __construct() {
        add_action( 'dt_zume_session_complete', [ &$this, 'hooks_session_complete' ], 10, 3 );

        parent::__construct();
    }

}

/**
 * Class Disciple_Tools_Notifications_Hook_Field_Updates
 */
class DT_Zume_Hook_Field_Updates extends DT_Zume_Hook_Base
{
    /**
     * Disciple_Tools_Notifications_Hook_Field_Updates constructor.
     */
    public function __construct()
    {
        add_action( "updated_user_meta", [ &$this, 'hooks_update_user_meta' ], 10, 4 );
        //do_action( "updated_{$meta_type}_meta", $meta_id, $object_id, $meta_key, $_meta_value );

        parent::__construct();
    }

    /**
     * Filter hook to see if it is a zume_group update
     *
     * @param $meta_id
     * @param $object_id
     * @param $meta_key
     * @param $meta_value
     */
    public function hooks_update_user_meta( $meta_id, $object_id, $meta_key, $meta_value ) {
        if( substr( $meta_key, 0, 10) == 'zume_group' ) {
            $this->send_group_update( $meta_id, $object_id, $meta_key, $meta_value );
            return;
        }
        return;
    }

    public function send_group_update( $meta_id, $object_id, $meta_key, $meta_value ) {

    }
}

/**
 * Class DT_Zume_Hook_User
 */
class DT_Zume_Hook_User extends DT_Zume_Hook_Base {

    public function hooks_user_register( $user_id ) {
        $new_key = DT_Zume_Zume::get_foreign_key( $user_id );
        add_user_meta( $user_id, 'zume_foreign_key', $new_key, true ); // add zume foreign key
    }

    public function hooks_profile_update( $user_id ) {
        dt_write_log( '@' . __METHOD__ );
    }

    public function hooks_wp_login( $user_login, $user ) {
        dt_write_log( '@' . __METHOD__ );
    }

    public function __construct() {
        add_action( 'user_register', [ &$this, 'hooks_user_register' ], 99, 1 );
        //        add_action( 'profile_update', [ &$this, 'hooks_profile_update' ], 10, 1 ); // @todo remove
        //        add_action( 'wp_login', [ &$this, 'hooks_wp_login' ], 10, 2 ); // @todo remove

        parent::__construct();
    }

}