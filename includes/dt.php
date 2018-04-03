<?php

class DT_Zume_DT
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

    public static function test_last_check( $post_id ) : bool {
        $timestamp = get_post_meta( $post_id, 'zume_last_check', true );
        if ( date( 'Ymd' ) > date( 'Ymd', strtotime( $timestamp ) ) ) {
            return false;
        }
        return true;
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


