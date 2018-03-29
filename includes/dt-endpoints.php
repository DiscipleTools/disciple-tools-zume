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
            $namespace, '/zume/session_complete_transfer', [
                [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => [ $this, 'session_complete_transfer' ],
                ],
            ]
        );

        register_rest_route(
            $namespace, '/zume/three_month_plan_submitted', [
            [
            'methods'  => WP_REST_Server::CREATABLE,
            'callback' => [ $this, 'three_month_plan_submitted' ],
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

                $zume_group_key = sanitize_key( wp_unslash( $params['group_raw_record']['key'] ) );

                // check if group exists
                $group_id = $this->get_id_from_group_key( $zume_group_key );
                if ( ! $group_id ) {
                    $new_group_id = Disciple_Tools_Groups::create_group( [ 'title' => $params['group_raw_record']['group_name'] ], false );

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

    /**
     * Respond to transfer request of files
     *
     * @param \WP_REST_Request $request
     * @return bool|\WP_Error
     */
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

    public function get_id_from_group_key( $zume_group_key ) {
        global $wpdb;
        $post_id = $wpdb->get_var( $wpdb->prepare("
                    SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'zume_group_key' AND meta_value = %s
                ",
            $zume_group_key
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
        ]
        ];

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

}
DT_Zume_DT_Endpoints::instance();