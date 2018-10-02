<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Class DT_Zume_Hooks
 */
class DT_Zume_Hooks
{

    private static $_instance = null;

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    /**
     * Build hook classes
     */
    public function __construct() {
        new DT_Zume_Hooks_User();
        new DT_Zume_Hooks_Groups();
        new DT_Zume_Hooks_Training();

        add_filter( 'site_link_type', [ $this, 'site_link_type' ], 10, 1 );
        add_filter( 'site_link_type_capabilities', [ $this, 'site_link_capabilities' ], 10, 2 );
    }

    public function site_link_type( $type ) {
        $type['zume'] = __( 'Zúme' );
        return $type;
    }

    // Add the specific capabilities needed for the site to site linking.
    public function site_link_capabilities( $connection_type, $capabilities ) {
        if ( 'zume' === $connection_type ) {
            $capabilities[] = 'access_contacts';
            $capabilities[] = 'create_contacts';
            $capabilities[] = 'update_shared_contacts';
            $capabilities[] = 'view_any_contacts';
            $capabilities[] = 'assign_any_contacts';
            $capabilities[] = 'update_any_contacts';
            $capabilities[] = 'delete_any_contacts';

            $capabilities[] = 'access_groups';
            $capabilities[] = 'create_groups';
            $capabilities[] = 'view_any_groups';
            $capabilities[] = 'assign_any_groups';
            $capabilities[] = 'update_any_groups';
            $capabilities[] = 'delete_any_groups';

            $capabilities[] = 'read_location';
            $capabilities[] = 'publish_locations';

            $capabilities[] = 'read_peoplegroup';
        }

        return $capabilities;
    }
}


/**
 * Empty class for now..
 * Class DT_Zume_Hook_Base
 */
abstract class DT_Zume_Hooks_Base
{
    public function __construct() {
    }
}

/**
 * Class DT_Zume_Hook_User
 */
class DT_Zume_Hooks_User extends DT_Zume_Hooks_Base {

