<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

class DT_Zume_Hook_User extends DT_Zume_Hook_Base {

    public function hooks_user_register( $user_id ) {
        $user = get_user_by( 'id', $user_id );

        $args = [];

        // Load and launch async insert
        try {
            $insert_location = new DT_Zume_Send_New_User();
            $insert_location->launch( $args );
        } catch ( Exception $e ) {
            dt_write_log( new WP_Error( 'async_insert_error', 'Failed to launch async insert process' ) );
        }

    }

    public function hooks_profile_update( $user_id ) {
        // send updated information with new contact information

        $user = get_user_by( 'id', $user_id );

        dt_write_log( [
            'task' => 'New User Registered',
            'user_id' => $user->ID,
            'user_object' => $user,
            'user_meta' => dt_zume_get_user_meta( $user_id ),
        ]);
    }

    public function hooks_wp_login( $user_login, $user ) {
        // send an activity log that they signed into their Zume account
            $check_permission = false;

            $new_lead_meta = dt_zume_get_user_meta( $user->ID );

            // Build extra field data
            $notes = [
                'zume_user_id' => $user->ID,
            ];


            // Get best name
            if ( ! empty( $new_lead_meta['first_name'] ) && ! empty( $new_lead_meta['last_name'] ) ) {
                $title = $new_lead_meta['first_name'] . ' ' . $new_lead_meta['last_name'];
            } elseif ( ! empty( $new_lead_meta['first_name'] ) ) {
                $title = $new_lead_meta['first_name'];
            } else {
                $title = $user->display_name;
            }

            $phone = $new_lead_meta['zume_phone_number'] ?? '';
            $address = $new_lead_meta['zume_user_address'] ?? $new_lead_meta['zume_address_from_ip'] ?? '';

            // Build record data
            $fields = [
                'title' => $title,
                "contact_phone" => [
                    [ "value" => $phone ], //create
                ],
                "contact_email" => [
                    [ "value" => $user->user_email ], //create
                ],
                "contact_address" => [
                    [ "value" => $address ]
                ],
                'notes' => $notes
            ];

            // filter for preferred target site, depending on qualifications
            $keys = DT_Site_Link_System::get_site_keys();

            // parse site site link info
            foreach ( $keys as $key => $value ) {
                $url = DT_Site_Link_System::get_non_local_site( $value['site1'], $value['site2'] );
                $transfer_token = DT_Site_Link_System::create_transfer_token_for_site( $key );
                break;
            }


            $transfer_records = [];
            $transfer_records[] = $fields;

            // Send remote request
            $args = [
                'method' => 'POST',
                'body' => [
                    'transfer_token' => $transfer_token,
                    'transfer_records' => $transfer_records,
                ]
            ];
            $result = wp_remote_post( 'https://' . $url . '/wp-json/dt-public/v1/zume/create_new_contacts', $args );
            if ( is_wp_error( $result ) ) {
                dt_write_log( $result );
                return new WP_Error( 'failed_remote_get', $result->get_error_message() );
            }
            dt_write_log( $result );

    }

    public function __construct() {
        add_action( 'user_register', [ &$this, 'hooks_user_register' ], 99, 1 );
        add_action( 'profile_update', [ &$this, 'hooks_profile_update' ] );
        add_action( 'wp_login', [ &$this, 'hooks_wp_login' ], 10, 2 );

        parent::__construct();
    }

}
