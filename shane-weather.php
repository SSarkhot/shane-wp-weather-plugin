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