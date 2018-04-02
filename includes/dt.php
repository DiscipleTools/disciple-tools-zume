<?php

class DT_Zume_DT
{
    public static function create_contact_by_zume_foreign_key( $zume_foreign_key ) {
        // Get target site for transfer
        $site_key = get_option( 'zume_default_site' );
        if ( ! $site_key ) {
            return new WP_Error( __METHOD__, 'No site setup' );
        }
        $site = dt_zume_get_site_details( $site_key );

        // Send remote request
        $args = [
            'method' => 'POST',
            'body' => [
                'transfer_token' => $site['transfer_token'],
                'zume_foreign_key' => $zume_foreign_key,
            ]
        ];
        $result = dt_zume_remote_send( 'get_contact_by_foreign_key', $site['url'], $args );

        if ( ! $result['response']['code'] == '200') {
            return new WP_Error( __METHOD__, 'Failed response from Zume server' );
        }

        if ( ! isset( $result['body'] ) || empty( $result['body'] ) ) {
            return new WP_Error( __METHOD__, 'Mailformed or empty remote server response' );
        }

        $response = json_decode( $result['body'], true );
        dt_write_log( $response );

        $post_id = Disciple_Tools_Contacts::create_contact( $response['transfer_record'], false );

        if ( is_wp_error( $post_id ) || empty( $post_id ) ) {
            return new WP_Error( 'failed_insert', 'Failed record creation' );
        }

        add_post_meta( $post_id, 'zume_raw_record', $response['raw_record'], true );
        add_post_meta( $post_id, 'zume_foreign_key', $response['raw_record']['zume_foreign_key'], true );

        return $post_id;
    }

    public static function check_user_record_update( $contact_id ) {
        dt_write_log( __METHOD__ );
        dt_write_log( $contact_id );

        // check if already checked today
        if ( self::test_last_check( $contact_id ) ) {
            return true;
        }

        $check_sum = get_post_meta( $contact_id, 'zume_check_sum', true );

        $foreign_key = get_post_meta( $contact_id, 'zume_foreign_key', true );

        $site = self::get_site_details( get_option('zume_default_site') );

        // Send remote request
        $args = [
        'method' => 'POST',
        'body' => [
            'transfer_token' => $site['transfer_token'],
            'zume_check_sum' => $check_sum,
            'zume_foreign_key' => $foreign_key,
            ]
        ];
        $result = self::remote_send( 'check_user_record_update', $site['url'], $args );
        if ( isset( $result['body'] ) ) {
            $response = json_decode( $result['body'], true );
            if ( isset( $response['status'] ) ) {
                if ( $response['status'] == 'OK' ) {
                    // no update needed
                    dt_write_log( $response['status'] );
                    update_post_meta( $contact_id, 'zume_last_check', current_time('mysql' ) );
                } elseif ( $response['status'] == 'Update_Needed' && isset( $response['raw_record'] ) ) {
                    // updated needed
                    $new_check_sum = $response['raw_record']['zume_check_sum'] ?? $check_sum;

                    update_post_meta( $contact_id, 'zume_check_sum', $new_check_sum );
                    update_post_meta( $contact_id, 'zume_raw_record', $response['raw_record'] );
                    update_post_meta( $contact_id, 'zume_last_check', current_time('mysql' ) );
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
        if ( date('Ymd') > date('Ymd', strtotime($timestamp) ) ) {
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


