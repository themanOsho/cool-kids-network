<?php

use PHPUnit\Framework\TestCase;

class TestCoolKidsNetwork extends TestCase {
    public function test_user_role_assignment() {
        $this->assertTrue(function_exists('wp_update_user'), "WordPress function 'wp_update_user' is missing.");
    }

    public function test_rest_api_endpoint() {
        $response = wp_remote_get('http://localhost/wp-json/cool-kids/v1/update-role');
        $this->assertArrayHasKey('response', $response, "REST API did not return a response.");
    }
}
