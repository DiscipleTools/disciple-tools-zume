<?php
/**
 * DT_Zume_DT_Endpoints
 *
 * @class      DT_Zume_DT_Endpoints
 * @since      0.1.0
 * @package    DT_Webform
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}


/**
 * Class DT_Webform_Home_Endpoints
 */
class DT_Zume_DT_Endpoints
{
    private static $_instance = null;

    public static function instance()
    {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Constructor function.
     *
     * @access  public
     * @since   0.1.0
     */
    public function __construct()
    {
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    } // End __construct()

    public function add_api_routes()
    {
        $version = '1';
        $namespace = 'dt-public/v' . $version;

        register_rest_route(
            $namespace, '/zume/create_new_contact', [
            [
            'methods'  => WP_REST_Server::CREATABLE,
            'callback' => [ $this, 'create_new_contact' ],
            ],
            ]
        );

        register_rest_route(
            $namespace, '/zume/update_contact', [
            [
            'methods'  => WP_REST_Server::CREATABLE,
            'callback' => [ $this, 'update_contact' ],
            ],
            ]
        );
    }

    /**
     * Respond to transfer request of files
     *
     * @param \WP_REST_Request $request
     * @return bool|\WP_Error
     */
    public function create_new_contact( WP_REST_Request $request ) {

        dt_write_log( __METHOD__ );

        $params = $request->get_params();
        $site_key = DT_Site_Link_System::verify_transfer_token( $params['transfer_token'] );

        dt_write_log( $params );

        if ( ! is_wp_error( $site_key ) && $site_key ) {

            if ( isset( $params['transfer_record'] ) && ! empty( $params['transfer_record'] ) ) {

                $post_id = Disciple_Tools_Contacts::create_contact( $params['transfer_record'], false );

                if ( is_wp_error( $post_id ) || empty( $post_id ) ) {
                    return new WP_Error( 'failed_insert', 'Failed record creation' );
                }

                add_post_meta( $post_id, 'zume_raw_record', $params['raw_record'], true );
                add_post_meta( $post_id, 'zume_foreign_key', $params['raw_record']['zume_foreign_key'], true );

                return true;

            } else {
                return new WP_Error( 'malformed_content', 'Did not find `selected_records` in array.' );
            }
        } else {
            return new WP_Error( 'failed_authentication', 'Failed id and/or token authentication.' );
        }
    }

    /**
     * Respond to transfer request of files
     *
     * @param \WP_REST_Request $request
     * @return bool|\WP_Error
     */
    public function update_contact( WP_REST_Request $request ) {

        dt_write_log( __METHOD__ );

        $params = $request->get_params();
        dt_write_log( $params );

        $site_key = DT_Site_Link_System::verify_transfer_token( $params['transfer_token'] );
        if ( ! is_wp_error( $site_key ) && $site_key ) {

            if ( isset( $params['raw_record']['zume_foreign_key'] ) && ! empty( $params['raw_record']['zume_foreign_key'] ) ) {

                $post_id = sanitize_key( wp_unslash( $this->get_id_from_zume_foreign_key( $params['zume_foreign_key'] ) ) );

                if ( ! $post_id ) {
                    return false;
                }

                update_post_meta( $post_id, 'zume_raw_record', $params['raw_record'] );

                return true;

            } else {
                return new WP_Error( 'malformed_content', 'Did not find `raw_record` in array.' );
            }
        } else {
            return new WP_Error( 'failed_authentication', 'Failed id and/or token authentication.' );
        }
    }

    /**
     * Respond to transfer request of files
     *
     * @param \WP_REST_Request $request
     * @return bool|\WP_Error
     */
    public function contact_last_login( WP_REST_Request $request ) {

        dt_write_log( __METHOD__ );

        $params = $request->get_params();
        dt_write_log( $params );

        $site_key = DT_Site_Link_System::verify_transfer_token( $params['transfer_token'] );
        if ( ! is_wp_error( $site_key ) && $site_key ) {

            if ( isset( $params['zume_foreign_key'] ) && ! empty( $params['zume_foreign_key'] ) ) {

                // find contact
                $post_id = sanitize_key( wp_unslash( $this->get_id_from_zume_foreign_key( $params['zume_foreign_key'] ) ) );

                if ( ! $post_id ) {
                    return false;
                }

                dt_activity_insert(
                    [
                    'action' => 'logged_into_zume',
                    'object_type' => 'Post',
                    'object_subtype' => 'Contacts',
                    'object_id' => $post_id,
                    'object_name' => get_the_title( $post_id ),
                    'meta_id'           => ' ',
                    'meta_key'          => ' ',
                    'meta_value'        => ' ',
                    'meta_parent'        => ' ',
                    'object_note'       => __('Logged into ZumeProject.com'),
                    ]
                );

                return true;

            } else {
                return new WP_Error( 'malformed_content', 'Did not find `raw_record` in array.' );
            }
        } else {
            return new WP_Error( 'failed_authentication', 'Failed id and/or token authentication.' );
        }
    }

    public function get_id_from_zume_foreign_key( $zume_foreign_key ) {
        global $wpdb;
        $post_id = $wpdb->get_var( $wpdb->prepare("
                    SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'zume_foreign_key' AND meta_value = %s
                ",
        $zume_foreign_key
        ) );

        if ( ! $post_id ) {
            return false;
        }
        return $post_id;
    }
}
DT_Zume_DT_Endpoints::instance();