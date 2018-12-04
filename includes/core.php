<?php

class DT_Zume_Core
{

    /**
     * @param        $post_id
     * @param string $type  Must be either `contact` or `group`
     *
     * @return bool
     */
    public static function check_for_update( $post_id, $type = 'contact' ) {
        dt_write_log( __METHOD__ );

        // check if already checked today
        if ( self::test_last_check( $post_id ) ) {
            return true;
        }

        $check_sum = get_post_meta( $post_id, 'zume_check_sum', true );
        if ( empty( $check_sum ) ) {
            $check_sum = 1;
        }

        $groups_check_sum = get_post_meta( $post_id, 'zume_groups_check_sum', true );
        if ( empty( $groups_check_sum ) ) {
            $groups_check_sum = 1;
        }

        $foreign_key = get_post_meta( $post_id, 'zume_foreign_key', true );
        if ( empty( $foreign_key ) ) {
            $foreign_key = 1;
        }

        $site = self::get_site_details( get_option( 'zume_default_site' ) );
        if ( is_wp_error( $site ) ) {
            dt_write_log( $site );
            return false;
        }

        // Send remote request
        $args = [
        'method' => 'POST',
        'body' => [
            'transfer_token' => $site['transfer_token'],
            'zume_check_sum' => $check_sum,
            'zume_groups_check_sum' => $groups_check_sum,
            'zume_foreign_key' => $foreign_key,
            'type' => $type,
            ]
        ];
        $result = self::remote_send( 'check_for_update', $site['url'], $args );
        if ( is_wp_error( $result ) || is_wp_error( $result['body'] ) ) {
            dt_write_log( $result );
            return false;
        }
        if ( isset( $result['body'] ) ) {
            $response = json_decode( $result['body'], true );
//            dt_write_log( $response );

            // test for status
            if ( isset( $response['status'] ) ) {

                // no updated needed
                if ( $response['status'] == 'OK' ) {
                    // no update needed
                    update_post_meta( $post_id, 'zume_last_check', current_time( 'mysql' ), current_time( 'mysql' ) );

                // update needed
                } elseif ( $response['status'] == 'Update_Needed' && isset( $response['raw_record'] ) && isset( $response['raw_group_records'] ) ) {

                    $new_check_sum = $response['raw_record']['zume_check_sum'] ?? $check_sum;
                    $new_groups_check_sum = $response['raw_group_records']['groups_check_sum'] ?? $groups_check_sum;

                    update_post_meta( $post_id, 'zume_check_sum', $new_check_sum, $new_check_sum );
                    update_post_meta( $post_id, 'zume_groups_check_sum', $new_groups_check_sum, $new_groups_check_sum );
                    update_post_meta( $post_id, 'zume_raw_record', $response['raw_record'] );
                    update_post_meta( $post_id, 'zume_groups_raw_record', $response['raw_group_records'] );
                    update_post_meta( $post_id, 'zume_last_check', current_time( 'mysql' ) );

                    // check for group changes
                    if ( 'contact' === $type && isset( $response['raw_group_records'] ) && ! empty( $response['raw_group_records'] ) ) {
                        // get lists to compare
                        $zume_groups = maybe_unserialize( $response['raw_group_records'] );
                        $list = self::get_zume_foreign_keys(); // all zume foreign keys
                        foreach ( $zume_groups as $key => $group ) {
                            if ( ! isset( $group['foreign_key'] ) ) {
                                continue;
                            }

                            // if group does not exist, create group and add contact to it
                            if ( array_search( $group['foreign_key'], $list ) === false ) {
                                if ( '1' == $group['foreign_key'] ) {
                                    continue;
                                }

                                $group_id = self::insert_group_record( $group, $post_id );
                                if( is_wp_error( $group_id ) ) {
                                    wp_insert_comment([
                                        'comment_post_ID' => $post_id,
                                        'comment_content' => __( 'Tried to add group', 'disciple_tools' ) . ": ". $group['group_name'],
                                        'comment_type' => '',
                                        'comment_parent' => 0,
                                        'user_id' => 0,
                                        'comment_date' => current_time( 'mysql' ),
                                        'comment_approved' => 1,
                                    ]);
                                }

                            // if group exists, test if contact is connected to group
                            } else {
                                if ( $existing_group_id = self::contact_needs_connected_to_group( $group['foreign_key'], $post_id ) ) {
//                                    dt_write_log($existing_group_id);
                                    Disciple_Tools_Groups::add_member_to_group( $existing_group_id, $post_id );
                                }
                            }
                        }
                    }
                } else {
                    // error
                    dt_write_log( 'Contact update error.' );
                    dt_write_log( $response );
                }
            } else {
                // error
                dt_write_log( $response );
                return false;
            }
        }

        return true;
    }

    public static function insert_group_record( $group, $contact_id ) {
        $core_endpoint_object = new DT_Zume_Core_Endpoints();

        $fields = $core_endpoint_object->build_group_record_array( $group, $contact_id );

        $new_group_id = Disciple_Tools_Groups::create_group( $fields, false );

        if ( is_wp_error( $new_group_id ) ) {
            return new WP_Error( __METHOD__, 'Failed to create group.' );
        }

        add_post_meta( $new_group_id, 'zume_foreign_key', $group['foreign_key'], true );
        add_post_meta( $new_group_id, 'zume_raw_record', $group, true );
        add_post_meta( $new_group_id, 'zume_check_sum', $group['zume_check_sum'], true );
        add_post_meta( $new_group_id, 'member_count', $group['members'], true );

        wp_insert_comment([
            'comment_post_ID' => $contact_id,
            'comment_content' => __( 'Contact was connected to group', 'disciple_tools' ) . ': ' . $group['group_name'],
            'comment_type' => '',
            'comment_parent' => 0,
            'user_id' => 0,
            'comment_date' => current_time( 'mysql' ),
            'comment_approved' => 1,
        ]);

        return $new_group_id;
    }

