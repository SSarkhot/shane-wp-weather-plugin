<?php
/**
 * Plugin Name: Shane Weather
 * Description: A custom WordPress weather plugin for learning plugin development.
 * Version: 1.0.0
 * Author: Shane Sarkhot
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SHANE_WEATHER_PATH', plugin_dir_path( __FILE__ ) );
define( 'SHANE_WEATHER_URL', plugin_dir_url( __FILE__ ) );

require_once SHANE_WEATHER_PATH . 'includes/class-shane-weather-admin.php';
require_once SHANE_WEATHER_PATH . 'includes/class-shane-weather-api.php';
require_once SHANE_WEATHER_PATH . 'includes/class-shane-weather-frontend.php';
require_once SHANE_WEATHER_PATH . 'includes/class-shane-weather-widget.php';

function shane_weather_init_plugin() {
	$api      = new Shane_Weather_API();
	$admin    = new Shane_Weather_Admin();
	$frontend = new Shane_Weather_Frontend( $api );
	$widget   = new Shane_Weather_Widget( $frontend );

	$admin->init();
	$frontend->init();
	$widget->init();
}
add_action( 'plugins_loaded', 'shane_weather_init_plugin' );