<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * NOTE:
 * The menu and script load filter and action are located in /includes/hooks.php DT_Zume_Hooks_Metrics
 */

/**
 * Class DT_Zume_Metrics
 */
class DT_Zume_Metrics
{
    private static $_instance = null;

    public static function instance()
    {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct()
    {
    }



}

DT_Zume_Metrics::instance();