    public static function contact_needs_connected_to_group( $foreign_key, $contact_id ) {
        global $wpdb;
        $group_post_id = $wpdb->get_var( $wpdb->prepare( "
                SELECT post_id
                FROM $wpdb->postmeta
                WHERE meta_value = %s
                AND meta_key = 'zume_foreign_key'
                ",
            $foreign_key
            ));

        $is_connected = $wpdb->get_var( $wpdb->prepare( "SELECT p2p_id FROM $wpdb->p2p WHERE p2p_from = %s AND p2p_to = %s", $contact_id, $group_post_id ) );

        if ( $is_connected ) {
            return false;
        } else {
            return $group_post_id;
        }
    }

    public static function get_zume_foreign_keys() {
        global $wpdb;
        return $wpdb->get_col("SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = 'zume_foreign_key'");
    }

    /**
     * @return array  Returns array if success, empty array on fail
     */
    public static function get_project_stats(): array {

        $raw_record = get_option( 'zume_stats_raw_record' );
        if ( ! empty( $raw_record ) && isset( $raw_record['timestamp'] ) && ! empty( $raw_record['timestamp'] ) ) { // check if already checked today
            if ( ! ( date( 'Ymd' ) > date( 'Ymd', strtotime( $raw_record['timestamp'] ) ) ) ) {
                return $raw_record;
            }
        }

        $check_sum = $raw_record['zume_stats_check_sum'] ?: md5( 'no_check_sum' );

        $site = self::get_site_details( get_option( 'zume_default_site' ) );
        if ( is_wp_error( $site ) ) {
            dt_write_log( $site );
            return [];
        }

        // Send remote request
        $args = [
            'method' => 'POST',
            'body' => [
                'transfer_token' => $site['transfer_token'],
                'zume_stats_check_sum' => $check_sum,
            ]
        ];
        $result = self::remote_send( 'get_project_stats', $site['url'], $args );
        if ( is_wp_error( $result ) || is_wp_error( $result['body'] ) ) {
            dt_write_log( $result );
            return get_option( 'zume_stats_raw_record', [] );
        }
        if ( isset( $result['body'] ) ) {
            $response = json_decode( $result['body'], true );

            if ( isset( $response['status'] ) ) {
                if ( $response['status'] == 'OK' ) {
                    // updated timestamp of the raw record
                    $raw_record = get_option( 'zume_stats_raw_record' );
                    $raw_record['timestamp'] = current_time( 'mysql' );
                    update_option( 'zume_stats_raw_record', $raw_record );

                    return get_option( 'zume_stats_raw_record', [] );

                } elseif ( $response['status'] == 'Update_Needed' && isset( $response['raw_record'] ) ) {
                    update_option( 'zume_stats_raw_record', $response['raw_record'] );
                    return get_option( 'zume_stats_raw_record', [] );

                } else {
                    // error
                    dt_write_log( 'RESPONSE STATUS ERROR' );
                    dt_write_log( $response );

                    return [];
                }
            } else {
                // error
                dt_write_log( 'RESPONSE STATUS NOT SET' );
                dt_write_log( $response );

                return [];
            }
        } else {
            dt_write_log( 'RESPONSE BODY ERROR' );
            dt_write_log( $result );

            return [];
        }
    }

    public static function test_last_check( $post_id ) : bool {
        $timestamp = get_post_meta( $post_id, 'zume_last_check', true );
        if ( date( 'Ymd' ) > date( 'Ymd', strtotime( $timestamp ) ) ) {
            return false;
        }
        return true;
    }

    public static function test_zume_global_stats_needs_update() : bool {
        $raw_record = get_option( 'zume_stats_raw_record' );
        if ( empty( $raw_record ) ) {
            return true;
        }
        if ( date( 'Ymd' ) > date( 'Ymd', strtotime( $raw_record['timestamp'] ) ) ) {
            return true;
        }
        return false;
    }

    /**
     * Get the token and url of the site
     *
     * @param $site_key
     *
     * @return array|WP_Error
     */
    public static function get_site_details( $site_key ) {
        $keys = Site_Link_System::get_site_keys();

        $site1 = $keys[$site_key]['site1'];
        $site2 = $keys[$site_key]['site2'];

        $url = Site_Link_System::get_non_local_site( $site1, $site2 );
        $transfer_token = Site_Link_System::create_transfer_token_for_site( $site_key );

        return [
            'url' => $url,
            'transfer_token' => $transfer_token
        ];
    }

    /**
     * Send Post Request
     * $args = [
     *  'method' => 'POST',
     *   'body' => [
     *   'transfer_token' => $site['transfer_token'],
     *   'transfer_record' => $fields,
     *   'zume_foreign_key' => $user_data['zume_foreign_key'],
     *   'zume_language' => $user_data['zume_language'],
     *   'zume_check_sum' => $user_data['zume_check_sum'],
     * ]
     * ];
     *
     * @param $endpoint
     * @param $url
     * @param $args
     *
     * @return array|\WP_Error
     */
    public static function remote_send( $endpoint, $url, $args ) {

        $result = wp_remote_post( 'https://' . $url . '/wp-json/dt-public/v1/zume/' . $endpoint, $args );

        if ( is_wp_error( $result ) ) {
            return new WP_Error( 'failed_remote_get', $result->get_error_message() );
        }
        return $result;
    }

}