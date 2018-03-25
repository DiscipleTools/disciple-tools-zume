<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Class Disciple_Tools_Notification_Hooks
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
        // Load abstract class.
        include( 'hooks/abstract-class-hook-base.php' );

        // Load all our hooks.
        include( 'hooks/class-hook-field-updates.php' );
        include( 'hooks/class-hook-user.php' );
        include( 'hooks/class-hook-groups.php' );

        new DT_Zume_Hook_Field_Updates();
        new DT_Zume_Hook_User();
        new DT_Zume_Hook_Groups();
    }
}
DT_Zume_Hooks::instance();