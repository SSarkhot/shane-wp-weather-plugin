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

/**
 * Add settings page under Settings menu.
 */
function shane_weather_add_settings_page() {
	add_options_page(
		'Shane Weather Settings',     // Page title
		'Shane Weather',              // Menu title
		'manage_options',             // Capability
		'shane-weather',              // Menu slug
		'shane_weather_render_settings_page' // Callback
	);
}
add_action( 'admin_menu', 'shane_weather_add_settings_page' );

/**
 * Register plugin setting.
 */
function shane_weather_register_settings() {
	register_setting(
		'shane_weather_settings_group',
		'shane_weather_default_city',
		array(
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => '',
		)
	);

	add_settings_section(
		'shane_weather_main_section',
		'Weather Settings',
		'shane_weather_main_section_callback',
		'shane-weather'
	);

	add_settings_field(
		'shane_weather_default_city_field',
		'Default City',
		'shane_weather_default_city_field_callback',
		'shane-weather',
		'shane_weather_main_section'
	);
    register_setting(
	'shane_weather_settings_group',
	'shane_weather_api_key',
	array(
		'sanitize_callback' => 'sanitize_text_field',
		'default' => '',
	)
);

add_settings_field(
	'shane_weather_api_key_field',
	'API Key',
	'shane_weather_api_key_field_callback',
	'shane-weather',
	'shane_weather_main_section'
);

register_setting(
	'shane_weather_settings_group',
	'shane_weather_enable_intro',
	array(
		'sanitize_callback' => 'absint',
		'default'           => 1,
	)
);

add_settings_field(
	'shane_weather_enable_intro_field',
	'Enable Intro Animation',
	'shane_weather_enable_intro_field_callback',
	'shane-weather',
	'shane_weather_main_section'
);

}
add_action( 'admin_init', 'shane_weather_register_settings' );

/**
 * Section description.
 */
function shane_weather_main_section_callback() {
	echo '<p>Set the default city for your weather plugin.</p>';
}

/**
 * Render the Default City field.
 */
function shane_weather_default_city_field_callback() {
	$value = get_option( 'shane_weather_default_city', '' );
	?>
	<input
		type="text"
		name="shane_weather_default_city"
		value="<?php echo esc_attr( $value ); ?>"
		class="regular-text"
		placeholder="e.g. Durban"
	/>
	<?php
}

/**
 * Render the settings page.
 */
function shane_weather_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1>Shane Weather Settings</h1>
		<form action="options.php" method="post">
			<?php
			settings_fields( 'shane_weather_settings_group' );
			do_settings_sections( 'shane-weather' );
			submit_button( 'Save Settings' );
			?>
		</form>
	</div>
	<?php
}

function shane_weather_api_key_field_callback() {
	$value = get_option( 'shane_weather_api_key', '' );
	?>
	<input
		type="text"
		name="shane_weather_api_key"
		value="<?php echo esc_attr( $value ); ?>"
		class="regular-text"
		placeholder="Enter your API key"
	/>
	<?php
}

function shane_weather_enable_intro_field_callback() {
	$value = get_option( 'shane_weather_enable_intro', 1 );
	?>
	<label>
		<input
			type="checkbox"
			name="shane_weather_enable_intro"
			value="1"
			<?php checked( 1, $value ); ?>
		/>
		Enable intro animation
	</label>
	<?php
}

function shane_weather_get_weather_data() {

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

	// Cache for 15 minutes
	set_transient( 'shane_weather_data', $data, 15 * MINUTE_IN_SECONDS );

	return $data;
}

/**
 * Resets Cache upon Save
 */

add_action( 'update_option_shane_weather_default_city', 'shane_weather_clear_cache' );
add_action( 'update_option_shane_weather_api_key', 'shane_weather_clear_cache' );

function shane_weather_clear_cache() {
	delete_transient( 'shane_weather_data' );
}

/**
 * Ajax loading
 */

function shane_weather_render_weather_html( $data, $is_intro = false ) {
	if ( ! is_array( $data ) ) {
		return '<p>' . esc_html( $data ) . '</p>';
	}

	$temp      = $data['current']['temp_c'];
	$condition = $data['current']['condition']['text'];
	$icon      = $data['current']['condition']['icon'];
	$city      = $data['location']['name'];

	$background_image = shane_weather_get_background( $condition );
	$background_url   = plugin_dir_url( __FILE__ ) . 'assets/images/' . $background_image;
	$icon_url         = 'https:' . $icon;

	$size_class = $is_intro ? ' shane-weather-box-intro' : '';

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

function shane_weather_ajax_refresh() {
	$data = shane_weather_get_weather_data();

	echo shane_weather_render_weather_html( $data );
	wp_die();
}

add_action( 'wp_ajax_shane_weather_refresh', 'shane_weather_ajax_refresh' );
add_action( 'wp_ajax_nopriv_shane_weather_refresh', 'shane_weather_ajax_refresh' );

function shane_weather_display() {
	$data         = shane_weather_get_weather_data();
	$enable_intro = get_option( 'shane_weather_enable_intro', 1 );

	if ( ! $enable_intro ) {
		return '<div id="shane-weather-wrapper">' . shane_weather_render_weather_html( $data, false ) . '</div>';
	}

	$intro_card  = '<div id="shane-weather-intro" class="shane-weather-intro">';
	$intro_card .= shane_weather_render_weather_html( $data, true );
	$intro_card .= '</div>';

	$main_card  = '<div id="shane-weather-wrapper" class="shane-weather-hidden-initially">';
	$main_card .= shane_weather_render_weather_html( $data, false );
	$main_card .= '</div>';

	return $intro_card . $main_card;
}

function shane_weather_get_background( $condition ) {

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

	// default fallback
	return 'sunny.webp';
}



add_shortcode( 'shane_weather', 'shane_weather_display' );

add_action( 'wp_enqueue_scripts', 'shane_weather_styles' );

function shane_weather_styles() {
	wp_enqueue_style(
		'shane-weather-style',
		plugin_dir_url( __FILE__ ) . 'assets/style.css'
	);
}

/**
 * Register the weather widget.
 */
function shane_weather_register_widget() {
	register_widget( 'Shane_Weather_Widget' );
}
add_action( 'widgets_init', 'shane_weather_register_widget' );

/**
 * Weather widget class.
 */
class Shane_Weather_Widget extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'shane_weather_widget',
			'Shane Weather Widget',
			array(
				'description' => 'Displays the current weather.',
			)
		);
	}

	/**
	 * Front-end display of widget.
	 */
	public function widget( $args, $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : 'Current Weather';

		echo $args['before_widget'];

		if ( ! empty( $title ) ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
		}

		echo shane_weather_display();

		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 */
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : 'Current Weather';
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
				Title:
			</label>
			<input
				class="widefat"
				id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
				name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
				type="text"
				value="<?php echo esc_attr( $title ); ?>"
			>
		</p>
		<?php
	}

	/**
	 * Save widget options.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ! empty( $new_instance['title'] )
			? sanitize_text_field( $new_instance['title'] )
			: '';

		return $instance;
	}
}
function shane_weather_enqueue_scripts() {
	wp_enqueue_script(
		'shane-weather-refresh',
		plugin_dir_url( __FILE__ ) . 'assets/weather-refresh.js',
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
add_action( 'wp_enqueue_scripts', 'shane_weather_enqueue_scripts' );
add_action( 'wp_enqueue_scripts', 'shane_weather_enqueue_scripts' );