    public function user_detail_box( $section ) {
        if ( $section == 'zume_contact_details' ) :
            global $post;
            DT_Zume_Core::check_for_update( $post->ID, 'contact' );
            $record = get_post_meta( $post->ID, 'zume_raw_record', true );
            $plan_key = md5( maybe_serialize( $record['zume_three_month_plan'] ?? '' ) );
            ?>
            <label class="section-header"><?php esc_html_e( 'Zúme Info' ) ?></label>

            <style>
                #zume-tabs li a { padding: 1rem 1rem; }
            </style>

            <ul class="tabs" data-tabs id="zume-tabs">
                <li class="tabs-title is-active"><a href="#info" aria-selected="true"><?php esc_html_e( 'Info' ) ?></a></li>
                <li class="tabs-title"><a href="#map" data-tabs-target="map"><?php esc_html_e( 'Map' ) ?></a></li>
                <?php if ( user_can( get_current_user_id(), 'manage_dt' ) ) : ?>
                <li class="tabs-title"><a data-tabs-target="raw" href="#raw"><?php esc_html_e( 'Raw' ) ?></a></li>
                <?php endif; ?>
            </ul>

            <div class="tabs-content" data-tabs-content="zume-tabs">
                <!-- Sessions Tab -->
                <div class="tabs-panel is-active" id="info" style="min-height: 375px;">
                    <dl>

                    <?php if ( isset( $record['zume_three_month_plan'] ) ) : ?>
                        <dt>
                            <?php esc_html_e( 'Three Month Plan' ) ?>:
                        </dt>
                        <dd>
                            <a data-open="<?php echo esc_attr( $plan_key ) ?>"><?php esc_html_e( 'View Three Month Plan' ) ?></a>
                        </dd>
                    <?php endif; ?>

                    <?php if ( isset( $record['user_registered'] ) && ! empty( $record['user_registered'] ) ) :
                        $mdy = DateTime::createFromFormat( 'Y-m-d H:i:s', $record['user_registered'] )->format( 'm/d/Y' );
                        ?>
                            <dt>
                                <?php esc_html_e( 'Started Zúme' ) ?>:
                            </dt>
                            <dd>
                                <?php echo esc_attr( $mdy ) ?>
                            </dd>
                    <?php endif; ?>

                    <?php if ( isset( $record['last_activity'] ) && ! empty( $record['last_activity'] ) ) :
                        $mdy = DateTime::createFromFormat( 'Y-m-d H:i:s', $record['last_activity'] )->format( 'm/d/Y' );
                        ?>
                        <dt>
                            <?php esc_html_e( 'Last Active on Zúme' ) ?>:
                        </dt>
                        <dd>
                            <?php echo esc_attr( $mdy ) ?>
                        </dd>
                    <?php endif; ?>

                    <?php if ( isset( $record['zume_groups'] ) && ! empty( $record['zume_groups'] ) ) : ?>
                        <dt>
                            <?php esc_html_e( 'Number of Groups Started' ) ?>:
                        </dt>
                        <dd>
                            <?php echo $record['zume_groups'] ? count( maybe_unserialize( $record['zume_groups'] ) ) : 0; ?>
                        </dd>
                    <?php endif; ?>

                    <?php if ( isset( $record['zume_language'] ) && ! empty( $record['zume_language'] ) ) : ?>
                        <dt>
                            <?php esc_html_e( 'Language' ) ?>:
                        </dt>
                        <dd>
                            <?php echo $record['zume_language'] ? esc_html( strtoupper( $record['zume_language'] ) ) : esc_html__( 'Unknown' ); ?>
                        </dd>
                    <?php endif; ?>

                    </dl>

                    <?php if ( isset( $record['zume_three_month_plan'] ) ) : ?>
                    <div class="reveal small" id="<?php echo esc_attr( $plan_key ) ?>" data-reveal>
                        <h3><?php esc_html_e( 'Three Month Plan' ) ?></h3><hr>
                        <dl>
                            <?php
                            foreach ( $record['zume_three_month_plan'] as $key => $value ) {
                                echo '<dt>'. esc_html( ucwords( str_replace( '_', ' ', $key ) ) ) .'</dt>';
                                echo '<dd>'. esc_html( $value ?: __( 'Not answered' ) ) .'</dd>';
                            }
                            ?>
                        </dl>

                        <button class="close-button" data-close aria-label="Close modal" type="button">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php endif; ?>

                </div>

                <!-- Map Tab-->
                <div class="tabs-panel" id="map">
                    <?php
                    $raw_location = [];
                    $show = true;
                    if ( isset( $record['zume_raw_location'] ) && ! empty( $record['zume_raw_location'] ) ) {
                        $raw_location = $record['zume_raw_location'];
                        $source = 'from user';
                    } elseif ( isset( $record['zume_raw_location_from_ip'] ) && ! empty( $record['zume_raw_location_from_ip'] ) ) {
                        $raw_location = $record['zume_raw_location_from_ip'];
                        $source = 'from ip address';
                    } else {
                        $show = false;
                    }

                    if ( $show ) {
                        $lat = Disciple_Tools_Google_Geocode_API::parse_raw_result( $raw_location, 'lat' );
                        $lng = Disciple_Tools_Google_Geocode_API::parse_raw_result( $raw_location, 'lng' );
                        $address = Disciple_Tools_Google_Geocode_API::parse_raw_result( $raw_location, 'formatted_address' );

                        if ( empty( $lng ) || empty( $lat ) ) :
                            echo '<p>'. esc_html__( 'No map info gathered.' ) .'</p>';
                        else :
                            ?>

                            <p><?php echo esc_html( $address ) ?> <span class="text-small grey">( <?php echo esc_html( $source ) ?> )</span></p>
                            <a id="map-reveal" data-open="<?php echo esc_attr( md5( $address ?? 'none' ) ) ?>"><img src="https://maps.googleapis.com/maps/api/staticmap?center=<?php echo esc_attr( $lat ) . ',' . esc_attr( $lng ) ?>&zoom=6&size=640x640&scale=1&markers=color:red|<?php echo esc_attr( $lat ) . ',' . esc_attr( $lng ) ?>&key=<?php echo esc_attr( Disciple_Tools_Google_Geocode_API::key() ); ?>"/></a>
                            <p class="center"><a data-open="<?php echo esc_attr( md5( $address ?? 'none' ) ) ?>"><?php esc_html_e( 'click to show large map' ) ?></a></p>

                            <div class="reveal large" id="<?php echo esc_attr( md5( $address ?? 'none' ) ) ?>" data-reveal>
                                <img  src="https://maps.googleapis.com/maps/api/staticmap?center=<?php echo esc_attr( $lat ) . ',' . esc_attr( $lng ) ?>&zoom=5&size=640x550&scale=2&markers=color:red|<?php echo esc_attr( $lat ) . ',' . esc_attr( $lng ) ?>&key=<?php echo esc_attr( Disciple_Tools_Google_Geocode_API::key() ); ?>"/>
                                <button class="close-button" data-close aria-label="Close modal" type="button">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>

                            <?php
                        endif;
                    }
                    ?>
                </div>

                <!-- Raw Tab-->
                <?php if ( user_can( get_current_user_id(), 'manage_dt' ) ) : ?>
                <div class="tabs-panel" id="raw" style="width: 100%;height: 300px;overflow-y: scroll;overflow-x:hidden;">
                    <?php
                    if ( $record ) {
                        foreach ( $record as $key => $value ) {
                            echo '<strong>' . esc_attr( $key ) . ': </strong>' . esc_attr( maybe_serialize( $value ) ) . '<br>';
                        }
                    }
                    ?>
                </div>
                <?php endif; ?>
            </div>
        <br clear="all" />

            <?php
        endif;

    }

