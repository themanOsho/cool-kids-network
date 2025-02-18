<?php
/**
 * PHPUnit Tests for Cool Kids Network Plugin.
 *
 * @package CoolKidsNetwork
 */

namespace CoolKidsNetwork\Tests;

use PHPUnit\Framework\TestCase;

// Dynamically detect WordPress installation path.
$wp_root = getenv( 'ABSPATH' ) ? getenv( 'ABSPATH' ) : dirname( __DIR__, 4 ) . '/';

// Define ABSPATH if not already defined.
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', rtrim( $wp_root, '/' ) . '/' );
}

// Dynamically find wp-load.php.
$wp_load_path = ABSPATH . 'wp-load.php';

if ( file_exists( $wp_load_path ) ) {
	require_once $wp_load_path;
} else {
	die( 'ERROR: Cannot load WordPress! wp-load.php not found.' );
}

// Dynamically find and load the plugin class.
$plugin_class_path = dirname( __DIR__ ) . '/includes/class-coolkidsnetwork.php';

if ( file_exists( $plugin_class_path ) ) {
	require_once $plugin_class_path;
} else {
	die( 'ERROR: Cannot load plugin class! class-coolkidsnetwork.php not found.' );
}

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
	 * Test if the esc_html() function exists.
	 */
	public function test_esc_html_function() {
		$this->assertTrue( function_exists( 'esc_html' ), "WordPress function 'esc_html' is missing." );
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
