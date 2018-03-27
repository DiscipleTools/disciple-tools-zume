<?php
/**
 * Prepare Zume Data to Send
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class DT_Zume_Zume
 */
class DT_Zume_Zume
{

    public function send_new_contact( $user_id ) {

        // Get prepared data for user
        $user_data = $this->get_transfer_user_array( $user_id );

        // Get target site for transfer
        $site_key = dt_zume_filter_for_site_key( $user_data );
        if ( ! $site_key ) {
            return; // no sites setup
        }

        $site = dt_zume_get_site_details( $site_key );

        // Build new DT record data
        $fields = $this->build_dt_contact_record_array( $user_data );

        // Send remote request
        $args = [
            'method' => 'POST',
            'body' => [
                'transfer_token' => $site['transfer_token'],
                'transfer_record' => $fields,
                'raw_record' => $user_data,
            ]
        ];
        $result = dt_zume_remote_send( 'create_new_contact', $site['url'], $args );

        dt_write_log( __METHOD__ );
        dt_write_log( $result );
        return;
    }


    public function send_update_contact( $user_id ) {
        dt_write_log( __METHOD__ );

        // Get prepared data for user
        $user_data = $this->get_transfer_user_array( $user_id );

        // Get target site for transfer
        $site_key = dt_zume_filter_for_site_key( $user_data );
        if ( ! $site_key ) {
            return; // no sites setup
        }
        $site = dt_zume_get_site_details( $site_key );

        // Send remote request
        $args = [
            'method' => 'POST',
            'body' => [
                'transfer_token' => $site['transfer_token'],
                'raw_record' => $user_data,
            ]
        ];
        $result = dt_zume_remote_send( 'update_contact', $site['url'], $args );

        dt_write_log( $result );
        return;
    }

