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

        $foreign_key = get_post_meta( $post_id, 'zume_foreign_key', true );

        $site = self::get_site_details( get_option( 'zume_default_site' ) );

        // Send remote request
        $args = [
        'method' => 'POST',
        'body' => [
            'transfer_token' => $site['transfer_token'],
            'zume_check_sum' => $check_sum,
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
            if ( isset( $response['status'] ) ) {
                if ( $response['status'] == 'OK' ) {
                    // no update needed
                    update_post_meta( $post_id, 'zume_last_check', current_time( 'mysql' ) );
                } elseif ( $response['status'] == 'Update_Needed' && isset( $response['raw_record'] ) ) {
                    // updated needed
                    $new_check_sum = $response['raw_record']['zume_check_sum'] ?? $check_sum;

                    update_post_meta( $post_id, 'zume_check_sum', $new_check_sum );
                    update_post_meta( $post_id, 'zume_raw_record', $response['raw_record'] );
                    update_post_meta( $post_id, 'zume_last_check', current_time( 'mysql' ) );
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

        // Send remote request
        $args = [
            'method' => 'POST',
            'body' => [
                'transfer_token' => $site['transfer_token'],
                'zume_stats_check_sum' => $check_sum,
            ]
        ];
        $result = self::remote_send( 'get_project_stats', $site['url'], $args );

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
     * @return array
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