    public function user_filter_box( $sections, $post_type = '' ) {
        if ($post_type === "contacts") {
            global $post;
            if ( get_post_meta( $post->ID, 'zume_raw_record', true ) ) {
                $sections[] = 'zume_contact_details';
            }
        }
        return $sections;
    }

    public function register_fields( $fields, $post_type ) {
        if ( 'contacts' === $post_type ) {
            $fields["zume_last_check"] = [
                "name" => 'Zume Last Check Field',
                "type" => "text",
                "default" => '',
                "hidden" => true,
            ];
            $fields["zume_raw_record"] = [
                "name" => 'Zume Raw Record Field',
                "type" => "text",
                "default" => '',
                "hidden" => true,
            ];
            $fields["zume_check_sum"] = [
                "name" => 'Zume Check Sum Field',
                "type" => "text",
                "default" => '',
                "hidden" => true,
            ];
        }
        return $fields;
    }

    public function __construct() {
        add_action( 'dt_details_additional_section', [ $this, 'user_detail_box' ] );
        add_filter( 'dt_details_additional_section_ids', [ $this, 'user_filter_box' ], 999, 2 );
        add_filter( 'dt_custom_fields_settings', [ $this, 'register_fields' ], 999, 2 );

        parent::__construct();
    }

}

/**
 * Class DT_Zume_Hooks_Groups
 */
class DT_Zume_Hooks_Groups extends DT_Zume_Hooks_Base {

