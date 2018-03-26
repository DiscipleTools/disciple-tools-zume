<?php

class DT_Zume_DT
{
    public static function create_contact_by_zume_foreign_key( $zume_foreign_key ) {
        // Get target site for transfer
        $site_key = get_option( 'zume_default_site' );
        if ( ! $site_key ) {
            return new WP_Error(__METHOD__, 'No site setup' );
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
            return new WP_Error(__METHOD__, 'Failed response from Zume server' );
        }

        if ( ! isset( $result['body'] ) || empty( $result['body'] ) ) {
            return new WP_Error(__METHOD__, 'Mailformed or empty remote server response' );
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
}