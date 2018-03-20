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
            $namespace, '/zume/create_new_contacts', [
            [
            'methods'  => WP_REST_Server::CREATABLE,
            'callback' => [ $this, 'create_new_contacts' ],
            ],
            ]
        );
    }

    /**
     * Respond to transfer request of files
     *
     * @param \WP_REST_Request $request
     * @return array|\WP_Error
     */
    public function create_new_contacts( WP_REST_Request $request ) {

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

                update_post_meta( $post_id, 'zume_foreign_key', $params['zume_foreign_key'] );
                update_post_meta( $post_id, 'zume_language', $params['zume_language'] );
                update_post_meta( $post_id, 'zume_check_sum', $params['zume_check_sum'] );


            } else {
                return new WP_Error( 'malformed_content', 'Did not find `selected_records` in array.' );
            }
        } else {
            return new WP_Error( 'failed_authentication', 'Failed id and/or token authentication.' );
        }
    }
}
DT_Zume_DT_Endpoints::instance();