    public function group_detail_box( $section ) {
        global $post;
        if ( $section == 'zume_group_details' && get_post_meta( $post->ID, 'zume_raw_record', true ) ) :
            DT_Zume_Core::check_for_update( $post->ID, 'group' );
            $record = get_post_meta( $post->ID, 'zume_raw_record', true );
            ?>
            <label class="section-header"><?php esc_html_e( 'Zúme Info' ) ?></label>

            <style>
                #zume-tabs li a { padding: 1rem 1rem; }
            </style>

            <ul class="tabs" data-tabs id="zume-tabs">
                <li class="tabs-title is-active"><a href="#sessions" aria-selected="true"><?php esc_html_e( 'Sessions' ) ?></a></li>
                <li class="tabs-title"><a href="#info" data-tabs-target="info"><?php esc_html_e( 'Info' ) ?></a></li>
                <li class="tabs-title"><a href="#map" data-tabs-target="map"><?php esc_html_e( 'Map' ) ?></a></li>
                <?php if ( user_can( get_current_user_id(), 'manage_dt' ) ) : ?>
                <li class="tabs-title"><a data-tabs-target="raw" href="#raw"><?php esc_html_e( 'Raw' ) ?></a></li>
                <?php endif; ?>
            </ul>

            <div class="tabs-content" data-tabs-content="zume-tabs">
                <!-- Sessions Tab -->
                <div class="tabs-panel is-active" id="sessions">
                    <style>
                        .date-text {
                            font-size:.8em;
                        }
                    </style>
                    <?php
                    if ( $record ) { ?>

                        <!-- sessions -->
                        <button class="button <?php echo esc_html( $record['session_1'] ? 'success' : 'hollow' ) ?> expanded" type="button">
                            <strong><?php echo esc_html( 'Session 1' ) ?></strong>
                            <?php echo $record['session_1_complete'] ? '<br><span class="date-text">' . esc_html( date( 'M j, Y', strtotime( $record['session_1_complete'] ) ) ) . '</span>' : ''  ?>
                        </button>
                        <button class="button <?php echo esc_html( $record['session_2'] ? 'success' : 'hollow' ) ?> expanded" type="button">
                            <strong><?php echo esc_html( 'Session 2' ) ?></strong>
                            <?php echo $record['session_2_complete'] ? '<br><span class="date-text">' . esc_html( date( 'M j, Y', strtotime( $record['session_2_complete'] ) ) ) . '</span>' : ''  ?>
                        </button>
                        <button class="button <?php echo esc_html( $record['session_3'] ? 'success' : 'hollow' ) ?> expanded" type="button">
                            <strong><?php echo esc_html( 'Session 3' ) ?></strong>
                            <?php echo $record['session_3_complete'] ? '<br><span class="date-text">' . esc_html( date( 'M j, Y', strtotime( $record['session_3_complete'] ) ) ) . '</span>' : ''  ?>
                        </button>
                        <button class="button <?php echo esc_html( $record['session_4'] ? 'success' : 'hollow' ) ?> expanded" type="button">
                            <strong><?php echo esc_html( 'Session 4' ) ?></strong>
                            <?php echo $record['session_4_complete'] ? '<br><span class="date-text">' . esc_html( date( 'M j, Y', strtotime( $record['session_4_complete'] ) ) ) . '</span>' : ''  ?>
                        </button>
                        <button class="button <?php echo esc_html( $record['session_5'] ? 'success' : 'hollow' ) ?> expanded" type="button">
                            <strong><?php echo esc_html( 'Session 5' ) ?></strong>
                            <?php echo $record['session_5_complete'] ? '<br><span class="date-text">' . esc_html( date( 'M j, Y', strtotime( $record['session_5_complete'] ) ) ) . '</span>' : ''  ?>
                        </button>
                        <button class="button <?php echo esc_html( $record['session_6'] ? 'success' : 'hollow' ) ?> expanded" type="button">
                            <strong><?php echo esc_html( 'Session 6' ) ?></strong>
                            <?php echo $record['session_6_complete'] ? '<br><span class="date-text">' . esc_html( date( 'M j, Y', strtotime( $record['session_6_complete'] ) ) ) . '</span>' : ''  ?>
                        </button>
                        <button class="button <?php echo esc_html( $record['session_7'] ? 'success' : 'hollow' ) ?> expanded" type="button">
                            <strong><?php echo esc_html( 'Session 7' ) ?></strong>
                            <?php echo $record['session_7_complete'] ? '<br><span class="date-text">' . esc_html( date( 'M j, Y', strtotime( $record['session_7_complete'] ) ) ) . '</span>' : ''  ?>
                        </button>
                        <button class="button <?php echo esc_html( $record['session_8'] ? 'success' : 'hollow' ) ?> expanded" type="button">
                            <strong><?php echo esc_html( 'Session 8' ) ?></strong>
                            <?php echo $record['session_8_complete'] ? '<br><span class="date-text">' . esc_html( date( 'M j, Y', strtotime( $record['session_8_complete'] ) ) ) . '</span>' : ''  ?>
                        </button>
                        <button class="button <?php echo esc_html( $record['session_9'] ? 'success' : 'hollow' ) ?> expanded" type="button">
                            <strong><?php echo esc_html( 'Session 9' ) ?></strong>
                            <?php echo $record['session_9_complete'] ? '<br><span class="date-text">' . esc_html( date( 'M j, Y', strtotime( $record['session_9_complete'] ) ) ) . '</span>' : ''  ?>
                        </button>
                        <button class="button <?php echo esc_html( $record['session_10'] ? 'success' : 'hollow' ) ?> expanded" type="button">
                            <strong><?php echo esc_html( 'Session 10' ) ?></strong>
                            <?php echo $record['session_10_complete'] ? '<br><span class="date-text">' . esc_html( date( 'M j, Y', strtotime( $record['session_10_complete'] ) ) ) . '</span>' : ''  ?>
                        </button>

                    <?php } // endif ?>
                </div>

                <!-- Info box -->
                <div class="tabs-panel" id="info" style="min-height: 375px;">

                    <dl>

                        <?php if ( isset( $record['members'] ) && ! empty( $record['members'] ) ) :
                            ?>
                            <dt>
                                <?php esc_html_e( 'Members' ) ?>:
                            </dt>
                            <dd>
                                <?php echo esc_attr( $record['members'] ) ?>
                            </dd>
                        <?php endif; ?>

                        <?php if ( isset( $record['coleaders_accepted'] ) && ! empty( $record['coleaders_accepted'] ) ) :
                            ?>
                            <dt>
                                <?php esc_html_e( 'Coleaders' ) ?>:
                            </dt>
                            <dd>
                                <?php echo esc_attr( is_array( $record['coleaders_accepted'] ) ? count( $record['coleaders_accepted'] ) : '' ) ?>
                            </dd>
                        <?php endif; ?>

                        <?php if ( isset( $record['meeting_time'] ) && ! empty( $record['meeting_time'] ) ) :
                            ?>
                            <dt>
                                <?php esc_html_e( 'Meeting Time' ) ?>:
                            </dt>
                            <dd>
                                <?php echo esc_attr( $record['meeting_time'] ) ?>
                            </dd>
                        <?php endif; ?>

                        <?php if ( isset( $record['created_date'] ) && ! empty( $record['created_date'] ) ) :
                            $mdy = DateTime::createFromFormat( 'Y-m-d H:i:s', $record['created_date'] )->format( 'm/d/Y' );
                            ?>
                            <dt>
                                <?php esc_html_e( 'Group Start Date' ) ?>:
                            </dt>
                            <dd>
                                <?php echo esc_attr( $mdy ) ?>
                            </dd>
                        <?php endif; ?>

                        <?php if ( isset( $record['last_modified_date'] ) && ! empty( $record['last_modified_date'] ) ) :
                            $mdy = DateTime::createFromFormat( 'Y-m-d H:i:s', $record['last_modified_date'] )->format( 'm/d/Y' );
                            ?>
                            <dt>
                                <?php esc_html_e( 'Last Active' ) ?>:
                            </dt>
                            <dd>
                                <?php echo esc_attr( $mdy ) ?>
                            </dd>
                        <?php endif; ?>

                        <?php if ( isset( $record['closed'] ) ) :
                            ?>
                            <dt>
                                <?php esc_html_e( 'Status' ) ?>:
                            </dt>
                            <dd>
                                <?php echo esc_attr( $record['closed'] ? __( 'Open' ) : __( 'Closed' ) ) ?>
                            </dd>
                        <?php endif; ?>



                    </dl>

                </div>

                <!-- Map Tab-->
                <div class="tabs-panel" id="map">
                    <?php
                    $raw_location = [];
                    $show = true;
                    if ( isset( $record['raw_location'] ) && ! empty( $record['raw_location'] ) ) {
                        $raw_location = $record['raw_location'];
                        $source = 'from user';
                    } elseif ( isset( $record['ip_raw_location'] ) && ! empty( $record['ip_raw_location'] ) ) {
                        $raw_location = $record['ip_raw_location'];
                        $source = 'from ip address';
                    } else {
                         $show = false;
                    }

                    if ( $show ) {
                        $lat = Disciple_Tools_Google_Geocode_API::parse_raw_result( $raw_location, 'lat' );
                        $lng = Disciple_Tools_Google_Geocode_API::parse_raw_result( $raw_location, 'lng' );
                        $address = Disciple_Tools_Google_Geocode_API::parse_raw_result( $raw_location, 'formatted_address' );

                        if ( empty( $lng ) || empty( $lat ) ) :
                            echo '<p>' . esc_html__( 'No map info gathered.' ) . '</p>';
                        else :
                            ?>

                            <p><?php echo esc_html( $address ) ?> <span
                                        class="text-small grey">( <?php echo esc_html( $source ) ?> )</span></p>
                            <a id="map-reveal" data-open="<?php echo esc_attr( md5( $address ?? 'none' ) ) ?>"><img
                                        src="https://maps.googleapis.com/maps/api/staticmap?center=<?php echo esc_attr( $lat ) . ',' . esc_attr( $lng ) ?>&zoom=6&size=640x640&scale=1&markers=color:red|<?php echo esc_attr( $lat ) . ',' . esc_attr( $lng ) ?>&key=<?php echo esc_attr( Disciple_Tools_Google_Geocode_API::key() ); ?>"/></a>
                            <p class="center"><a
                                        data-open="<?php echo esc_attr( md5( $address ?? 'none' ) ) ?>"><?php esc_html_e( 'click to show large map' ) ?></a>
                            </p>

                            <div class="reveal large" id="<?php echo esc_attr( md5( $address ?? 'none' ) ) ?>"
                                 data-reveal>
                                <img src="https://maps.googleapis.com/maps/api/staticmap?center=<?php echo esc_attr( $lat ) . ',' . esc_attr( $lng ) ?>&zoom=5&size=640x550&scale=2&markers=color:red|<?php echo esc_attr( $lat ) . ',' . esc_attr( $lng ) ?>&key=<?php echo esc_attr( Disciple_Tools_Google_Geocode_API::key() ); ?>"/>
                                <button class="close-button" data-close aria-label="Close modal" type="button">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>

                            <?php
                        endif;
                    }
                    ?>
            </div>
            <br clear="all" />

            <!-- Raw Tab-->
            <?php if ( user_can( get_current_user_id(), 'manage_dt' ) ) : ?>
                <div class="tabs-panel" id="raw" style="width: 100%;height: 300px;overflow-y: scroll;overflow-x:hidden;">
                    <?php
                    if ( $record ) {
                        foreach ( $record as $key => $value ) {
                            echo '<strong>' . esc_attr( $key ) . ': </strong>' . esc_attr( maybe_serialize( $value ) ) . '<br>';
                        }
                    }
                    ?>
                </div>
            <?php endif; ?>

            <script>
                jQuery(document).ready(function(){
                    jQuery.ajax(window.location, function(data) {
                        jQuery('#map-reveal').html(data).foundation();
                    });
                })
            </script>
            <?php
        endif;

    }

