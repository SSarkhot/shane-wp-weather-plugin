<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Shane_Weather_Widget {

	private $frontend;

	public function __construct( $frontend ) {
		$this->frontend = $frontend;
	}

	public function init() {
		add_action( 'widgets_init', array( $this, 'register_widget' ) );
	}

	public function register_widget() {
		$frontend = $this->frontend;

		register_widget(
			new class( $frontend ) extends WP_Widget {
				private $frontend;

				public function __construct( $frontend ) {
					$this->frontend = $frontend;

					parent::__construct(
						'shane_weather_widget',
						'Shane Weather Widget',
						array(
							'description' => 'Displays the current weather.',
						)
					);
				}

				public function widget( $args, $instance ) {
					$title = ! empty( $instance['title'] ) ? $instance['title'] : 'Current Weather';

					echo $args['before_widget'];

					if ( ! empty( $title ) ) {
						echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
					}

					echo $this->frontend->display();

					echo $args['after_widget'];
				}

				public function form( $instance ) {
					$title = ! empty( $instance['title'] ) ? $instance['title'] : 'Current Weather';
					?>
					<p>
						<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">Title:</label>
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

				public function update( $new_instance, $old_instance ) {
					$instance          = array();
					$instance['title'] = ! empty( $new_instance['title'] ) ? sanitize_text_field( $new_instance['title'] ) : '';
					return $instance;
				}
			}
		);
	}
}