<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * NOTE:
 * The menu and script load filter and action are located in /includes/hooks.php DT_Zume_Hooks_Metrics
 */

/**
 * Class DT_Zume_Metrics
 */
class DT_Zume_Metrics
{
    private static $_instance = null;

    public static function instance()
    {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct()
    {
    }

    public static function get_zume_project_stats() {
        return [
            'site' => [
                'total_users' => 3000,
                'total_groups' => 400,
                'session_progress_groups' => [
                    'session_1' => 500,
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
            ],
            'global' => [
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
            ]
        ];
    } // @todo remove

    public static function get_zume_project_stats_cached(): array { // @todo remove
        // Check for transient cache first for speed
        $current = get_transient( 'zume_project_stats' );

        if ( ! empty( $current ) && isset( $current['timestamp'] ) ) {
            $expired_data = date( 'Ymd' ) > date( 'Ymd', strtotime( $current['timestamp'] ) ) ? true : false;
            if ( ! $expired_data ) {
                return $current;
            }
        }

        $current = self::get_zume_project_stats();
        if ( is_wp_error( $current ) ) {
            return $current;
        }
        $current['check_sum'] = md5( maybe_serialize( $current ) );
        $current['timestamp'] = current_time( 'mysql' ); // add timestamp so that we can publish age of the data
        set_transient( 'zume_project_stats', $current, 24 * HOUR_IN_SECONDS ); // transient is set to update every 24 hours.

        return $current;
    } // @todo remove

    public static function zume_pipeline_data()
    {
        $stats = self::get_zume_project_stats();

        if ( !isset( $stats['site']['session_progress_groups'] ) || !isset( $stats['global']['session_progress_groups'] ) || empty( $stats['site']['session_progress_groups'] || $stats['global']['session_progress_groups'] ) ) {
            return new WP_Error( __FUNCTION__, __( "Incomplete groups progress data" ), [ 'status' => 403 ] );
        }

        $report_site = [
            [
                'Sessions Completed by Groups',
        'Groups',
        [ 'role' => 'annotation' ]
            ]
        ];
        foreach ( $stats['site']['session_progress_groups'] as $key => $value ) {
            $report_site[] = [ ucwords( str_replace( '_', ' ', $key ) ), (int) $value, (int) $value ];
        }

        $report_global = [
            [
                'Sessions Completed by Groups',
        'Groups',
        [ 'role' => 'annotation' ]
            ]
        ];
        foreach ( $stats['global']['session_progress_groups'] as $key => $value ) {
            $report_global[] = [ ucwords( str_replace( '_', ' ', $key ) ), (int) $value, (int) $value ];
        }

        if ( !empty( $report_site ) && !empty( $report_global ) ) {
            return [
            'status' => true,
            'data'   => [
                'chart_site' => $report_site,
                'chart_global' => $report_global,
                'timestamp' => current_time( 'mysql' ),
                ]
            ];
        } else {
            return [
            'status'  => false,
            'message' => 'Failed to build critical path data.',
            ];
        }
    } // @todo remove

    public static function zume_groups_coordinates() {
        DT_Zume_Core::get_project_stats(); // @todo remove

        $raw_stats = get_option( 'zume_stats_raw_record' );
        $raw_stats = maybe_unserialize( $raw_stats );



        if ( !empty( $raw_stats['global']['group_coordinates'] ) ) {
//            array_unshift($raw_stats['global']['group_coordinates'], ['number', 'number'] );
            return [
                'status' => true,
                'data'   => [
                    'coordinates' => $raw_stats['global']['group_coordinates'],
                    'timestamp' => current_time( 'mysql' ),
                ]
            ];
        } else {
            return [
                'status'  => false,
                'message' => 'No data.',
            ];
        }
    } // @todo remove

}

DT_Zume_Metrics::instance();