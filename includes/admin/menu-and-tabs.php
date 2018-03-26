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
        add_action( 'admin_head', [ $this, 'scripts' ], 20 );
    } // End __construct()

    /**
     * Loads the subnav page
     *
     * @since 0.1.0
     */
    public function register_menu()
    {
        if ( dt_zume_is_this_disciple_tools() ) {
            add_menu_page( __( 'Zúme Integration', 'disciple_tools' ), __( 'Zúme Integration', 'disciple_tools' ), 'manage_dt', 'dt_zume', [ $this, 'dt_content' ], 'dashicons-admin-generic', 59 );
        }
        else {
            add_menu_page( __( 'Zúme Integration', 'disciple_tools' ), __( 'Zúme Integration', 'disciple_tools' ), 'manage_dt', 'dt_zume', [ $this, 'zume_content' ], 'dashicons-admin-generic', 59 );
        }
    }

    /**
     * Combined tabs preprocessor
     */
    public function dt_content()
    {

        if ( ! current_user_can( 'manage_dt' ) ) {
            wp_die( esc_attr__( 'You do not have sufficient permissions to access this page.' ) );
        }

        $title = __( 'ZUME / DISCIPLE TOOLS - INTEGRATION' );

        $link = 'admin.php?page=' . $this->token . '&tab=';

        $tab_bar = [
        [
        'key' => 'site_links',
        'label' => __( 'Site Links', 'dt_zume' ),
        ],
        [
        'key' => 'dt_settings',
        'label' => __( 'Settings', 'dt_zume' ),
        ],
        ];

        // determine active tabs
        $active_tab = 'site_links';

        if ( isset( $_GET["tab"] ) ) {
            $active_tab = sanitize_key( wp_unslash( $_GET["tab"] ) );
        }

        $this->tab_loader( $title, $active_tab, $tab_bar, $link );
    }

    /**
     * Combined tabs preprocessor
     */
    public function zume_content()
    {

        if ( ! current_user_can( 'manage_dt' ) ) {
            wp_die( esc_attr__( 'You do not have sufficient permissions to access this page.' ) );
        }

        $title = __( 'ZUME / DISCIPLE TOOLS - INTEGRATION' );

        $link = 'admin.php?page=' . $this->token . '&tab=';

        $tab_bar = [
        [
        'key' => 'site_links',
        'label' => __( 'Site Links', 'dt_zume' ),
        ],
        [
        'key' => 'zume_settings',
        'label' => __( 'Settings', 'dt_zume' ),
        ],
        ];

        // determine active tabs
        $active_tab = 'site_links';

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
            <span class="text-light-grey"><?php echo esc_attr__( 'Active: Disciple Tools' ) ?></span>

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
                case "zume_settings":
                    $this->tab_zume_settings();
                    break;
                case "dt_settings":
                    $this->tab_dt_settings();
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

    public function tab_zume_settings() {
        // begin columns template
        $this->template( 'begin' );

        // Runs validation of the database when page is loaded.
        $object = new DT_Zume_Zume();
        $object->verify_foreign_key_installed();
        $object->verify_check_sum_installed();

        $this->site_default_metabox();
        $this->system_health_metabox();

        // begin right column template
        $this->template( 'right_column' );
        // end columns template
        $this->template( 'end' );
    }

    public function tab_dt_settings() {
        // begin columns template
        $this->template( 'begin' );

        // Runs validation of the database when page is loaded.
        $this->site_default_metabox();

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

    public function scripts() {

        echo "<style>
            .text-light-grey {
                color: lightsteelblue;
            }
            </style>";
    }

    public static function site_default_metabox()
    {
        // Check for post
        if ( isset( $_POST['dt_site_default_nonce'] ) && ! empty( $_POST['dt_site_default_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dt_site_default_nonce'] ) ), 'dt_site_default_'. get_current_user_id() ) ) {
            if ( isset( $_POST['default-site'] ) && ! empty( $_POST['default-site'] ) ) {
                $default_site = sanitize_key( wp_unslash( $_POST['default-site'] ) );
                update_option( 'zume_default_site', $default_site );
            }
        }
        $keys = DT_Site_Link_System::get_site_keys();
        $current_key = get_option( 'zume_default_site' );

        ?>
        <form method="post" action="">
            <?php wp_nonce_field( 'dt_site_default_'. get_current_user_id(), 'dt_site_default_nonce', false, true ) ?>

            <!-- Box -->
            <table class="widefat striped">
                <thead>
                <tr>
                    <td>
                        <?php esc_html_e( 'Set Default Transfer Site' ) ?>
                    </td>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <select id="default-site" name="default-site">
                            <?php foreach ($keys as $key => $value ) : ?>
                                <option value="<?php echo esc_attr( $key ) ?>" <?php $current_key == $key ? print esc_attr( 'selected' ) : print '';  ?> >
                                    <?php echo esc_html( $value['id'] )?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td>
                        <button class="button" type="submit"><?php esc_html_e( 'Update' ) ?></button>
                    </td>
                </tr>
                </tbody>
            </table>
            <br>
            <!-- End Box -->
        </form>
        <?php
    }

    public static function system_health_metabox()
    {
        $object = new DT_Zume_Zume();

        ?>
        <form method="post" action="">

            <table class="widefat striped">
                <thead>
                <tr>
                    <td colspan="2">
                        <?php esc_html_e( 'Connection Health' ) ?>
                    </td>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <?php esc_html_e( 'Foreign Keys Needing Update' ) ?>:
                    </td>
                    <td>
                        <?php echo esc_html( $object->verify_foreign_key_installed() )  ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php esc_html_e( 'Check Sum Records Needing Update' ) ?>:
                    </td>
                    <td>
                        <?php echo esc_html( $object->verify_check_sum_installed() )  ?>
                    </td>
                </tr>

                </tbody>
            </table>
            <br>

        </form>
        <?php
    }

}