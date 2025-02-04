<?php
/**
 * PHPUnit Tests for Cool Kids Network Plugin.
 *
 * @package CoolKidsNetwork
 */

use PHPUnit\Framework\TestCase;

/**
 * Class TestCoolKidsNetwork
 *
 * Tests the user role assignment and REST API endpoint.
 */
class TestCoolKidsNetwork extends TestCase {

	/**
	 * Test if the WordPress function wp_update_user exists.
	 */
	public function test_user_role_assignment() {
		$this->assertTrue( function_exists( 'wp_update_user' ), "WordPress function 'wp_update_user' is missing." );
	}

	/**
	 * Test if the REST API endpoint returns a response.
	 */
	public function test_rest_api_endpoint() {
		$response = wp_remote_get( 'http://localhost/wp-json/cool-kids/v1/update-role' );
		$this->assertArrayHasKey( 'response', $response, 'REST API did not return a response.' );
	}
}
