<?php


function dt_zume_format_connection_message( $message, $activity, $fields ) {
    // (maybe) modify $string // @todo add modification
    return $message;
}
add_filter( 'dt_format_connection_message', 'dt_zume_format_connection_message', 10, 2 );