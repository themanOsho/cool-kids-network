<?php
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
define( 'WP_DEBUG_DISPLAY', false );
// @ini_set( 'display_errors', 0 );

// Set the correct WordPress installation path.
define( 'ABSPATH', 'C:/xampp/htdocs/wp6.7.1/rankmath-CKN-test/' ); // Adjust this path.

// Include WordPress settings.
require_once ABSPATH . 'wp-settings.php';
