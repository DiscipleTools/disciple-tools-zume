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

    public static function get_zume_project_stats() {
        return [
            'total_users' => 3000,
            'total_groups' => 400,
            'session_progress_groups' => [
                'session_1' => 1000,
                'session_2' => 500,
                'session_3' => 400,
                'session_4' => 300,
                'session_5' => 200,
                'session_6' => 100,
                'session_7' => 90,
                'session_8' => 70,
                'session_9' => 40,
                'session_10' => 30,
            ],
            'session_progress_users' => [
                'session_1' => 1000,
                'session_2' => 100,
                'session_3' => 100,
                'session_4' => 100,
                'session_5' => 100,
                'session_6' => 100,
                'session_7' => 100,
                'session_8' => 100,
                'session_9' => 100,
                'session_10' => 100,
            ],
            'members_per_group' => [
                '1' => 200,
                '2' => 200,
                '3' => 20,
                '4' => 20,
                '5' => 20,
                '6' => 100,
                '7' => 20,
                '8' => 20,
                '9' => 20,
                '10' => 10,
            ],
            'logins' => [
                'last_day' => 5,
                'last_week' => 200,
                'last_month' => 1200,
                'last_3_months' => 5000,
            ],
            'top_cities' => [
                0 => 'Denver, CO',
                1 => 'Lexington, KY',
                2 => 'Nampa, FL',
                3 => 'Los Angelos, CA',
                4 => 'Lexington, KY',
            ],
            'top_countries' => [
                0 => [
                    'name' => 'U.S.A.',
                    'count' => 400,
                ],
                1 => [
                    'name' => 'U.S.A.',
                    'count' => 100,
                ],
                2 => [
                    'name' => 'U.S.A.',
                    'count' => 50,
                ],
                3 => [
                    'name' => 'U.S.A.',
                    'count' => 40,
                ],
                4 => [
                    'name' => 'U.S.A.',
                    'count' => 10,
                ],
            ],
            'top_languages' => [
                0 => [
                    'name' => 'English',
                    'count' => 3000,
                ],
                1 => [
                    'name' => 'Farsi',
                    'count' => 500,
                ],
                2 => [
                    'name' => 'Arabic',
                    'count' => 300,
                ],
                3 => [
                    'name' => 'French',
                    'count' => 200,
                ],
                4 => [
                    'name' => 'Spanish',
                    'count' => 100,
                ],

            ],
            'group_locations' => [
                0 => '39.550007, -105.988124',
                1 => '33.550007, -104.988124',
                2 => '34.550007, -105.988124',
                3 => '35.550007, -104.988124',
                4 => '39.550007, -105.988124',
                5 => '36.550007, -104.988124',
                6 => '39.550007, -105.988124',
                7 => '35.550007, -105.988124',
                8 => '31.550007, -104.988124',
                9 => '38.550007, -105.988124',
                10 => '39.550007, -104.988124',
                11 => '34.550007, -105.988124',
                12 => '35.550007, -104.988124',
                13 => '34.550007, -105.988124',
                14 => '34.550007, -105.988124',
                15 => '33.550007, -104.988124',
                16 => '32.550007, -102.988124',
            ]
        ];
    }

    /**
     * Bundles the basic critical path numbers in a google chart format
     *
     * @param $check_permissions
     *
     * @return array|\WP_Error
     */
    public static function zume_pipeline_data( $check_permissions )
    {

        $current_user = get_current_user();
        if ( $check_permissions && !self::can_view( 'zume_pipeline', $current_user ) ) {
            return new WP_Error( __FUNCTION__, __( "No permissions to read report" ), [ 'status' => 403 ] );
        }


        $stats = self::get_zume_project_stats();
        if ( !isset( $stats['session_progress_groups'] ) || empty( $stats['session_progress_groups'] ) ) {
            return new WP_Error( __FUNCTION__, __( "No groups progress data" ), [ 'status' => 403 ] );
        }

        $report = [
        [ 'Sessions Completed by Groups', 'Groups', [ 'role' => 'annotation' ] ]
        ];

        foreach( $stats['session_progress_groups'] as $key => $value ) {
            $report[] = [ ucwords( str_replace( '_', ' ', $key ) ), (int) $value, (int) $value ];
        }

        if ( !empty( $report ) ) {
            return [
            'status' => true,
            'data'   => [
                'chart' => $report,
                'timestamp' => current_time('mysql'),
                ]
            ];
        } else {
            return [
            'status'  => false,
            'message' => 'Failed to build critical path data.',
            ];
        }
    }

    public static function get_zume_pipeline(): array {
        // Check for transient cache first for speed
        $current = get_transient( 'dt_critical_path' );
        if ( empty( $current ) ) {
            $current = Disciple_Tools_Counter::critical_path();
            if ( is_wp_error( $current ) ) {
                return $current;
            }
            $current['timestamp'] = current_time( 'mysql' ); // add timestamp so that we can publish age of the data
            set_transient( 'dt_critical_path', $current, 6 * HOUR_IN_SECONDS ); // transient is set to update every 6 hours. Average work day.
        }
        return $current;
    }

    public static function can_view( $report_name, $user_id )
    {
        // TODO decide on permission strategy for reporting
        // Do we hardwire permissions to reports to the roles of a person?
        // Do we set up a permission assignment tool in the config area, so that a group could assign reports to a role
        if ( empty( $user_id ) ) {
            $user_id = get_current_user_id();
        }
        if ( ! $user_id ) {
            return false;
        }

        switch ( $report_name ) {
            case 'zume_pipeline':
                return true;
                break;
            default:
                return true; // TODO temporary true response returned until better permissions check is created
                break;
        }
    }
}


