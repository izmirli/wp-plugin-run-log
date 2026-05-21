<?php
/**
 * Class ShortcodesTest
 *
 * @package Run_Log
 */

class ShortcodesTest extends WP_UnitTestCase {

	/**
	 * Set up test environment before each test.
	 */
	public function set_up() {
		parent::set_up();

		// Clean up existing run-log options
		delete_option( 'oi-run-log-options' );
	}

	/**
	 * Helper function to create a run post with meta values.
	 *
	 * @param float  $distance The run distance.
	 * @param string $duration The run duration (hh:mm:ss).
	 * @param int    $elevation The elevation gain.
	 * @param int    $calories The calories burned.
	 * @param string $date Optional post date (YYYY-MM-DD HH:MM:SS).
	 * @return int The post ID.
	 */
	private function create_run_post( $distance, $duration, $elevation, $calories, $date = null ) {
		$args = array(
			'post_type'   => 'oi_run_log_post',
			'post_status' => 'publish',
		);

		if ( $date ) {
			$args['post_date'] = $date;
		}

		$post_id = $this->factory->post->create( $args );

		update_post_meta( $post_id, 'oirl-mb-distance', $distance );
		update_post_meta( $post_id, 'oirl-mb-duration', $duration );
		update_post_meta( $post_id, 'oirl-mb-elevation', $elevation );
		update_post_meta( $post_id, 'oirl-mb-calories', $calories );

		return $post_id;
	}

	/**
	 * Test the default shortcode output with no runs.
	 */
	public function test_shortcode_with_no_runs() {
		// Default options (km, pace, light theme)
		$options = array(
			'distance_unit' => 'km',
			'pace_or_speed' => 'pace',
			'style_theme'   => 'light',
		);
		update_option( 'oi-run-log-options', $options );

		$output = do_shortcode( '[oirl_total]' );

		// When there are no posts, we expect totals to be 0 or empty/default formatted values
		$this->assertStringContainsString( 'class="oirl oirl-light oirl-data-box"', $output );
		$this->assertStringContainsString( 'Total distance', $output );
		$this->assertStringContainsString( '0.0', $output );
		$this->assertStringContainsString( 'Total duration', $output );
		$this->assertStringContainsString( '00:00:00', $output );
	}

	/**
	 * Test shortcode with multiple runs and standard options.
	 */
	public function test_shortcode_with_multiple_runs() {
		// Set options
		$options = array(
			'distance_unit' => 'km',
			'pace_or_speed' => 'pace',
			'style_theme'   => 'dark',
		);
		update_option( 'oi-run-log-options', $options );

		// Create two runs:
		// Run 1: 10.0 km, 00:50:00, 150m elevation, 700 calories
		// Run 2: 12.0 km, 01:10:00, 250m elevation, 800 calories
		$this->create_run_post( 10.0, '00:50:00', 150, 700 );
		$this->create_run_post( 12.0, '01:10:00', 250, 800 );

		// Test the full summary shortcode
		$output = do_shortcode( '[oirl_total]' );

		$this->assertStringContainsString( 'class="oirl oirl-dark oirl-data-box"', $output );
		$this->assertStringContainsString( 'Total distance</span> <span class="oirl-data-value">22.0</span>km', $output );
		$this->assertStringContainsString( 'Total duration</span> <span class="oirl-data-value">02:00:00</span>', $output );
		$this->assertStringContainsString( 'Average pace</span> <span class="oirl-data-value">5:27</span>min/km', $output );

		// Test shortcode with 'only="distance"' attribute
		$distance_only = do_shortcode( '[oirl_total only="distance"]' );
		$this->assertStringContainsString( 'class="oirl oirl-dark oirl-total-box"', $distance_only );
		$this->assertStringContainsString( 'Total distance', $distance_only );
		$this->assertStringContainsString( '<span>2</span>', $distance_only );
		$this->assertStringContainsString( '<span class="sub bold">.</span>', $distance_only );
		$this->assertStringContainsString( '<span>0</span>', $distance_only );
		$this->assertStringContainsString( '<span class="super">km</span>', $distance_only );

		// Test shortcode with 'only="time"' attribute
		$time_only = do_shortcode( '[oirl_total only="time"]' );
		$this->assertStringContainsString( 'Total duration', $time_only );
		$this->assertStringContainsString( '<span class="oirl-counter"><span>0</span></span><span class="oirl-counter"><span>2</span></span>:<span class="oirl-counter"><span>0</span></span><span class="oirl-counter"><span>0</span></span><span class="smaller sub">:<span class="oirl-counter"><span>0</span></span><span class="oirl-counter"><span>0</span></span></span>', $time_only );

		// Test shortcode with 'only="elevation"' attribute
		$elevation_only = do_shortcode( '[oirl_total only="elevation"]' );
		$this->assertStringContainsString( 'Total elevation', $elevation_only );
		$this->assertStringContainsString( '<span class="oirl-counter"><span>4</span></span><span class="oirl-counter"><span>0</span></span><span class="oirl-counter"><span>0</span></span>', $elevation_only );
		$this->assertStringContainsString( '<span class="super">m</span>', $elevation_only );

		// Test shortcode with 'only="calories"' attribute
		$calories_only = do_shortcode( '[oirl_total only="calories"]' );
		$this->assertStringContainsString( 'Total calories', $calories_only );
		$this->assertStringContainsString( '<span class="oirl-counter"><span>1</span></span><span class="oirl-counter"><span>5</span></span><span class="oirl-counter"><span>0</span></span><span class="oirl-counter"><span>0</span></span>', $calories_only );
	}

