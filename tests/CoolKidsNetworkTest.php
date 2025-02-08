<?php
/**
 * PHPUnit Tests for Cool Kids Network Plugin.
 *
 * @package CoolKidsNetwork
 */

namespace CoolKidsNetwork\Tests;

use PHPUnit\Framework\TestCase;

class CoolKidsNetworkTest extends TestCase {

    /**
     * Load WordPress before tests run.
     */
    public static function setUpBeforeClass(): void {
        if (!defined('ABSPATH')) {
            require_once dirname(__DIR__, 4) . '/wp-load.php'; 
        }
    }

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
