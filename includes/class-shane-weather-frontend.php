<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Shane_Weather_Frontend {

	private $api;

	public function __construct( $api ) {
		$this->api = $api;
	}

	public function init() {
		add_shortcode( 'shane_weather', array( $this, 'display' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_ajax_shane_weather_refresh', array( $this, 'ajax_refresh' ) );
		add_action( 'wp_ajax_nopriv_shane_weather_refresh', array( $this, 'ajax_refresh' ) );
	}

	public function enqueue_scripts() {
		wp_enqueue_style(
			'shane-weather-style',
			SHANE_WEATHER_URL . 'assets/style.css',
			array(),
			'1.0.0'
		);

		wp_enqueue_script(
			'shane-weather-refresh',
			SHANE_WEATHER_URL . 'assets/weather-refresh.js',
			array(),
			'1.0.0',
			true
		);

		wp_localize_script(
			'shane-weather-refresh',
			'shaneWeather',
			array(
				'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
				'enableIntro' => (int) get_option( 'shane_weather_enable_intro', 1 ),
			)
		);
	}

	public function get_background( $condition ) {
		$condition = strtolower( $condition );

		if ( strpos( $condition, 'thunder' ) !== false ) {
			return 'thunder.webp';
		}

		if ( strpos( $condition, 'rain' ) !== false ) {
			return 'rainy.webp';
		}

		if ( strpos( $condition, 'cloud' ) !== false ) {
			return 'cloudy.webp';
		}

		if ( strpos( $condition, 'sun' ) !== false || strpos( $condition, 'clear' ) !== false ) {
			return 'sunny.webp';
		}

		return 'sunny.webp';
	}

	public function render_weather_html( $data, $is_intro = false ) {
		if ( ! is_array( $data ) ) {
			return '<p>' . esc_html( $data ) . '</p>';
		}

		$temp      = $data['current']['temp_c'];
		$condition = $data['current']['condition']['text'];
		$icon      = $data['current']['condition']['icon'];
		$city      = $data['location']['name'];

		$background_image = $this->get_background( $condition );
		$background_url   = SHANE_WEATHER_URL . 'assets/images/' . $background_image;
		$icon_url         = 'https:' . $icon;
		$size_class       = $is_intro ? ' shane-weather-box-intro' : '';

		$output  = '<div class="shane-weather-box' . esc_attr( $size_class ) . '" style="background-image: url(\'' . esc_url( $background_url ) . '\');">';
		$output .= '<h3>' . esc_html( $city ) . '</h3>';
		$output .= '<div class="shane-weather-main">';
		$output .= '<img src="' . esc_url( $icon_url ) . '" alt="' . esc_attr( $condition ) . '">';
		$output .= '<div class="shane-weather-info">';
		$output .= '<p class="temp">' . esc_html( $temp ) . '°C</p>';
		$output .= '<p class="condition">' . esc_html( $condition ) . '</p>';
		$output .= '</div>';
		$output .= '</div>';
		$output .= '</div>';

		return $output;
	}

	public function display() {
		$data         = $this->api->get_weather_data();
		$enable_intro = get_option( 'shane_weather_enable_intro', 1 );

		if ( ! $enable_intro ) {
			return '<div id="shane-weather-wrapper">' . $this->render_weather_html( $data, false ) . '</div>';
		}

		$intro_card  = '<div id="shane-weather-intro" class="shane-weather-intro">';
		$intro_card .= $this->render_weather_html( $data, true );
		$intro_card .= '</div>';

		$main_card  = '<div id="shane-weather-wrapper" class="shane-weather-hidden-initially">';
		$main_card .= $this->render_weather_html( $data, false );
		$main_card .= '</div>';

		return $intro_card . $main_card;
	}

	public function ajax_refresh() {
		$data = $this->api->get_weather_data();

		echo $this->render_weather_html( $data, false );
		wp_die();
	}
}