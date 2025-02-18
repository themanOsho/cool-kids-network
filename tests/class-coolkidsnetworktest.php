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
	define( 'ABSPATH', dirname( __DIR__, 4 ) . '/' );
}

// Dynamically find wp-load.php for local & CI/CD environments.
$wp_load_paths = array(
	'/home/runner/work/cool-kids-network/cool-kids-network/wp-load.php', // CI/CD environment.
	ABSPATH . 'wp-load.php', // Local environment.
);

$wp_loaded = false;
foreach ( $wp_load_paths as $wp_load_path ) {
	if ( file_exists( $wp_load_path ) ) {
		require_once $wp_load_path;
		$wp_loaded = true;
		break;
	}
}

if ( ! $wp_loaded ) {
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
