<?php

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
