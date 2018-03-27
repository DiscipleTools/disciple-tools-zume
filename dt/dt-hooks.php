<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Class DT_Zume_Hooks
 */
class DT_Zume_DT_Hooks
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
        new DT_Zume_DT_Hook_User();
        new DT_Zume_DT_Hook_Groups();
    }
}
DT_Zume_DT_Hooks::instance();

/**
 * Empty class for now..
 * Class DT_Zume_Hook_Base
 */
abstract class DT_Zume_DT_Hook_Base
{
    public function __construct()
    {
    }
}

/**
 * Class DT_Zume_Hook_User
 */
class DT_Zume_DT_Hook_User extends DT_Zume_DT_Hook_Base {

    public function __construct() {

        parent::__construct();
    }

}

/**
 * Class DT_Zume_Hook_Groups
 */
class DT_Zume_DT_Hook_Groups extends DT_Zume_DT_Hook_Base {


    public function __construct() {

        parent::__construct();
    }

}


