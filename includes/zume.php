<?php
/**
 * Prepare Zume Data to Send
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}


class DT_Zume_Zume
{
    public static function temp_call() {
        $object = new DT_Zume_Zume();

        $object->get_transfer_user_data();

    }

    public function send_user_data( $user_id ) {

        // get prepared data for user
        $user_data = $this->get_transfer_user_data( $user_id );

        $language = $user_data['zume_language'];
        if ( function_exists( 'pll_the_languages' ) ) {
            $language = pll_current_language( 'name' );
        }

        // Build extra field data
        $notes = [
            'user_registered' => $user_data['user_registered'],
            'zume_language' => $language,
        ];

        $phone = $user_data['zume_phone_number'] ?? '';
        $address = $user_data['zume_user_address'] ?: $user_data['zume_address_from_ip'] ?: '';

        // Build record data
        $fields = [
        'title' => $user_data['title'],
        "contact_phone" => [
            [ "value" => $phone ], //create
        ],
        "contact_email" => [
            [ "value" => $user_data['user_email'] ], //create
        ],
        "contact_address" => [
            [ "value" => $address ]
        ],
            'notes' => $notes
        ];

        $site_key = $this->filter_for_site_key( $user_data );
        if ( ! $site_key ) {
            return false; // no sites setup
        }

        $site = $this->get_site_details( $site_key );

        // Send remote request
        $args = [
            'method' => 'POST',
            'body' => [
                'transfer_token' => $site['transfer_token'],
                'transfer_record' => $fields,
                'zume_foreign_key' => $user_data['zume_foreign_key'],
                'zume_language' => $user_data['zume_language'],
                'zume_check_sum' => $user_data['zume_check_sum'],
            ]
        ];

        $result = wp_remote_post( 'https://' . $site['url'] . '/wp-json/dt-public/v1/zume/create_new_contacts', $args );

        if ( is_wp_error( $result ) ) {
            dt_write_log( $result );
            return new WP_Error( 'failed_remote_get', $result->get_error_message() );
        }

        dt_write_log( $result );
        return true;
    }

    /**
     * @param $user_data
     *
     * @return bool|int|string
     */
    public function filter_for_site_key( $user_data ) {

        // @TODO Potentially add routing logic.
        // Evaluate routing factors of the user_data to route the user to a certain site.
        // Is language set, then potentially route to language DT site
        // Is location set, then potentially route to location site

        // Get site keys
        $keys = DT_Site_Link_System::get_site_keys();
        if ( empty( $keys ) ) {
            return false;
        }

        $key = get_option( 'zume_default_site' );
        if ( ! $key ) {
            foreach ( $keys as $key => $value ) {
                update_option( 'zume_site_default', $key );
                break;
            }
        }
        return $key;
    }

    /**
     * Get the token and url of the site
     *
     * @param $site_key
     *
     * @return array
     */
    public function get_site_details( $site_key ) {
        $keys = DT_Site_Link_System::get_site_keys();

        $site1 = $keys[$site_key]['site1'];
        $site2 = $keys[$site_key]['site2'];

        $url = DT_Site_Link_System::get_non_local_site( $site1, $site2 );
        $transfer_token = DT_Site_Link_System::create_transfer_token_for_site( $site_key );

        return [
            'url' => $url,
            'transfer_token' => $transfer_token
        ];
    }


    public function get_transfer_user_data( $user_id = null ) {
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

        $prepared_user_data = [
            'title' => $full_name ?: $user_meta['nickname'] ?: $user->data->display_name,
            'user_login' => $user->data->user_login,
            'first_name' => $user_meta['first_name'] ?? '',
            'last_name' => $user_meta['last_name'] ?? '',
            'user_registered' => $user->data->user_registered,
            'user_email' => $user->data->user_email,
            'zume_language' => $user_meta['zume_language'] ?? zume_current_language(),
            'zume_phone_number' => $user_meta['zume_phone_number'] ?? '',
            'zume_user_address' => $user_meta['zume_user_address'] ?? '',
            'zume_address_from_ip' => $user_meta['zume_address_from_ip'] ?? '',
            'zume_foreign_key' => $user_meta['zume_foreign_key'] ?? self::get_foreign_key( $user_id ),
        ];

        update_user_meta( $user_id, 'zume_check_sum', md5( serialize( $prepared_user_data ) ) );
        $prepared_user_data['zume_check_sum'] = md5( serialize( $prepared_user_data ) );

        return $prepared_user_data;
    }

    /**
     * Goes through database and adds foreign key to any users missing
     */
    // @todo VIP coding standard is flagging this sql query saying "Usage of users/usermeta tables is highly discouraged in VIP context, For storing user additional user metadata, you should look at User Attributes."
    // @codingStandardsIgnoreStart
    public function verify_check_sum_installed() {
        global $wpdb;
        $results = $wpdb->get_col( "SELECT ID FROM $wpdb->users WHERE id NOT IN ( SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'zume_check_sum' )" );

        $i = 0;
        if ( ! empty( $results ) ) {
            foreach ( $results as $user_id ) {
                $this->get_transfer_user_data( $user_id );
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
                $key = DT_Site_Link_System::generate_token( 16 );
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

    public static function get_foreign_key( $user_id ) {
        $key = get_user_meta( $user_id, 'zume_foreign_key' );
        if ( empty( $current_key ) ) {
            $key = DT_Site_Link_System::generate_token( 16 );
            update_user_meta( $user_id, 'zume_foreign_key', $key );
        }
        return $key;
    }

}