<?php
    /*
    Author: Yauhen Kharuzhy <jekhor@gmail.com>
    Plugin URI:
    Plugin Name: Eurovelo.by Map
    Description: A plugin for add maps of eurovelo.by routes
    Version: 0.1
    License: GPL2
    */

if (!class_exists('Eurovelo_Map_Plugin')) {

    class Eurovelo_Map_Plugin {

        public static $defaults = array (
            'text' => array(
                'eurovelo_default_zoom' => '7',
                'eurovelo_default_height' => '250',
		'eurovelo_default_width' => '100%',
		'eurovelo_routes_baseurl' => '/wp-content/uploads'
                ),
            'textarea' => array(
                ),
            'select' => array(
                ),
            'checkbox' => array(
                'eurovelo_show_zoom_controls' => '1',
                'eurovelo_scroll_wheel_zoom' => '1'
                ),
            'serialized' => array(
                )
            );

        public static $helptext = array(
            'eurovelo_default_zoom' => 'Can set per map in shortcode or adjust for all maps here; e.g. <br /> <code>[leaflet-map zoom="5"]</code>',
            'eurovelo_default_height' => 'Can set per map in shortcode or adjust for all maps here. Values can include "px" but it is not necessary.  Can also be %; e.g. <br/> <code>[leaflet-map height="250"]</code>',
            'eurovelo_default_width' => 'Can set per map in shortcode or adjust for all maps here. Values can include "px" but it is not necessary.  Can also be %; e.g. <br/> <code>[leaflet-map height="250"]</code>',
            'eurovelo_show_zoom_controls' => 'The zoom buttons can be large and annoying.  Enabled or disable per map in shortcode: <br/> <code>[leaflet-map zoomcontrol="0"]</code>',
	    'eurovelo_scroll_wheel_zoom' => 'Disable zoom with mouse scroll wheel.  Sometimes someone wants to scroll down the page, and not zoom the map.  Enabled or disable per map in shortcode: <br/> <code>[leaflet-map scrollwheel="0"]</code>',
	    'eurovelo_routes_baseurl' => 'URL of directory with KML routes files'
            );

        public function __construct() {
            add_action('admin_init', array(&$this, 'admin_init'));
            add_action('admin_menu', array(&$this, 'admin_menu'));

            add_shortcode('eurovelo-map', array(&$this, 'map_shortcode'));

            add_action( 'wp_enqueue_scripts', array(&$this, 'enqueue_and_register') );
            add_action( 'admin_enqueue_scripts', array(&$this, 'enqueue_and_register') );

            /* allow maps on excerpts */
            add_filter('the_excerpt', 'do_shortcode');
        }

        public static function activate () {
	    $defs = self::$defaults;
	    $defs['text']['eurovelo_routes_baseurl'] = wp_upload_dir()['baseurl'];
            /* set default values to db */
            foreach(self::$defaults as $arrs) {
                foreach($arrs as $k=>$v) {
                    add_option($k, $v);
               }
	    }
	}

        public static function uninstall () {

            /* remove values from db */
            foreach (self::$defaults as $arrs) {
                foreach($arrs as $k=>$v) {
                    delete_option($k);
                }
            }
        }

        public function enqueue_and_register () {
            $defaults = $this::$defaults['text'];

	    $version = "1.0.0~beta2";
            $css_url = sprintf("//cdn.leafletjs.com/leaflet/v%s/leaflet.css", $version);
	    $js_url = sprintf("//cdn.leafletjs.com/leaflet/v0.7.7/leaflet.js", $version);

	    wp_register_style('leaflet_stylesheet', plugins_url('lib/Leaflet/leaflet.css', __FILE__), Array(), $version);
	    wp_register_style('leaflet_fullscreen_css', plugins_url('lib/Leaflet.fullscreen/dist/leaflet.fullscreen.css', __FILE__),
		    ['leaflet_stylesheet']);
	    wp_register_style('leaflet_markercluster_css', plugins_url('lib/Leaflet.markercluster/dist/MarkerCluster.css', __FILE__),
		    ['leaflet_stylesheet']);

	    wp_register_style('eurovelo_map_css', plugins_url('map.css', __FILE__),
		    ['leaflet_stylesheet']);

	    wp_register_style('leaflet_markercluster_def_css', plugins_url('lib/Leaflet.markercluster/dist/MarkerCluster.Default.css', __FILE__),
		    ['leaflet_markercluster_css']);
	    wp_register_style('leaflet_layer_tree_css', plugins_url('lib/leaflet-layer-tree/leaflet-layer-tree-control.css', __FILE__),
		    ['leaflet_stylesheet']);
	    wp_register_style('leaflet_grouped_layer_control_css', plugins_url('lib/leaflet-groupedlayercontrol/leaflet.groupedlayercontrol.min.css', __FILE__),
		    ['leaflet_stylesheet']);

        wp_register_style('leaflet_locate_control_css', plugins_url('lib/leaflet-locatecontrol/L.Control.Locate.min.css', __FILE__),
                    ['leaflet_stylesheet']);

        wp_register_style('leaflet_gesture_handing_css', plugins_url('lib/Leaflet.GestureHandling/leaflet-gesture-handling.min.css', __FILE__),
                            ['leaflet_stylesheet']);

	    wp_register_style('font-awesome_css', plugins_url('lib/font-awesome-4.5.0/css/font-awesome.min.css', __FILE__), array());

            wp_register_script('leaflet_js', plugins_url('lib/Leaflet/leaflet-src.js', __FILE__), Array(), $version, true);
	    wp_register_script('leaflet_fullscreen_js', plugins_url('lib/Leaflet.fullscreen/dist/Leaflet.fullscreen.min.js', __FILE__),
		    ['leaflet_js'], null, true);

	    wp_register_script('leaflet_markercluster_js', plugins_url('lib/Leaflet.markercluster/dist/leaflet.markercluster.js', __FILE__),
		    ['leaflet_js'], null, true);

	    wp_register_script('leaflet_layer_tree_js', plugins_url('lib/leaflet-layer-tree/leaflet-layer-tree-control.js', __FILE__),
		    ['leaflet_js'], null, true);
	    wp_register_script('leaflet_grouped_layer_control_js', plugins_url('lib/leaflet-groupedlayercontrol/leaflet.groupedlayercontrol.min.js', __FILE__),
		    ['leaflet_js'], null, true);
	    wp_register_script('leaflet_featuregroup_subgroup_js', plugins_url('lib/Leaflet.markercluster/leaflet.featuregroup.subgroup.js', __FILE__),
		    ['leaflet_js', 'leaflet_markercluster_js'], null, true);

        wp_register_script('leaflet_hash_js', plugins_url('lib/leaflet-hash/leaflet-hash.js', __FILE__),
                    ['leaflet_js'], null, true);
        wp_register_script('leaflet_locate_control_js', plugins_url('lib/leaflet-locatecontrol/L.Control.Locate.min.js', __FILE__),
                            ['leaflet_js'], null, true);
        wp_register_script('leaflet_gesture_handing_js', plugins_url('lib/Leaflet.GestureHandling/leaflet-gesture-handling.min.js', __FILE__),
                                    ['leaflet_js'], null, true);
        wp_register_script('map_style_js', plugins_url('scripts/map-root-style.js', __FILE__),
                                    ['leaflet_js'], null, true);

//	    wp_register_script('mapbox_omnivore',
//		    '//api.tiles.mapbox.com/mapbox.js/plugins/leaflet-omnivore/v0.3.1/leaflet-omnivore.js',
//		    ['leaflet_js'], null, true);
	    wp_register_script('mapbox_omnivore',
		    plugins_url('lib/leaflet-omnivore/leaflet-omnivore.js', __FILE__),
		    ['leaflet_js'], null, true);


	    wp_register_script('globus_data', plugins_url('data/gb-utf8.js', __FILE__), array(), null, true);


            /* run an init function because other wordpress plugins don't play well with their window.onload functions */
	    wp_register_script('eurovelo_map_init', plugins_url('scripts/init-eurovelo-map.js', __FILE__),
		    Array('leaflet_js', 'leaflet_markercluster_js',
		    'mapbox_omnivore', 'globus_data'), '0.1', true);

            /* run a construct function in the document head for the init function to use */
            wp_enqueue_script('eurovelo_map_construct', plugins_url('scripts/construct-eurovelo-map.js', __FILE__), Array(), '0.1', false);

        }

        public function admin_init () {
            wp_register_style('eurovelo_admin_stylesheet', plugins_url('style.css', __FILE__));
        }

        public function admin_menu () {
            add_menu_page("Eurovelo Map", "Eurovelo Map", 'manage_options', "eurovelo-map", array(&$this, "settings_page"), plugins_url('images/leaf.png', __FILE__));
            add_submenu_page("eurovelo-map", "Default Values", "Default Values", 'manage_options', "eurovelo-map", array(&$this, "settings_page"));
//            add_submenu_page("leaflet-map", "Shortcodes", "Shortcodes", 'manage_options', "leaflet-get-shortcode", array(&$this, "shortcode_page"));
        }

        public function settings_page () {

            wp_enqueue_style( 'eurovelo_admin_stylesheet' );

            include 'templates/admin.php';
        }
/*
        public function shortcode_page () {
            wp_enqueue_style( 'leaflet_admin_stylesheet' );
            wp_enqueue_script('custom_plugin_js', plugins_url('scripts/get-shortcode.js', __FILE__), Array('leaflet_js'), false);

            include 'templates/find-on-map.php';
        }
 */

	/* count map shortcodes to allow for multiple */
        public static $eurovelo_map_count;

        public function map_shortcode ( $atts ) {

            if (!$this::$eurovelo_map_count) {
            	$this::$eurovelo_map_count = 0;
            }
            $this::$eurovelo_map_count++;

            $eurovelo_map_count = $this::$eurovelo_map_count;

            $defaults = array_merge($this::$defaults['text'], $this::$defaults['textarea'], $this::$defaults['checkbox']);
	    $defaults['eurovelo_routes_baseurl'] = wp_upload_dir()['baseurl'];

            /* defaults from db */
            $default_zoom = get_option('eurovelo_default_zoom', $defaults['eurovelo_default_zoom']);
            $default_zoom_control = get_option('eurovelo_show_zoom_controls', $defaults['eurovelo_show_zoom_controls']);
            $default_height = get_option('eurovelo_default_height', $defaults['eurovelo_default_height']);
            $default_width = get_option('eurovelo_default_width', $defaults['eurovelo_default_width']);
            $default_scrollwheel = get_option('eurovelo_scroll_wheel_zoom', $defaults['eurovelo_scroll_wheel_zoom']);
            $default_routes_url = get_option('eurovelo_routes_baseurl', $defaults['eurovelo_routes_baseurl']);

            /* leaflet script */
        wp_enqueue_script('map_style_js');
	    wp_enqueue_style('eurovelo_map_css');
            wp_enqueue_style('leaflet_stylesheet');
	    wp_enqueue_style('leaflet_fullscreen_css');
	    wp_enqueue_style('leaflet_markercluster_css');
	    wp_enqueue_style('leaflet_markercluster_def_css');
	    wp_enqueue_style('leaflet_layer_tree_css');
	    wp_enqueue_style('leaflet_locate_control_css');
	    wp_enqueue_style('leaflet_grouped_layer_control_css');
        wp_enqueue_style('leaflet_gesture_handing_css');
	    wp_enqueue_style('font-awesome_css');
            wp_enqueue_script('leaflet_js');
            wp_enqueue_script('leaflet_fullscreen_js');
            wp_enqueue_script('leaflet_hash_js');
            wp_enqueue_script('leaflet_locate_control_js');
            wp_enqueue_script('leaflet_gesture_handing_js');
            wp_enqueue_script('leaflet_markercluster_js');
            wp_enqueue_script('leaflet_featuregroup_subgroup_js');
	    wp_enqueue_script('leaflet_layer_tree_js');
	    wp_enqueue_script('leaflet_grouped_layer_control_js');
            wp_enqueue_script('mapbox_omnivore');
            wp_enqueue_script('globus_data');
            wp_enqueue_script('eurovelo_map_init');

            if ($atts) {
                extract($atts);
            }

            $lat = empty($lat) ? '53.664' : $lat;
            $lng = empty($lng) ? '27.037' : $lng;

            /* check more user defined $atts against defaults */
            $zoomcontrol = empty($zoomcontrol) ? $default_zoom_control : $zoomcontrol;
            $zoom = empty($zoom) ? $default_zoom : $zoom;
            $scrollwheel = empty($scrollwheel) ? $default_scrollwheel : $scrollwheel;
            $height = empty($height) ? $default_height : $height;
            $width = empty($width) ? $default_width : $width;
	    $routes_url = empty($routes_url) ? $default_routes_url : $routes_url;
	    $disabled_routes = empty($disabled_routes) ? 'vh.kml' : $disabled_routes;

	    $disabled_routes = explode(',', $disabled_routes);
	    $disabled_routes = implode("','", $disabled_routes);
	    $disabled_routes = "'{$disabled_routes}'";


            /* allow percent, but add px for ints */
            $height .= is_numeric($height) ? 'px' : '';
            $width .= is_numeric($width) ? 'px' : '';

            /* should be iterated for multiple maps */
            $content = '<div id="eurovelo-wordpress-map-'.$eurovelo_map_count.'" class="eurovelo-wordpress-map" style="height:'.$height.'; width:'.$width.';"></div>';

	    $plugin_url = plugins_url('', __FILE__);

	    $poi_icons = file_get_contents(wp_upload_dir()['basedir'] . '/poi-icons-map.js');
	    if (!$poi_icons)
		    $poi_icons = 'null';

            $content .= "<script>
	    WPEuroveloMapPlugin.addMap('eurovelo-wordpress-map-{$eurovelo_map_count}', {
                lat: {$lat},
                lng: {$lng},
                zoom: {$zoom},
                scrollwheel: {$scrollwheel},
		zoomcontrol: {$zoomcontrol},
		routes_base_url: '{$routes_url}',
		disabled_routes: [{$disabled_routes}],
		plugin_url: '{$plugin_url}',
		poiIcons: {$poi_icons}
            });
            </script>";

            return $content;
        }
    } /* end class */

    register_activation_hook( __FILE__, array('Eurovelo_Map_Plugin', 'activate'));
    register_uninstall_hook( __FILE__, array('Eurovelo_Map_Plugin', 'uninstall') );

    $eurovelo_map_plugin = new Eurovelo_Map_Plugin();
}
?>
