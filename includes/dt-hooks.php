<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Class DT_Zume_Hooks
 */
class DT_Zume_DT_Hooks
{

    private static $_instance = null;

    public static function instance()
    {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    /**
     * Build hook classes
     */
    public function __construct()
    {
        new DT_Zume_DT_Hook_User();
        new DT_Zume_DT_Hook_Groups();
    }
}
DT_Zume_DT_Hooks::instance();

/**
 * Empty class for now..
 * Class DT_Zume_Hook_Base
 */
abstract class DT_Zume_DT_Hook_Base
{
    public function __construct()
    {
    }
}

/**
 * Class DT_Zume_Hook_User
 */
class DT_Zume_DT_Hook_User extends DT_Zume_DT_Hook_Base {

    public function user_detail_box( $section ) {
        if ( $section == 'zume_contact_details') :
            global $post;
            DT_Zume_DT::check_for_update( $post->ID, 'contact' );
            $record = get_post_meta( $post->ID, 'zume_raw_record', true );
            ?>
            <label class="section-header"><?php esc_html_e( 'Zúme Group Info' ) ?></label>

            <ul class="tabs" data-tabs id="zume-tabs">
                <li class="tabs-title is-active"><a href="#sessions" aria-selected="true"><?php esc_html_e( 'Sessions' ) ?></a></li>
                <li class="tabs-title"><a href="#map" data-tabs-target="map"><?php esc_html_e( 'Map' ) ?></a></li>
                <li class="tabs-title"><a data-tabs-target="raw" href="#raw"><?php esc_html_e( 'Raw' ) ?></a></li>
            </ul>

            <div class="tabs-content" data-tabs-content="zume-tabs">
                <!-- Sessions Tab -->
                <div class="tabs-panel is-active" id="sessions">

                </div>

                <!-- Map Tab-->
                <div class="tabs-panel" id="map">
                    <?php
                    $address = $record['zume_user_address'] ?: $record['zume_address_from_ip'];
                    $source = $record['zume_user_address'] ? 'User Provided Address' : 'Location from IP Address';
                    ?>
                    <p><?php echo esc_html( $address ) ?> <span class="text-small grey">( <?php echo esc_html( $source ) ?> )</span></p>
<!--                    <a id="map-reveal" data-open="--><?php //echo md5( $address ?? 'none' ) ?><!--"><img src="https://maps.googleapis.com/maps/api/staticmap?center=--><?php //echo esc_attr( $lat ) . ',' . esc_attr( $lng  ) ?><!--&zoom=6&size=640x640&scale=1&markers=color:red|--><?php //echo esc_attr( $lat  ) . ',' . esc_attr( $lng ) ?><!--&key=--><?php //echo esc_attr( Disciple_Tools_Google_Geocode_API::$key ); ?><!--"/></a>-->
                    <p class="center"><a data-open="<?php echo esc_attr( md5( $address ?? 'none' ) ) ?>"><?php esc_html_e( 'click to show large map' ) ?></a></p>

                    <div class="reveal large" id="<?php echo esc_attr( md5( $address ?? 'none' ) ) ?>" data-reveal>
<!--                        <img  src="https://maps.googleapis.com/maps/api/staticmap?center=--><?php //echo esc_attr( $lat ) . ',' . esc_attr( $lng ) ?><!--&zoom=5&size=640x640&scale=2&markers=color:red|--><?php //echo esc_attr( $lat ) . ',' . esc_attr( $lng ) ?><!--&key=--><?php //echo esc_attr( Disciple_Tools_Google_Geocode_API::$key ); ?><!--"/>-->
                        <button class="close-button" data-close aria-label="Close modal" type="button">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>

                <!-- Raw Tab-->
                <div class="tabs-panel" id="raw" style="width: 100%;height: 300px;overflow-y: scroll;overflow-x:hidden;">
                    <?php
                    if ( $record ) {
                        foreach ( $record as $key => $value ) {
                            echo '<strong>' . esc_attr( $key ) . ': </strong>' . esc_attr( maybe_serialize( $value ) ) . '<br>';
                        }
                    }
                    ?>
                </div>
            </div>


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

    public function user_filter_box( $sections, $post_type = '' ) {
        if ($post_type === "contacts") {
            $sections[] = 'zume_contact_details';
        }
        return $sections;
    }

    public function __construct() {
        add_action( 'dt_details_additional_section', [ $this, 'user_detail_box' ] );
        add_filter( 'dt_details_additional_section_ids', [ $this, 'user_filter_box' ], 999, 2 );

        parent::__construct();
    }

}

/**
 * Class DT_Zume_Hook_Groups
 */
class DT_Zume_DT_Hook_Groups extends DT_Zume_DT_Hook_Base {

