<?php
/**
 * Plugin Name: Disciple Tools - Zume
 * Plugin URI: https://github.com/ZumeProject/disciple-tools-zume
 * Description: Disciple Tools - Zume plugin integrates the Disciple Tools system into the Zume Project.
 * Version:  0.1.0
 * Author URI: https://github.com/ZumeProject
 * GitHub Plugin URI: https://github.com/DiscipleTools/disciple-tools-zume
 * Requires at least: 4.7.0
 * (Requires 4.7+ because of the integration of the REST API at 4.7 and the security requirements of this milestone version.)
 * Tested up to: 4.9
 *
 * @package Disciple_Tools
 * @link    https://github.com/DiscipleTools
 * @license GPL-2.0 or later
 *          https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Gets the instance of the `DT_Zume` class.  This function is useful for quickly grabbing data
 * used throughout the plugin.
 *
 * @since  0.1
 * @access public
 * @return object
 */
function dt_zume() {
    $current_theme = get_option( 'current_theme' );
    if ( 'Disciple Tools' == $current_theme || 'Zúme Project' == $current_theme ) {
        return DT_Zume::get_instance();
    }
    else {
        add_action( 'admin_notices', 'dt_zume_no_disciple_tools_theme_found' );
        return new WP_Error( 'current_theme_not_dt', 'Disciple Tools Theme not active.' );
    }

}
add_action( 'plugins_loaded', 'dt_zume' );

/**
 * Singleton class for setting up the plugin.
 *
 * @since  0.1
 * @access public
 */
class DT_Zume {

    /**
     * Declares public variables
     *
     * @since  0.1
     * @access public
     * @return object
     */
    public $token;
    public $version;
    public $dir_path = '';
    public $dir_uri = '';
    public $img_uri = '';
    public $includes_path;

    /**
     * Returns the instance.
     *
     * @since  0.1
     * @access public
     * @return object
     */
    public static function get_instance() {

        static $instance = null;

        if ( is_null( $instance ) ) {
            $instance = new dt_zume();
            $instance->setup();
            $instance->includes();
            $instance->setup_actions();
        }
        return $instance;
    }

    /**
     * Constructor method.
     *
     * @since  0.1
     * @access private
     * @return void
     */
    private function __construct() {
    }

    /**
     * Loads files needed by the plugin.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    private function includes() {

        // Call site link system if Disciple Tools is not the theme, else use this.
        $current_theme = get_option( 'current_theme' );
        if ( 'Zúme Project' === $current_theme ) {
            require_once( 'includes/site-link-system.php' ); // site linking system
        }

        require_once( 'includes/admin/admin-menu-and-tabs.php' );
    }

    /**
     * Sets up globals.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    private function setup() {

        // Main plugin directory path and URI.
        $this->dir_path     = trailingslashit( plugin_dir_path( __FILE__ ) );
        $this->dir_uri      = trailingslashit( plugin_dir_url( __FILE__ ) );

        // Plugin directory paths.
        $this->includes_path      = trailingslashit( $this->dir_path . 'includes' );

        // Plugin directory URIs.
        $this->img_uri      = trailingslashit( $this->dir_uri . 'img' );

        // Admin and settings variables
        $this->token             = 'dt_zume';
        $this->version             = '0.1';
    }

    /**
     * Sets up main plugin actions and filters.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    private function setup_actions() {

        // Internationalize the text strings used.
        add_action( 'plugins_loaded', array( $this, 'i18n' ), 2 );

    }

    /**
     * Method that runs only when the plugin is activated.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public static function activation() {
        // Add integration capacity to administrator based on manage_dt
        $role = get_role( 'administrator' );
        $role->add_cap( 'manage_dt' );
    }

    /**
     * Method that runs only when the plugin is deactivated.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public static function deactivation() {
    }

    /**
     * Loads the translation files.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public function i18n() {
        load_plugin_textdomain( 'dt_zume', false, trailingslashit( dirname( plugin_basename( __FILE__ ) ) ). 'languages' );
    }

    /**
     * Magic method to output a string if trying to use the object as a string.
     *
     * @since  0.1
     * @access public
     * @return string
     */
    public function __toString() {
        return 'dt_zume';
    }

    /**
     * Magic method to keep the object from being cloned.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public function __clone() {
        _doing_it_wrong( __FUNCTION__, esc_html__( 'Whoah, partner!', 'dt_zume' ), '0.1' );
    }

    /**
     * Magic method to keep the object from being unserialized.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public function __wakeup() {
        _doing_it_wrong( __FUNCTION__, esc_html__( 'Whoah, partner!', 'dt_zume' ), '0.1' );
    }

    /**
     * Magic method to prevent a fatal error when calling a method that doesn't exist.
     *
     * @since  0.1
     * @access public
     * @return null
     */
    public function __call( $method = '', $args = array() ) {
        // @codingStandardsIgnoreLine
        _doing_it_wrong( "dt_zume::{$method}", esc_html__( 'Method does not exist.', 'dt_zume' ), '0.1' );
        unset( $method, $args );
        return null;
    }
}
// end main plugin class

// Register activation hook.
register_activation_hook( __FILE__, [ 'DT_Zume', 'activation' ] );
register_deactivation_hook( __FILE__, [ 'DT_Zume', 'deactivation' ] );

/**
 * Admin alert for when Disciple Tools Theme is not available
 */
function dt_zume_no_disciple_tools_theme_found()
{
    ?>
    <div class="notice notice-error">
        <p><?php esc_html_e( "'Disciple Tools - Zume' plugin requires 'Disciple Tools' or 'Zúme Project' themes to work. Please activate 'Disciple Tools' or 'Zúme Project' themes or deactivate 'Disciple Tools - Zume' plugin.", "dt_zume" ); ?></p>
    </div>
    <?php
}

/**
 * A simple function to assist with development and non-disruptive debugging.
 * -----------
 * -----------
 * REQUIREMENT:
 * WP Debug logging must be set to true in the wp-config.php file.
 * Add these definitions above the "That's all, stop editing! Happy blogging." line in wp-config.php
 * -----------
 * define( 'WP_DEBUG', true ); // Enable WP_DEBUG mode
 * define( 'WP_DEBUG_LOG', true ); // Enable Debug logging to the /wp-content/debug.log file
 * define( 'WP_DEBUG_DISPLAY', false ); // Disable display of errors and warnings
 * @ini_set( 'display_errors', 0 );
 * -----------
 * -----------
 * EXAMPLE USAGE:
 * (string)
 * write_log('THIS IS THE START OF MY CUSTOM DEBUG');
 * -----------
 * (array)
 * $an_array_of_things = ['an', 'array', 'of', 'things'];
 * write_log($an_array_of_things);
 * -----------
 * (object)
 * $an_object = new An_Object
 * write_log($an_object);
 */
if ( !function_exists( 'dt_write_log' ) ) {
    /**
     * A function to assist development only.
     * This function allows you to post a string, array, or object to the WP_DEBUG log.
     *
     * @param $log
     */
    function dt_write_log( $log )
    {
        if ( true === WP_DEBUG ) {
            if ( is_array( $log ) || is_object( $log ) ) {
                error_log( print_r( $log, true ) );
            } else {
                error_log( $log );
            }
        }
    }
}