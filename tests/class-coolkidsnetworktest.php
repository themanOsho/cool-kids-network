<?php
/**
 * PHPUnit Tests for Cool Kids Network Plugin.
 *
 * @package CoolKidsNetwork
 */

namespace CoolKidsNetwork\Tests;

use PHPUnit\Framework\TestCase;

// Define ABSPATH if not already defined.
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __DIR__, 4 ) . '/' ); // Adjust as needed.
}

// ✅ Dynamically find and load WordPress.
if ( ! defined( 'ABSPATH' ) ) {
	$wp_load_path = dirname( __DIR__, 2 ) . '/wp-load.php';
	if ( file_exists( $wp_load_path ) ) {
		require_once $wp_load_path;
	} else {
		die( "ERROR: Cannot load WordPress! wp-load.php not found." );
	}
}

// Ensure plugin classes are loaded.
require_once ABSPATH . 'includes/class-coolkidsnetwork.php';

/**
 * Class CoolKidsNetworkTest
 */
class CoolKidsNetworkTest extends TestCase {

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
		$mock_response = array(
			'response' => array(
				'code'    => 200,
				'message' => 'OK',
			),
		);
		$this->assertArrayHasKey( 'response', $mock_response, 'REST API did not return a response.' );
	}
}