    public function group_detail_box( $section ) {
        if ( $section == 'zume_group_details') :
            global $post;
            DT_Zume_DT::check_for_update( $post->ID, 'group' );
            $record = get_post_meta( $post->ID, 'zume_raw_record', true );
            ?>
            <label class="section-header"><?php esc_html_e( 'Zúme Group Info' ) ?></label>

            <ul class="tabs" data-tabs id="zume-tabs">
                <li class="tabs-title is-active"><a href="#sessions" aria-selected="true"><?php esc_html_e( 'Sessions' ) ?></a></li>
                <li class="tabs-title"><a href="#map" data-tabs-target="map"><?php esc_html_e( 'Map' ) ?></a></li>
                <li class="tabs-title"><a data-tabs-target="raw" href="#raw"><?php esc_html_e( 'Raw' ) ?></a></li>
            </ul>

            <div class="tabs-content" data-tabs-content="zume-tabs">
                <!-- Sessions Tab -->
                <div class="tabs-panel is-active" id="sessions">
                    <?php
                    if ( $record ) { ?>

                        <!-- sessions -->
                        <button class="button <?php echo esc_html( $record['session_1'] ? 'success' : 'hollow' ) ?> expanded" type="button"><?php echo esc_html( 'Session 1' ) ?></button>
                        <button class="button <?php echo esc_html( $record['session_2'] ? 'success' : 'hollow' ) ?> expanded" type="button"><?php echo esc_html( 'Session 2' ) ?></button>
                        <button class="button <?php echo esc_html( $record['session_3'] ? 'success' : 'hollow' ) ?> expanded" type="button"><?php echo esc_html( 'Session 3' ) ?></button>
                        <button class="button <?php echo esc_html( $record['session_4'] ? 'success' : 'hollow' ) ?> expanded" type="button"><?php echo esc_html( 'Session 4' ) ?></button>
                        <button class="button <?php echo esc_html( $record['session_5'] ? 'success' : 'hollow' ) ?> expanded" type="button"><?php echo esc_html( 'Session 5' ) ?></button>
                        <button class="button <?php echo esc_html( $record['session_6'] ? 'success' : 'hollow' ) ?> expanded" type="button"><?php echo esc_html( 'Session 6' ) ?></button>
                        <button class="button <?php echo esc_html( $record['session_7'] ? 'success' : 'hollow' ) ?> expanded" type="button"><?php echo esc_html( 'Session 7' ) ?></button>
                        <button class="button <?php echo esc_html( $record['session_8'] ? 'success' : 'hollow' ) ?> expanded" type="button"><?php echo esc_html( 'Session 8' ) ?></button>
                        <button class="button <?php echo esc_html( $record['session_9'] ? 'success' : 'hollow' ) ?> expanded" type="button"><?php echo esc_html( 'Session 9' ) ?></button>
                        <button class="button <?php echo esc_html( $record['session_10'] ? 'success' : 'hollow' ) ?> expanded" type="button"><?php echo esc_html( 'Session 10' ) ?></button>

                    <?php } // endif ?>
                </div>

                <!-- Map Tab-->
                <div class="tabs-panel" id="map">
                    <?php
                    $lng = $record['lng'] ?: $record['ip_lng'];
                    $lat = $record['lat'] ?: $record['ip_lat'];
                    $address = $record['address'] ?: $record['ip_address'];
                    $source = $record['address'] ? 'User Provided Address' : 'Location from IP Address';
                    ?>
                    <p><?php echo esc_html( $address ) ?> <span class="text-small grey">( <?php echo esc_html( $source ) ?> )</span></p>
                    <a id="map-reveal" data-open="<?php echo esc_attr( md5( $address ?? 'none' ) ) ?>"><img src="https://maps.googleapis.com/maps/api/staticmap?center=<?php echo esc_attr( $lat ) . ',' . esc_attr( $lng ) ?>&zoom=6&size=640x640&scale=1&markers=color:red|<?php echo esc_attr( $lat ) . ',' . esc_attr( $lng ) ?>&key=<?php echo esc_attr( Disciple_Tools_Google_Geocode_API::$key ); ?>"/></a>
                    <p class="center"><a data-open="<?php echo esc_attr( md5( $address ?? 'none' ) ) ?>"><?php esc_html_e( 'click to show large map' ) ?></a></p>

                    <div class="reveal large" id="<?php echo esc_attr( md5( $address ?? 'none' ) ) ?>" data-reveal>
                        <img  src="https://maps.googleapis.com/maps/api/staticmap?center=<?php echo esc_attr( $lat ) . ',' . esc_attr( $lng ) ?>&zoom=5&size=640x640&scale=2&markers=color:red|<?php echo esc_attr( $lat ) . ',' . esc_attr( $lng ) ?>&key=<?php echo esc_attr( Disciple_Tools_Google_Geocode_API::$key ); ?>"/>
                        <button class="close-button" data-close aria-label="Close modal" type="button">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>

                <!-- Raw Tab-->
                <div class="tabs-panel" id="raw" style="width: 100%;height: 300px;overflow-y: scroll;overflow-x:hidden;">
                    <?php
                    if ( $record ) {
                        foreach ( $record as $key => $value ) {
                            echo '<strong>' . esc_attr( $key ) . ': </strong>' . esc_attr( maybe_serialize( $value ) ) . '<br>';
                        }
                    }
                    ?>
                </div>
            </div>


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
            $sections[] = 'zume_group_details';
        }
        return $sections;
    }

    public function __construct() {
        add_action( 'dt_details_additional_section', [ $this, 'group_detail_box' ] );
        add_filter( 'dt_details_additional_section_ids', [ $this, 'groups_filter_box' ], 999, 2 );

        parent::__construct();
    }

}


