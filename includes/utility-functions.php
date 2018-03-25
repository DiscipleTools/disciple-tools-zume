<?php
/**
 * Misc utility functions
 */

function dt_zume_is_this_zumeproject() : bool {
    $current_theme = get_option( 'current_theme' );
    if ( 'Zúme Project' == $current_theme ) {
        return true;
    }
    return false;
}

function dt_zume_is_this_disciple_tools() : bool {
    $current_theme = get_option( 'current_theme' );
    if ( 'Disciple Tools' == $current_theme ) {
        return true;
    }
    return false;
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
function dt_zume_remote_send( $endpoint, $url, $args ) {

    $result = wp_remote_post( 'https://' . $url . '/wp-json/dt-public/v1/zume/' . $endpoint, $args );

    if ( is_wp_error( $result ) ) {
        return new WP_Error( 'failed_remote_get', $result->get_error_message() );
    }
    return $result;
}
