<?php
/**
 * DT_Zume_Core_Endpoints
 *
 * @class      DT_Zume_Core_Endpoints
 * @since      0.1.0
 * @package    DT_Webform
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}


/**
 * Class DT_Webform_Home_Endpoints
 */
class DT_Zume_Core_Endpoints
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
        $public_namespace = 'dt-public/v' . $version;
        $private_namespace = 'dt/v' . $version;

        register_rest_route(
            $public_namespace, '/zume/session_complete_transfer', [
                [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => [ $this, 'session_complete_transfer' ],
                ],
            ]
        );

        register_rest_route(
            $public_namespace, '/zume/three_month_plan_submitted', [
            [
            'methods'  => WP_REST_Server::CREATABLE,
            'callback' => [ $this, 'three_month_plan_submitted' ],
            ],
            ]
        );

        /**
         * Charts and Reports
         */
        register_rest_route(
            $private_namespace, '/zume/reset_zume_stats', [
               [
                   'methods'  => WP_REST_Server::READABLE,
                   'callback' => [ $this, 'reset_zume_stats' ],
               ],
            ]
        );
    }

    public function session_complete_transfer( WP_REST_Request $request ) {
        dt_write_log( __METHOD__ );

        $params = $request->get_params();

        $site_key = Site_Link_System::verify_transfer_token( $params['transfer_token'] );
        if ( ! is_wp_error( $site_key ) && $site_key ) {
            $added = [ 'group' => 0, 'owner' => 0, 'coleaders' => 0 ];
            $errors = [];

     // check owner exists
            if ( isset( $params['owner_raw_record']['zume_foreign_key'] ) && ! empty( $params['owner_raw_record']['zume_foreign_key'] ) ) {
                dt_write_log( 'Owner' );
                dt_write_log( $params['owner_raw_record']['zume_foreign_key'] );
                dt_write_log( $params['owner_raw_record'] );

                $members = [];
                $owner_foreign_key = sanitize_key( wp_unslash( $params['owner_raw_record']['zume_foreign_key'] ) );

                $owner_post_id = $this->get_id_from_zume_foreign_key( $owner_foreign_key );
                if ( ! $owner_post_id ) {
                    // Create owner contact
                    $fields = $this->build_dt_contact_record_array( $params['owner_raw_record'] );
                    $new_post_id = Disciple_Tools_Contacts::create_contact( $fields, false );

                    if ( is_wp_error( $new_post_id ) ) {
                        $errors[] = $new_post_id;
                    } else {
                        add_post_meta( $new_post_id, 'zume_foreign_key', $owner_foreign_key, true );
                        add_post_meta( $new_post_id, 'zume_raw_record', $params['owner_raw_record'], true );
                        add_post_meta( $new_post_id, 'zume_check_sum', $params['owner_raw_record']['zume_check_sum'], true );
                        $owner_post_id = $new_post_id;
                        $added['owner']++;
                    }
                }
                $members[] = $owner_post_id;
            }

    // parse for coleaders and test if they exist
            if ( isset( $params['coleaders'] ) && ! empty( $params['coleaders'] ) ) {
                foreach ( $params['coleaders'] as $foreign_key => $raw_record ) {
                    dt_write_log( 'Coleader' );
                    dt_write_log( $foreign_key );
                    dt_write_log( $raw_record );

                    $post_id = $this->get_id_from_zume_foreign_key( $foreign_key );
                    if ( ! $post_id ) {
                        // Create owner contact
                        $fields = $this->build_dt_contact_record_array( $raw_record );
                        $new_post_id = Disciple_Tools_Contacts::create_contact( $fields, false );

                        if ( is_wp_error( $new_post_id ) ) {
                            $errors[] = $new_post_id;
                        } else {
                            add_post_meta( $new_post_id, 'zume_foreign_key', $foreign_key, true );
                            add_post_meta( $new_post_id, 'zume_raw_record', $raw_record, true );
                            add_post_meta( $new_post_id, 'zume_check_sum', $raw_record['zume_check_sum'], true );
                            $post_id = $new_post_id;
                            $added['owner']++;
                        }
                    }
                    $members[] = $post_id;

                    $added['coleaders']++;
                }
            }

     // check group exists
            if ( isset( $params['group_raw_record']['key'] ) && ! empty( $params['group_raw_record']['key'] ) ) {
                dt_write_log( 'Group' );
                dt_write_log( $params['group_raw_record']['key'] );
                dt_write_log( $params['group_raw_record'] );

                $zume_foreign_key = sanitize_key( wp_unslash( $params['group_raw_record']['foreign_key'] ) );

                // check if group exists
                $group_id = $this->get_id_from_zume_foreign_key( $zume_foreign_key );
                if ( ! $group_id ) {

                    $fields = $this->build_group_record_array( $params['group_raw_record'], $owner_post_id );

                    $new_group_id = Disciple_Tools_Groups::create_group( $fields, false );

                    if ( is_wp_error( $new_group_id ) ) {
                        $errors[] = $new_group_id;
                    } else {
                        $group_id = $new_group_id;
                    }
                }

                if ( $group_id ) {
                    $members = array_filter( $members );
                    if ( ! empty( $members ) ) {
                        foreach ( $members as $member ) {
                            Disciple_Tools_Groups::add_item_to_field( $group_id, "members", $member, false );
                        }
                    }

                    add_post_meta( $group_id, 'zume_foreign_key', $params['group_raw_record']['foreign_key'], true );
                    add_post_meta( $group_id, 'zume_raw_record', $params['group_raw_record'], true );
                    add_post_meta( $group_id, 'zume_check_sum', $params['group_raw_record']['zume_check_sum'], true );
                }
                // add members to group

                $added['group']++;
            }

            return [ $added, $errors ];

        } else {
            dt_write_log( 'failed_authentication' );
            return new WP_Error( 'failed_authentication', 'Failed id and/or token authentication.' );
        }
    }

    public function three_month_plan_submitted( WP_REST_Request $request ) {

        dt_write_log( __METHOD__ );

        $params = $request->get_params();
        dt_write_log( $params );

        $site_key = Site_Link_System::verify_transfer_token( $params['transfer_token'] );
        if ( ! is_wp_error( $site_key ) && $site_key ) {

            if ( isset( $params['zume_foreign_key'] ) && ! empty( $params['zume_foreign_key'] ) ) {

                // Check if zume_foreign_key already in system


                // find contact
                $post_id = sanitize_key( wp_unslash( $this->get_id_from_zume_foreign_key( $params['zume_foreign_key'] ) ) );

                if ( ! $post_id ) {
                    return false;
                }

                dt_activity_insert(
                    [
                    'action' => 'logged_into_zume',
                    'object_type' => 'contacts',
                    'object_subtype' => 'Contacts',
                    'object_id' => $post_id,
                    'object_name' => get_the_title( $post_id ),
                    'meta_id'           => ' ',
                    'meta_key'          => ' ',
                    'meta_value'        => ' ',
                    'meta_parent'        => ' ',
                    'object_note'       => __( 'Logged into ZumeProject.com' ),
                    ]
                );

                return true;

            } else {
                return new WP_Error( 'malformed_content', 'Did not find `zume_foreign_key` in array.' );
            }
        } else {
            return new WP_Error( 'failed_authentication', 'Failed id and/or token authentication.' );
        }
    } // @todo finish

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

    public function build_dt_contact_record_array( $user_data ) {
        // Build new DT record data
        $fields = [
            'title' => $user_data['title'],
            "contact_email" => [
                [ "value" => $user_data['user_email'] ],
            ],
            "sources" => [
                "values" => [
                    [ "value" => "Zume" ],
                ],
                "force_values" => false
            ]
        ];

        // get or create location
        $location_post_id = $this->parse_raw_user_record_for_location_id( $user_data );
        if ( $location_post_id ) {
            $fields['locations'] = [
                "values" => [
                    [ "value" => $location_post_id ],
                ],
                "force_values" => false,
            ];
        }

        if ( !empty( $user_data['zume_phone_number'] ) ) { // add phone
            $phone = $user_data['zume_phone_number'] ?? '';
            $fields['contact_phone'] = [
            [ "value" => $phone ],
            ];
        }
        if ( ! empty( $user_data['zume_user_address'] ) || ! empty( $user_data['zume_address_from_ip'] ) ) { // add address
            $address = $user_data['zume_user_address'] ?: $user_data['zume_address_from_ip'];
            $fields['contact_address'] = [
            [ "value" => $address ]
            ];
        }
        $user_data_string = ''; // add raw record into starting note
        foreach ( $user_data as $key => $item ) {
            if ( ! 'zume_foreign_key' === $key && ! 'zume_check_sum' === $key && ! empty( $item ) ) {
                $user_data_string .= $item . '; ';
            }
        }
        $fields['notes'] = [
        'user_snapshot' => $user_data_string,
        ];

        return $fields;
    }


    public function parse_raw_user_record_for_location_id( $raw_user_record ) {

        if ( ! isset( $raw_user_record['zume_raw_location'] ) || ! isset( $raw_user_record['zume_raw_location_from_ip'] ) ) {
            return false;
        }

        if ( $raw_user_record['zume_raw_location'] ) { // prioritize user provided location info
            $raw_location = $raw_user_record['zume_raw_location'];
        } else {
            $raw_location = $raw_user_record['zume_raw_location_from_ip'];
        }

        if ( ! Disciple_Tools_Google_Geocode_API::check_valid_request_result( $raw_location ) ) { // test for valid raw location data
            return false;
        }

        $post_id = false;
        $auto_create_location = dt_get_option( 'auto_location' );
        $location_level_preference = dt_get_option( 'location_levels' );

        // @todo note overwrite the $post_id at each check down the chain, so all is created in parent order, but returns post_id
        if ( $country = Disciple_Tools_Google_Geocode_API::parse_raw_result( $raw_location, 'country' ) ) {
            // lookup country location

            // auto create??
        }


        $country = Disciple_Tools_Google_Geocode_API::parse_raw_result( $raw_user_record, 'country' );


        // check for admin1 location post_id with country parent_id, or create admin1 post_id
        // check for admin2 location post_id with country parent_id, or create admin1 post_id
        // check for admin3 location post_id with country parent_id, or create admin1 post_id
        // check for admin4 location post_id with country parent_id, or create admin1 post_id
        // check for locality location post_id with country parent_id, or create admin1 post_id
        // check for neighborhood location post_id with country parent_id, or create admin1 post_id

        return $post_id;
    }

    public function parse_raw_group_record_for_location_id( $raw_group_record ) {
        $raw_result_data = false;
        // if get valid raw location data
        // else get valid location components
        // if valid location components, get raw location response
        // else return no location id


        // parse valid location data for location components
        if ( ! Disciple_Tools_Google_Geocode_API::check_valid_request_result( $raw_result_data ) ) {
            return false;
        }

        $post_id = false;
        // @todo note overwrite the $post_id at each check down the chain, so all is created in parent order, but returns post_id

        // check for country location post_id, or create country location post_id
        // check for admin1 location post_id with country parent_id, or create admin1 post_id
        // check for admin2 location post_id with country parent_id, or create admin1 post_id
        // check for admin3 location post_id with country parent_id, or create admin1 post_id
        // check for admin4 location post_id with country parent_id, or create admin1 post_id
        // check for locality location post_id with country parent_id, or create admin1 post_id
        // check for neighborhood location post_id with country parent_id, or create admin1 post_id

        return $post_id;
    }

    /**
     * @param $raw_user_record
     *
     * @return bool|array
     */
    public function get_raw_result_from_user_data( $raw_user_record ) {
        $valid_record = [];

        // cascade/overwrite the retrieval. ip location is less accurate than user provided.
        if ( ! empty( $raw_user_record['zume_raw_location_from_ip'] ) ) {
            if ( Disciple_Tools_Google_Geocode_API::check_valid_request_result( $raw_user_record['zume_raw_location_from_ip'] ) ) {
                $valid_record = $raw_user_record['zume_raw_location'];
            }
        }

        if ( ! empty( $raw_user_record['zume_raw_location'] ) ) {
            if ( Disciple_Tools_Google_Geocode_API::check_valid_request_result( $raw_user_record['zume_raw_location'] ) ) {
                $valid_record = $raw_user_record['zume_raw_location'];
            }
        }

        if ( empty( $valid_record ) ){
            return false;
        } else {
            return $valid_record;
        }
    }


    public function build_group_record_array( $raw_record, $owner_post_id ) {

        $fields = [
            "title" => $raw_record['group_name'],
            "group_type" => "group",
            "group_status" => "active",
            "created_from_contact_id" => $owner_post_id,
        ];

        if ( ! empty( $raw_record['address'] ) || ! empty( $raw_record['ip_address'] ) ) {
            $fields["contact_address"] = [
                [ "value" => sanitize_text_field( wp_unslash( $raw_record['address'] ?: $raw_record['ip_address'] ) ) ]
            ];
        }

        if ( ! empty( $raw_record['created_date'] ) ){
            $fields["start_date"] = substr( $raw_record['created_date'], 0, 10 );
        }

        // get or build location
        $location_post_id = $this->parse_raw_user_record_for_location_id( $raw_record );
        if ( $location_post_id ) {
            $fields['locations'] = [
                "values" => [
                    [ "value" => $location_post_id ],
                ],
                "force_values" => false,
            ];
        }

        return $fields;
    }

    public function reset_zume_stats() {

        if ( !self::can_view( 'zume_pipeline', get_current_user() ) ) {
            return new WP_Error( __FUNCTION__, __( "No permissions to read report" ), [ 'status' => 403 ] );
        }

        $raw_record = get_option( 'zume_stats_raw_record' );
        if ( isset( $raw_record['timestamp'] ) && isset( $raw_record['zume_stats_check_sum'] ) ) {
            $raw_record['timestamp'] = '';
            $raw_record['zume_stats_check_sum'] = '';
            update_option( 'zume_stats_raw_record', $raw_record ); // keep array in case of failure in retrieval
        } else {
            update_option( 'zume_stats_raw_record', [] ); // wipe corrupt array
        }

        $raw_record = DT_Zume_Core::get_project_stats();
        if ( empty( $raw_record ) ) {
            // log failure and leave
            dt_write_log( __METHOD__ );
            dt_write_log( 'Attempt to update metrics data failed.' );
            dt_write_log( new WP_Error( __METHOD__, 'Failed to get remote statistics data. Returned empty array.' ) );
            return new WP_Error( __METHOD__, 'Failed to get remote statistics data. Returned empty array.' );
        } else {
            return $raw_record;
        }
    }

    public static function can_view( $report_name, $user_id )
    {
        // TODO decide on permission strategy for reporting
        // Do we hardwire permissions to reports to the roles of a person?
        // Do we set up a permission assignment tool in the config area, so that a group could assign reports to a role
        if ( empty( $user_id ) ) {
            $user_id = get_current_user_id();
        }
        if ( ! $user_id ) {
            return false;
        }

        switch ( $report_name ) {
            case 'zume_pipeline':
                return true;
                break;
            default:
                return true; // TODO temporary true response returned until better permissions check is created
                break;
        }
    }

    /**
     * @param        $location_data
     * @param string $type
     *
     * @return array|\WP_Error
     */
    public function build_location_from_raw_info( $location_data, $type = 'google_result' ) {

        $address_components = [];
        $raw_google_response = [];

        // check for nulls and build array for searching
        switch ( $type ) {
            case 'google_result': // this is a raw google geocoding result
                if ( ! is_null( $location_data ) && isset( $location_data['status'] ) && ( 'OK' == $location_data['status'] ?? ''  ) ) {
                    $address_components = $location_data['results'][0]['address_components'];
                    $raw_google_response = $location_data;
                }
                break;
            case 'address':
                if ( ! empty( $location_data ) ) {
                    $result = Disciple_Tools_Google_Geocode_API::query_google_api( $location_data );
                    if ( $result ) {
                        $address_components = $result['results'][0]['address_components'];
                        $raw_google_response = $result;
                    }
                }
                break;
            case 'ip_address':
                // @todo reverse geocode from lng/lat
                /** @link https://developers.google.com/maps/documentation/geocoding/intro#ReverseGeocoding */
                break;
            case 'lng_lat':
                // @todo lookup ip address, then reverse geocode from lng/lat of ip address
                break;
            default:
                break;
        }

        if ( empty( $address_components ) ) {
            dt_write_log( new WP_Error( __METHOD__, 'No valid address components' ) );
            dt_write_log( $address_components );
            return new WP_Error( __METHOD__, 'No valid address components' );
        }

        $location = [];
        $level1 = '';
        $level2 = '';

        foreach ( $address_components as $address_component ) {
            if ( 'neighborhood' == $address_component['types'][0] ) {
                $location['neighborhood'] = $address_component['long_name'];
                $level2 .= $location['neighborhood'] . ', ';
            }
            if ( 'locality' == $address_component['types'][0] ) {
                $location['locality'] = $address_component['long_name'];
                $level2 .= $location['locality'] . ', ';
            }
            if ( 'administrative_area_level_2' == $address_component['types'][0] ) {
                $location['admin_2'] = $address_component['long_name'];
                $level2 .= $location['admin_2'] . ', ';
            }
            if ( 'administrative_area_level_1' == $address_component['types'][0] ) {
                $location['admin_1'] = $address_component['long_name'];
                $level1 .= $location['admin_1'] . ', ';
            }
            if ( 'country' == $address_component['types'][0] ) {
                $location['country'] = $address_component['long_name'];
                $level1 .= $location['country'];
            }
            $level1 = rtrim( $level1, ',' );
        }

        $level2 = substr( $level2, 0, -2 );

        $location['level2'] = $level2;
        $location['level1'] = $level1;
        $location['raw'] = $raw_google_response;

        return $location;
    }


}
DT_Zume_Core_Endpoints::instance();