    public function groups_filter_box( $sections, $post_type = '' ) {
        if ($post_type === "groups") {
            global $post;
            if ( get_post_meta( $post->ID, 'zume_raw_record', true ) ) {
                $sections[] = 'zume_group_details';
            }
        }
        return $sections;
    }

    public function register_fields( $fields, $post_type ) {
        if ( 'groups' === $post_type ) {
            $fields["zume_last_check"] = [
                "name" => 'Zume Last Check Field',
                "type" => "text",
                "default" => '',
                "hidden" => true,
            ];
            $fields["zume_raw_record"] = [
                "name" => 'Zume Raw Record Field',
                "type" => "text",
                "default" => '',
                "hidden" => true,
            ];
            $fields["zume_check_sum"] = [
                "name" => 'Zume Check Sum Field',
                "type" => "text",
                "default" => '',
                "hidden" => true,
            ];
        }
        return $fields;
    }

    public function __construct() {
        add_action( 'dt_details_additional_section', [ $this, 'group_detail_box' ] );
        add_filter( 'dt_details_additional_section_ids', [ $this, 'groups_filter_box' ], 999, 2 );
        add_filter( 'dt_custom_fields_settings', [ $this, 'register_fields' ], 999, 2 );

        parent::__construct();
    }
}


class DT_Zume_Hooks_Training extends DT_Zume_Hooks_Base
{
    /**
     * This filter adds a menu item to the metrics
     *
     * @param $content
     *
     * @return string
     */
    public function menu( $content ) {
        $content .= '<li><a href="'. site_url( '/training/' ) .'#zume_project" onclick="show_zume_project()">' .  esc_html__( 'Zúme Project', 'dt_zume' ) . '</a>
            <ul class="menu vertical nested is-active">
              <li><a href="'. site_url( '/training/' ) .'#zume_project" onclick="show_zume_project()">' .  esc_html__( 'Overview', 'dt_zume' ) . '</a></li>
              <li><a href="'. site_url( '/training/' ) .'#zume_locations" onclick="show_zume_locations()">' .  esc_html__( 'Locations', 'dt_zume' ) . '</a></li>
              <li><a href="'. site_url( '/training/' ) .'#zume_groups" onclick="show_zume_groups()">' .  esc_html__( 'Groups', 'dt_zume' ) . '</a></li>
              <li><a href="'. site_url( '/training/' ) .'#zume_people" onclick="show_zume_people()">' .  esc_html__( 'People', 'dt_zume' ) . '</a></li>
            </ul>
          </li>';
        return $content;
    }

