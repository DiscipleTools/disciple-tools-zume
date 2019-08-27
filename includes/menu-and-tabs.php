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

    public static function instance() {
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
    public function __construct() {
        $this->token = DT_Zume::get_instance()->token;
        add_action( "admin_menu", [ $this, "register_menu" ] );
    } // End __construct()

    /**
     * Loads the subnav page
     *
     * @since 0.1.0
     */
    public function register_menu() {
        add_menu_page( __( 'Zúme Integration', 'disciple_tools' ), __( 'Zúme Integration', 'disciple_tools' ), 'manage_dt', 'dt_zume', [ $this, 'dt_content' ], dt_svg_icon(), 100 );
        add_meta_box( 'site_link_system_extensions', 'Zúme Default Site', [ $this, 'meta_box_extensions' ], 'site_link_system', 'normal', 'low' );
    }

    public function meta_box_extensions() {
        $current_key = get_option( 'zume_default_site' );
        $keys = Site_Link_System::get_site_keys();
        if ( ! isset( $keys[$current_key] ) ) {
            ?>
            You need to set the default Zume connection <a href="<?php echo esc_url( admin_url() ) . 'admin.php?page=dt_zume' ?>">Set Connection</a>
            <?php
        } else {
            echo '<strong>' . esc_attr( $keys[$current_key]['label'] ) . '</strong> is the current Zume site link';
        }
    }

    /**
     * Combined tabs preprocessor
     */
    public function dt_content() {

        if ( ! current_user_can( 'manage_dt' ) ) {
            wp_die( esc_attr__( 'You do not have sufficient permissions to access this page.' ) );
        }

        $title = __( 'ZUME / DISCIPLE TOOLS - INTEGRATION' );

        $link = 'admin.php?page=' . $this->token . '&tab=';

        $tab_bar = [
            [
                'key' => 'settings',
                'label' => __( 'Settings', 'dt_zume' ),
            ],
            [
                'key' => 'utilities',
                'label' => __( 'Utilities', 'dt_zume' ),
            ]
        ];

        // determine active tabs
        $active_tab = 'settings';

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

                case "settings":
                    $this->tab_dt_settings();
                    break;
                case "utilities":
                    $this->tab_utilities();
                    break;
                default:
                    break;
            }
            ?>

        </div><!-- End wrap -->

        <?php
    }

    public function tab_dt_settings() {
        // begin columns template
        $this->template( 'begin' );

        // add boxes
        $this->site_default_metabox();

        $this->template( 'right_column' );
        $this->template( 'end' );
    }

    public function tab_utilities() {
        // begin columns template
        $this->template( 'begin' );

        // add boxes
        $this->raw_data_retrieval_test();
        $this->function_test();

        $this->template( 'right_column' );
        $this->template( 'end' );
    }

    public static function site_default_metabox() {
        // Check for post
        if ( isset( $_POST['dt_site_default_nonce'] ) && ! empty( $_POST['dt_site_default_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dt_site_default_nonce'] ) ), 'dt_site_default_'. get_current_user_id() ) ) {
            if ( isset( $_POST['default-site'] ) && ! empty( $_POST['default-site'] ) ) {
                $default_site = sanitize_key( wp_unslash( $_POST['default-site'] ) );
                update_option( 'zume_default_site', $default_site );
            }
        }
        $keys = Site_Link_System::get_site_keys();
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
                            <option></option>
                            <?php foreach ($keys as $key => $value ) : ?>
                                <option value="<?php echo esc_attr( $key ) ?>" <?php $current_key == $key ? print esc_attr( 'selected' ) : print '';  ?> >
                                    <?php echo esc_html( $value['label'] )?>
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

    public static function raw_data_retrieval_test() {
        $error = [];
        // Check for post
        if ( isset( $_POST['dt_site_raw_data_nonce'] ) && ! empty( $_POST['dt_site_raw_data_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dt_site_raw_data_nonce'] ) ), 'dt_site_raw_data_'. get_current_user_id() ) ) {
            if ( isset( $_POST['raw_data_test'] ) && ! empty( $_POST['raw_data_test'] ) ) {

                $raw_record = get_option( 'zume_stats_raw_record' );
                $error[] = $raw_record;
                if ( ! empty( $raw_record ) && isset( $raw_record['timestamp'] ) && ! empty( $raw_record['timestamp'] ) ) { // check if already checked today
                    if ( ! ( date( 'Ymd' ) > date( 'Ymd', strtotime( $raw_record['timestamp'] ) ) ) ) {
                        $error[] = $raw_record;
                    }
                }

                $check_sum = $raw_record['zume_stats_check_sum'] ?: md5( 'no_check_sum' );

                $site = DT_Zume_Core::get_site_details( get_option( 'zume_default_site' ) );
                if ( is_wp_error( $site ) ) {
                    dt_write_log( $site );
                    $error[] = $site;
                } else {
                    // Send remote request
                    $args = [
                        'method' => 'POST',
                        'body' => [
                            'transfer_token' => $site['transfer_token'],
                            'zume_stats_check_sum' => $check_sum,
                        ]
                    ];
                    $result = DT_Zume_Core::remote_send( 'get_project_stats', $site['url'], $args );
                    if ( is_wp_error( $result ) || is_wp_error( $result['body'] ) ) {
                        dt_write_log( $result );
                        $error[] = $result;

                    }
                    elseif ( isset( $result['body'] ) ) {
                        $response = json_decode( $result['body'], true );

                        if ( isset( $response['status'] ) ) {
                            if ( $response['status'] == 'OK' ) {
                                // updated timestamp of the raw record
                                $raw_record = get_option( 'zume_stats_raw_record' );
                                $raw_record['timestamp'] = current_time( 'mysql' );
                                update_option( 'zume_stats_raw_record', $raw_record );

                                $error[] = get_option( 'zume_stats_raw_record', [] );

                            } elseif ( $response['status'] == 'Update_Needed' && isset( $response['raw_record'] ) ) {
                                update_option( 'zume_stats_raw_record', $response['raw_record'] );
                                $error[] = get_option( 'zume_stats_raw_record', [] );

                            } else {
                                // error
                                dt_write_log( 'RESPONSE STATUS ERROR' );
                                dt_write_log( $response );

                                $error[] = [];
                            }
                        } else {
                            // error
                            dt_write_log( 'RESPONSE STATUS NOT SET' );
                            dt_write_log( $response );

                            $error[] = [];
                        }
                    } else {
                        dt_write_log( 'RESPONSE BODY ERROR' );
                        dt_write_log( $result );

                        $error[] = [];
                    }
                }
            }
        }

        ?>
        <form method="post" action="">
            <?php wp_nonce_field( 'dt_site_raw_data_'. get_current_user_id(), 'dt_site_raw_data_nonce', false, true ) ?>

            <!-- Box -->
            <table class="widefat striped">
                <thead>
                <tr>
                    <td>
                        <?php esc_html_e( 'Retrieval Test' ) ?>
                    </td>
                </tr>
                </thead>
                <tbody>

                <tr>
                    <td>
                        <button class="button" name="raw_data_test" value="1" type="submit"><?php esc_html_e( 'Test' ) ?></button>
                    </td>
                </tr>
                </tbody>
            </table>
            <br>
            <!-- End Box -->

            <?php if ( ! empty( $error ) ) {
                print_r( $error );
            }
            ?>

        </form>
        <?php
    }

    public static function function_test() {
        $report = [];
        // Check for post
        if ( isset( $_POST['dt_zume_test_nonce'] )
            && ! empty( $_POST['dt_zume_test_nonce'] )
            && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dt_zume_test_nonce'] ) ), 'dt_zume_test'. get_current_user_id() )
            && isset( $_POST['dt_zume_test'] )
            && ! empty( $_POST['dt_zume_test'] ) ) {

            if ( get_transient( 'sample_google_response' ) && empty( $_POST['address'] ) ) {
                $raw = maybe_unserialize( get_transient( 'sample_google_response' ) );
            } else {
                $result = DT_Mapbox_API::forward_lookup( sanitize_text_field( wp_unslash( $_POST['address'] ) ) ?: 'Highlands Ranch, CO 80126' );
                set_transient( 'sample_google_response', $result, 3600 );
                $raw = maybe_unserialize( get_transient( 'sample_google_response' ) );
            }
        }

        ?>
        <form method="post" action="">
            <?php wp_nonce_field( 'dt_zume_test'. get_current_user_id(), 'dt_zume_test_nonce', false, true ) ?>

            <!-- Box -->
            <table class="widefat striped">
                <thead>
                <tr>
                    <td>
                        <?php esc_html_e( 'Function Test (Dev)' ) ?>
                    </td>
                </tr>
                </thead>
                <tbody>

                <tr>
                    <td>
                        <label for="address">Address</label>
                        <input type="text" value="" id="address" name="address" />
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="item">Item</label>
                        <input type="text" value="" id="item" name="item" />
                    </td>
                </tr>
                <tr>
                    <td>
                        <button class="button" name="dt_zume_test" value="1" type="submit"><?php esc_html_e( 'Test' ) ?></button>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php print '<pre>'; print_r( $raw ?? '' )  ?>
                    </td>
                </tr>
                </tbody>
            </table>
            <br>
            <!-- End Box -->



        </form>
        <?php
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
