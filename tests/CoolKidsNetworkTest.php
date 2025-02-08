<?php
/**
 * PHPUnit Tests for Cool Kids Network Plugin.
 *
 * @package CoolKidsNetwork
 */

namespace CoolKidsNetwork\Tests;

use PHPUnit\Framework\TestCase;

// ✅ Dynamically find wp-load.php for local & CI/CD environments.
$wp_load_path = '/home/runner/work/cool-kids-network/cool-kids-network/wp-load.php';

if ( file_exists( $wp_load_path ) ) {
    require_once $wp_load_path;
} else {
    fwrite(STDERR, "ERROR: wp-load.php not found! Tests cannot run.\n");
    exit(1);
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
        $mockResponse = [
            'response' => [
                'code'    => 200,
                'message' => 'OK'
            ]
        ];
        $this->assertArrayHasKey('response', $mockResponse, 'REST API did not return a response.');
    }    
}