    /**
     * Load scripts for the plugin
     */
    public function scripts() {
        $url_path = trim( parse_url( add_query_arg( array() ), PHP_URL_PATH ), '/' );

        if ( 'training' === substr( $url_path, '0', 8 ) ) {
            wp_enqueue_script( 'dt_zume_script',
                DT_Zume::get_instance()->includes_uri . 'metrics.js',
                [
                'jquery',
                'jquery-ui-core',
                ],
                filemtime( DT_Zume::get_instance()->includes_path . 'metrics.js' ),
            true );

            wp_localize_script(
                'dt_zume_script',
                'wpApiZumeMetrics',
                [
                    'root' => esc_url_raw( rest_url() ),
                    'plugin_uri' => DT_Zume::get_instance()->dir_uri,
                    'nonce' => wp_create_nonce( 'wp_rest' ),
                    'current_user_login' => wp_get_current_user()->user_login,
                    'current_user_id' => get_current_user_id(),
                    'map_key' => dt_get_option( 'map_key' ),
                    'zume_stats' => DT_Zume_Core::get_project_stats(),
                    'translations' => [
                        "zume_project" => __( "Zúme Overview", "dt_zume" ),
                        "zume_groups" => __( "Zúme Groups", "dt_zume" ),
                        "zume_people" => __( "Zúme People", "dt_zume" ),
                        "zume_locations" => __( "Zúme Locations", "dt_zume" ),
                    ]
                ]
            );
        }
    }

