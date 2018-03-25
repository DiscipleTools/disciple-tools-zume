<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

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
