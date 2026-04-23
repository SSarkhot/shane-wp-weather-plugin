<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Shane_Weather_API {

	public function __construct() {
		add_action( 'update_option_shane_weather_default_city', array( $this, 'clear_cache' ) );
		add_action( 'update_option_shane_weather_api_key', array( $this, 'clear_cache' ) );
	}

	public function get_weather_data() {
		$cached = get_transient( 'shane_weather_data' );

		if ( false !== $cached ) {
			return $cached;
		}

		$api_key = get_option( 'shane_weather_api_key' );
		$city    = get_option( 'shane_weather_default_city' );

		if ( empty( $api_key ) || empty( $city ) ) {
			return 'Missing API key or city.';
		}

		$url = "https://api.weatherapi.com/v1/current.json?key={$api_key}&q={$city}";

		$response = wp_remote_get( $url );

		if ( is_wp_error( $response ) ) {
			return 'Error fetching weather data.';
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		set_transient( 'shane_weather_data', $data, 15 * MINUTE_IN_SECONDS );

		return $data;
	}

	public function clear_cache() {
		delete_transient( 'shane_weather_data' );
	}
}