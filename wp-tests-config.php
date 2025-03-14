<?php
/**
 * PHPUnit Database Configuration for Cool Kids Network.
 *
 * This file defines the database settings for running PHPUnit tests
 * with a WordPress test environment.
 *
 * @package CoolKidsNetwork
 */

// Database settings for PHPUnit tests.
define( 'DB_NAME', 'wordpress_test' );
define( 'DB_USER', 'root' ); // Change if your MySQL user is different.
define( 'DB_PASSWORD', '' ); // Change if your MySQL password is different.
define( 'DB_HOST', 'localhost' );
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );

define( 'WP_TESTS_TABLE_PREFIX', 'wptests_' );

// Enable debugging.
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );

// Set the correct WordPress installation path.
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __DIR__, 1 ) . '/' ); // Adjust if needed.
}

// Include WordPress settings.
require_once ABSPATH . 'wp-settings.php';
