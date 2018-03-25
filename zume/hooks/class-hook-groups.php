<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

class DT_Zume_Hook_Groups extends DT_Zume_Hook_Base {

    public function hooks_create_group( $user_id, $group_key, $group_data ) {
        dt_write_log( '@' . __METHOD__ );
    }

    public function hooks_edit_group( $user_id, $group_key, $group_data ) {
        dt_write_log( '@' . __METHOD__ );
    }

    public function hooks_delete_group( $user_id, $group_key, $group_data ) {
        dt_write_log( '@' . __METHOD__ );
    }

    public function __construct() {
        add_action( 'dt_zume_create_group', [ &$this, 'hooks_create_group' ], 10, 3 );
        add_action( 'dt_zume_edit_group', [ &$this, 'hooks_create_group' ], 10, 3 );
        add_action( 'dt_zume_delete_group', [ &$this, 'hooks_delete_group' ], 10, 3 );

        parent::__construct();
    }

}