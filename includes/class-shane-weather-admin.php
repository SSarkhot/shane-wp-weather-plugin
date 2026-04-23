<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Shane_Weather_Admin {

	public function init() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	public function add_settings_page() {
		add_options_page(
			'Shane Weather Settings',
			'Shane Weather',
			'manage_options',
			'shane-weather',
			array( $this, 'render_settings_page' )
		);
	}

	public function register_settings() {
		register_setting(
			'shane_weather_settings_group',
			'shane_weather_default_city',
			array(
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
			)
		);

		register_setting(
			'shane_weather_settings_group',
			'shane_weather_api_key',
			array(
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
			)
		);

		register_setting(
			'shane_weather_settings_group',
			'shane_weather_enable_intro',
			array(
				'sanitize_callback' => 'absint',
				'default'           => 1,
			)
		);

		add_settings_section(
			'shane_weather_main_section',
			'Weather Settings',
			array( $this, 'main_section_callback' ),
			'shane-weather'
		);

		add_settings_field(
			'shane_weather_default_city_field',
			'Default City',
			array( $this, 'default_city_field_callback' ),
			'shane-weather',
			'shane_weather_main_section'
		);

		add_settings_field(
			'shane_weather_api_key_field',
			'API Key',
			array( $this, 'api_key_field_callback' ),
			'shane-weather',
			'shane_weather_main_section'
		);

		add_settings_field(
			'shane_weather_enable_intro_field',
			'Enable Intro Animation',
			array( $this, 'enable_intro_field_callback' ),
			'shane-weather',
			'shane_weather_main_section'
		);
	}

	public function main_section_callback() {
		echo '<p>Set the default city and API settings for your weather plugin.</p>';
	}

	public function default_city_field_callback() {
		$value = get_option( 'shane_weather_default_city', '' );
		?>
		<input type="text" name="shane_weather_default_city" value="<?php echo esc_attr( $value ); ?>" class="regular-text" />
		<?php
	}

	public function api_key_field_callback() {
		$value = get_option( 'shane_weather_api_key', '' );
		?>
		<input type="text" name="shane_weather_api_key" value="<?php echo esc_attr( $value ); ?>" class="regular-text" />
		<?php
	}

	public function enable_intro_field_callback() {
		$value = get_option( 'shane_weather_enable_intro', 1 );
		?>
		<label>
			<input type="checkbox" name="shane_weather_enable_intro" value="1" <?php checked( 1, $value ); ?> />
			Enable intro animation
		</label>
		<?php
	}

	public function render_settings_page() {
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
}