    public function send_contact_last_login( $user_id ) {
        dt_write_log( __METHOD__ );

        $zume_foreign_key = $this->get_foreign_key( $user_id );
        $time = current_time('mysql');

        // Get target site for transfer
        $site_key = dt_zume_filter_for_site_key();
        if ( ! $site_key ) {
            return; // no sites setup
        }
        $site = dt_zume_get_site_details( $site_key );

        // Send remote request
        $args = [
            'method' => 'POST',
            'body' => [
                'transfer_token' => $site['transfer_token'],
                'zume_foreign_key' => $zume_foreign_key,
                'last_login' => $time,
            ]
        ];
        $result = dt_zume_remote_send( 'contact_last_login', $site['url'], $args );

        dt_write_log( $result );
        return;
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


    /**
     * Transfer user data
     * This function puts together the complete user data package to send to Disciple Tools
     *
     * @param null $user_id
     *
     * @return array
     */
    public function get_transfer_user_array( $user_id = null ) {
        if ( is_null( $user_id ) ) {
            $user_id = get_current_user_id();
        }
        $user = get_user_by( 'id', $user_id );
        $user_meta = dt_zume_get_user_meta( $user->ID );

        $user_meta['first_name'] = $user_meta['first_name'] ?? '';
        $user_meta['last_name'] = $user_meta['last_name'] ?? '';

        $full_name = trim( $user_meta['first_name'] . ' ' . $user_meta['last_name'] );
        if ( empty( $full_name ) ) {
            $full_name = null;
        }

        $three_month_plan = get_user_meta( get_current_user_id(), 'three_month_plan', true );
        if( class_exists( 'Zume_Three_Month_Plan' ) ) {
            $three_month_plan = Zume_Three_Month_Plan::plan_items_filter( $three_month_plan );
        }

        $zume_groups = $this->get_groups_for_user( $user_meta );

        $zume_colead_groups = $this->get_colead_groups_for_user( $user->data->user_email );

        $prepared_user_data = [
            'title' => sanitize_text_field( wp_unslash( ucwords( $full_name ?: $user_meta['nickname'] ?: $user->data->display_name ) ) ),
            'user_login' => $user->data->user_login,
            'first_name' => sanitize_text_field( wp_unslash( $user_meta['first_name'] ?? '' ) ),
            'last_name' => sanitize_text_field( wp_unslash( $user_meta['last_name'] ?? '' ) ),
            'user_registered' => $user->data->user_registered,
            'user_email' => sanitize_email( wp_unslash( $user->data->user_email ) ),
            'zume_language' => maybe_unserialize( $user_meta['zume_language'] ?? zume_current_language() ),
            'zume_phone_number' => sanitize_text_field( wp_unslash( $user_meta['zume_phone_number'] ?? '' ) ),
            'zume_user_address' => sanitize_text_field( wp_unslash( $user_meta['zume_user_address'] ?? '' ) ),
            'zume_address_from_ip' => $user_meta['zume_address_from_ip'] ?? '',
            'zume_foreign_key' => $user_meta['zume_foreign_key'] ?? self::get_foreign_key( $user_id ),
            'zume_three_month_plan' => $three_month_plan ?: [],
            'zume_groups' => $zume_groups,
            'zume_colead_groups' => $zume_colead_groups,
        ];

        update_user_meta( $user_id, 'zume_check_sum', md5( serialize( $prepared_user_data ) ) );
        $prepared_user_data['zume_check_sum'] = md5( serialize( $prepared_user_data ) );

        return $prepared_user_data;
    }

    public function get_transfer_group_array( $zume_group_key, $owner_id = null ) {
        dt_write_log( __METHOD__ );

        if ( is_null( $owner_id ) ) {
            global $wpdb;
            $zume_group_meta = $wpdb->get_var( $wpdb->prepare( "
                SELECT meta_value FROM $wpdb->usermeta WHERE meta_key = %s
            ",
            $zume_group_key
            ));

            if ( ! $zume_group_meta ) {
                return;
            }

        }


    }

    public function get_groups_for_user( $user_meta ) : array {
        $groups = [];
        foreach ( $user_meta as $zume_key => $v ) {
            $zume_key_beginning = substr( $zume_key, 0, 10 );
            if ( 'zume_group' == $zume_key_beginning ) {
                $groups[] = $zume_key;
            }
        }
        return $groups;
    }

    /**
     * @see Zume_Dashboard::get_colead_groups()
     */
    public function get_colead_groups_for_user( $user_email ) : array {
        global $wpdb;
        $groups = [];
        $results = $wpdb->get_results($wpdb->prepare(
        "SELECT *
                        FROM `$wpdb->usermeta`
                        WHERE meta_key LIKE %s
                          AND meta_value LIKE %s",
        $wpdb->esc_like( 'zume_group_' ).'%',
        '%'. $wpdb->esc_like( $user_email ). '%'
        ), ARRAY_A );
        if ( empty( $results ) ) {
            return $groups;
        }

        foreach ( $results as $v ){
            $zume_key = $v['meta_key'];
            $zume_value = maybe_unserialize( $v['meta_value'] );
            $zume_value = Zume_Dashboard::verify_group_array_filter( $zume_value );

            if ( in_array( $user_email, $zume_value['coleaders'] ) && // is added as coleader
            in_array( $user_email, $zume_value['coleaders_accepted'] ) && // is accepted
            ! in_array( $user_email, $zume_value['coleaders_declined'] ) ) // not declined
            {
                $zume_value['no_edit'] = true; // tags record as no owned
                $prepared[$zume_key] = $zume_value;
            }
        }

        return $groups;
    }

    public static function get_foreign_key( $user_id ) {
        $key = get_user_meta( $user_id, 'zume_foreign_key', true );
        if ( empty( $key ) ) {
            $key = DT_Site_Link_System::generate_token( 40 ); // forty bits equals 1.1 trillion combinations
            update_user_meta( $user_id, 'zume_foreign_key', $key );
        }
        return $key;
    }

    public static function get_user_by_foreign_key( $zume_foreign_key ) {
        global $wpdb;
        $user_id = $wpdb->get_var( $wpdb->prepare( "
            SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'zume_foreign_key' AND meta_value = %s
        ",
        $zume_foreign_key
        ) );

        return $user_id;
    }

    /**
     * Goes through database and adds foreign key to any users missing
     * Called from the Zume Settings page. Used during database installation
     */
    // @todo VIP coding standard is flagging this sql query saying "Usage of users/usermeta tables is highly discouraged in VIP context, For storing user additional user metadata, you should look at User Attributes."
    // @codingStandardsIgnoreStart
    public function verify_check_sum_installed() {
        global $wpdb;
        $results = $wpdb->get_col( "SELECT ID FROM $wpdb->users WHERE id NOT IN ( SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'zume_check_sum' )" );

        $i = 0;
        if ( ! empty( $results ) ) {
            foreach ( $results as $user_id ) {
                $this->get_transfer_user_array( $user_id );
                $i++;
            }
            dt_write_log( 'Updated: ' . $i );
            return $i;
        } else {
            return $i;
        }
    }

    /**
     * Goes through database and adds foreign key to any users missing
     */
    public function verify_foreign_key_installed() {
        global $wpdb;
        $results = $wpdb->get_col( "SELECT ID FROM $wpdb->users WHERE id NOT IN ( SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'zume_foreign_key' )" );

        $i = 0;
        if ( ! empty( $results ) ) {
            foreach ( $results as $user_id ) {
                $key = DT_Site_Link_System::generate_token( 40 ); // forty bits equals 1.1 trillion combinations
                update_user_meta( $user_id, 'zume_foreign_key', $key );
                $i++;
            }
            dt_write_log( 'Updated: ' . $i );
            return $i;
        } else {
            return $i;
        }
    }
    // @codingStandardsIgnoreEnd

}

