<?php
/**
 * Class CalculationsTest
 *
 * @package Run_Log
 */

class CalculationsTest extends WP_UnitTestCase {

	/**
	 * Test the iorl_calculate_pace function with various inputs.
	 */
	public function test_calculate_pace() {
		// Test standard pace calculation (time in hh:mm:ss format)
		$this->assertEquals( '5:00', iorl_calculate_pace( 10, '00:50:00', 'pace' ) );
		$this->assertEquals( '6:00', iorl_calculate_pace( 10, '01:00:00', 'pace' ) );

		// 21.1 km in 02:01:29
		$this->assertEquals( '5:45', iorl_calculate_pace( 21.1, '02:01:29', 'pace' ) );

		// Test speed calculation
		$this->assertEquals( '10.00', iorl_calculate_pace( 10, '01:00:00', 'speed' ) );
		$this->assertEquals( '10.78', iorl_calculate_pace( 42.2, '03:54:53', 'speed' ) );

		// Test invalid parameters
		$this->assertEquals( 0, iorl_calculate_pace( 0, '01:00:00' ) );
		$this->assertEquals( 0, iorl_calculate_pace( 10, 'invalid' ) );
		$this->assertEquals( 0, iorl_calculate_pace( 10, '01:00:00', 'invalid_type' ) );
	}

	/**
	 * Test the iorl_distance_converter function with various inputs.
	 */
	public function test_distance_converter() {
		$this->assertEquals( '16.09', iorl_distance_converter( 10, 'M2K' ) );
		$this->assertEquals( '6.21', iorl_distance_converter( 10, 'K2M' ) );
		$this->assertEquals( '32.81', iorl_distance_converter( 10, 'M2F' ) );
		$this->assertEquals( '3.05', iorl_distance_converter( 10, 'F2M' ) );

		// Test invalid parameters
		$this->assertEquals( 0, iorl_distance_converter( 0, 'M2K' ) );
		$this->assertEquals( 0, iorl_distance_converter( -5, 'M2K' ) );
		$this->assertEquals( 0, iorl_distance_converter( 'invalid', 'M2K' ) );
		$this->assertEquals( 0, iorl_distance_converter( 10, 'invalid_conversion' ) );
	}
}
