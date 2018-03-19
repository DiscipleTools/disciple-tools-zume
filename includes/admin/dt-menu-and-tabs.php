<?php
/**
 * DT_Webform_Menu class for the admin page
 *
 * @class       DT_Webform_Menu
 * @version     0.1.0
 * @since       0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class DT_Webform_Menu
 */
DT_Zume_Menu::instance(); // Initialize class
class DT_Zume_Menu
{
    public $token;

    private static $_instance = null;

    public static function instance()
    {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    /**
     * Constructor function.
     *
     * @access  public
     * @since   0.1.0
     */
    public function __construct()
    {
        $this->token = DT_Zume::get_instance()->token;
        add_action( "admin_menu", [ $this, "register_menu" ] );
    } // End __construct()

    /**
     * Loads the subnav page
     *
     * @since 0.1.0
     */
    public function register_menu()
    {
        add_menu_page( __( 'Zúme Integration', 'disciple_tools' ), __( 'Zúme Integration', 'disciple_tools' ), 'manage_dt', 'dt_zume', [ $this, 'content' ], 'dashicons-admin-generic', 59 );

    }

    /**
     * Combined tabs preprocessor
     */
    public function content()
    {

        if ( ! current_user_can( 'manage_dt' ) ) {
            wp_die( esc_attr__( 'You do not have sufficient permissions to access this page.' ) );
        }

        $title = __( 'ZUME / DISCIPLE TOOLS - INTEGRATION' );

        $link = 'admin.php?page=' . $this->token . '&tab=';

        $tab_bar = [
        [
        'key'   => 'activity',
        'label' => __( 'Activity', 'dt_zume' ),
        ],
        [
        'key' => 'site_links',
        'label' => __( 'Site Links', 'dt_zume' ),
        ],
        [
        'key' => 'settings',
        'label' => __( 'Settings', 'dt_zume' ),
        ],
        ];

        // determine active tabs
        $active_tab = 'activity';

        if ( isset( $_GET["tab"] ) ) {
            $active_tab = sanitize_key( wp_unslash( $_GET["tab"] ) );
        }

        $this->tab_loader( $title, $active_tab, $tab_bar, $link );
    }


    /**
     * Tab Loader
     *
     * @param $title
     * @param $active_tab
     * @param $tab_bar
     * @param $link
     */
    public function tab_loader( $title, $active_tab, $tab_bar, $link ) {
        ?>
        <div class="wrap">

            <h2><?php echo esc_attr( $title ) ?></h2>

            <h2 class="nav-tab-wrapper">
                <?php foreach ( $tab_bar as $tab) : ?>
                    <a href="<?php echo esc_attr( $link . $tab['key'] ) ?>"
                       class="nav-tab <?php echo ( $active_tab == $tab['key'] ) ? esc_attr( 'nav-tab-active' ) : ''; ?>">
                        <?php echo esc_attr( $tab['label'] ) ?>
                    </a>
                <?php endforeach; ?>
            </h2>

            <?php
            switch ( $active_tab ) {

                case "activity":
                    $this->tab_activity();
                    break;
                case 'site_links':
                    $this->tab_site_links();
                    break;
                case "settings":
                    $this->tab_settings();
                    break;
                default:
                    break;
            }
            ?>

        </div><!-- End wrap -->

        <?php
    }


    public function tab_activity() {

        // begin columns template
        $this->template( 'begin', 1 );

        DT_Zume_Activity::list_box();

        // end columns template
        $this->template( 'end', 1 );
    }

    public function tab_site_links() {
        // begin columns template
        $this->template( 'begin' );

        DT_Site_Link_System::metabox_multiple_link(); // main column content

        // begin right column template
        $this->template( 'right_column' );
        // end columns template
        $this->template( 'end' );
    }

    public function tab_settings() {
        // begin columns template
        $this->template( 'begin' );

        // begin right column template
        $this->template( 'right_column' );
        // end columns template
        $this->template( 'end' );
    }

    public function template( $section, $columns = 2 ) {
        switch ( $columns ) {

            case '1':
                switch ( $section ) {
                    case 'begin':
                        ?>
                        <div class="wrap">
                        <div id="poststuff">
                        <div id="post-body" class="metabox-holder columns-1">
                        <div id="post-body-content">
                        <!-- Main Column -->
                        <?php
                        break;


                    case 'end':
                        ?>
                        </div><!-- postbox-container 1 -->
                        </div><!-- post-body meta box container -->
                        </div><!--poststuff end -->
                        </div><!-- wrap end -->
                        <?php
                        break;
                }
                break;

            case '2':
                switch ( $section ) {
                    case 'begin':
                        ?>
                        <div class="wrap">
                        <div id="poststuff">
                        <div id="post-body" class="metabox-holder columns-2">
                        <div id="post-body-content">
                        <!-- Main Column -->
                        <?php
                        break;
                    case 'right_column':
                        ?>
                        <!-- End Main Column -->
                        </div><!-- end post-body-content -->
                        <div id="postbox-container-1" class="postbox-container">
                        <!-- Right Column -->
                        <?php
                    break;
                    case 'end':
                        ?>
                        </div><!-- postbox-container 1 -->
                        </div><!-- post-body meta box container -->
                        </div><!--poststuff end -->
                        </div><!-- wrap end -->
                        <?php
                        break;
                }
                break;
        }
    }
}