	/**
	 * Test shortcode with different units (Miles and Speed).
	 */
	public function test_shortcode_with_miles_and_speed() {
		// Set options to miles and speed
		$options = array(
			'distance_unit' => 'mi',
			'pace_or_speed' => 'speed',
			'style_theme'   => 'light',
		);
		update_option( 'oi-run-log-options', $options );

		// Create run (in km)
		$this->create_run_post( 16.0934, '01:00:00', 100, 500 );

		$output = do_shortcode( '[oirl_total]' );

		$this->assertStringContainsString( 'Total distance</span> <span class="oirl-data-value">10.0</span>mi', $output );
		$this->assertStringContainsString( 'Average speed</span> <span class="oirl-data-value">10.00</span>mi/h', $output );

		// Check elevation in feet: 100 meters * 3.28084 = ~328 feet
		$elevation_only = do_shortcode( '[oirl_total only="elevation"]' );
		$this->assertStringContainsString( 'Total elevation', $elevation_only );
		$this->assertStringContainsString( '<span class="oirl-counter"><span>3</span></span><span class="oirl-counter"><span>2</span></span><span class="oirl-counter"><span>8</span></span>', $elevation_only );
		$this->assertStringContainsString( '<span class="super">ft</span>', $elevation_only );
	}

	/**
	 * Test shortcode year/month filtering.
	 */
	public function test_shortcode_date_filtering() {
		// Set options
		$options = array(
			'distance_unit' => 'km',
			'pace_or_speed' => 'pace',
			'style_theme'   => 'light',
		);
		update_option( 'oi-run-log-options', $options );

		// Run 1: Year 2025, Month 05 - Distance 5.0 km
		// Run 2: Year 2026, Month 05 - Distance 10.0 km
		// Run 3: Year 2026, Month 06 - Distance 15.0 km
		$this->create_run_post( 5.0, '00:30:00', 50, 300, '2025-05-15 08:00:00' );
		$this->create_run_post( 10.0, '01:00:00', 100, 600, '2026-05-20 09:00:00' );
		$this->create_run_post( 15.0, '01:30:00', 150, 900, '2026-06-10 07:00:00' );

		// Filter for Year 2026
		$output_2026 = do_shortcode( '[oirl_total year="2026"]' );
		$this->assertStringContainsString( 'Total distance</span> <span class="oirl-data-value">25.0</span>km', $output_2026 );

		// Filter for Year 2026, Month 05
		$output_2026_05 = do_shortcode( '[oirl_total year="2026" month="5"]' );
		$this->assertStringContainsString( 'Total distance</span> <span class="oirl-data-value">10.0</span>km', $output_2026_05 );

		// Filter for Year 2025
		$output_2025 = do_shortcode( '[oirl_total year="2025"]' );
		$this->assertStringContainsString( 'Total distance</span> <span class="oirl-data-value">5.0</span>km', $output_2025 );
	}
}