    public function check_zume_raw_data() {
        $url_path = trim( parse_url( add_query_arg( array() ), PHP_URL_PATH ), '/' );

        if ( 'training' === substr( $url_path, '0', 8 ) && DT_Zume_Core::test_zume_global_stats_needs_update() ) {
            DT_Zume_Core::get_project_stats();
        }
    }

    public function add_url( $template_for_url ) {
        $template_for_url['training'] = 'template-metrics.php';
        return $template_for_url;
    }

    public function top_nav_desktop() {
        if ( user_can( get_current_user_id(), 'view_contacts' ) || user_can( get_current_user_id(), 'view_project_metrics' ) ) {
            ?><li><a href="<?php echo esc_url( site_url( '/training/' ) ); ?>"><?php esc_html_e( "Online Training" ); ?></a></li><?php
        }
    }

    // Enqueue maps and charts for standard metrics
    public function enqueue_google() {
        /* phpcs:ignore WordPress.WP.EnqueuedResourceParameters */
        wp_enqueue_script( 'google-charts', 'https://www.gstatic.com/charts/loader.js', [], false );
        /* phpcs:ignore WordPress.WP.EnqueuedResourceParameters */
        wp_enqueue_script( 'google-maps', 'https://maps.googleapis.com/maps/api/js?key=' . dt_get_option( 'map_key' ), array(), null, true );
    }

    public function return_link_to_zume_project() {
        ?><li><a href="<?php echo esc_url( network_site_url( '/dashboard', 'https' ) ); ?>"><?php esc_html_e( "Zúme Dashboard" ); ?></a></li><?php
    }

    public function __construct() {

        if ( user_can( get_current_user_id(), 'view_contacts' ) ) {

            add_action( 'dt_top_nav_desktop', [ $this, 'top_nav_desktop' ] );
            add_action( 'dt_settings_menu_post', [ $this, 'return_link_to_zume_project' ] );
            add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_google' ], 10 );
            add_action( 'plugins_loaded', [ $this, 'check_zume_raw_data' ] );

            if ( isset( $_SERVER["SERVER_NAME"] ) ) {
                $url  = ( !isset( $_SERVER["HTTPS"] ) || @( $_SERVER["HTTPS"] != 'on' ) ) ? 'http://'. sanitize_text_field( wp_unslash( $_SERVER["SERVER_NAME"] ) ) : 'https://'. sanitize_text_field( wp_unslash( $_SERVER["SERVER_NAME"] ) );
                if ( isset( $_SERVER["REQUEST_URI"] ) ) {
                    $url .= sanitize_text_field( wp_unslash( $_SERVER["REQUEST_URI"] ) );
                }
            }
            $url_path = trim( str_replace( get_site_url(), "", $url ), '/' );

            if ( 'training' === substr( $url_path, '0', 8 ) ) {

                add_filter( 'dt_templates_for_urls', [ $this, 'add_url' ] ); // add custom URL
                add_filter( 'dt_metrics_menu', [ $this, 'menu' ], 99 );
                add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );

            }
        } // end admin only test
    }
}