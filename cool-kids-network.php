<?php
/*
Plugin Name: Cool Kids Network
Description: A WordPress plugin for managing user roles in the Cool Kids Network.
Version: 1.0
Author: Joshua Osho
*/

/** Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Load the main class file.*/
require_once plugin_dir_path( __FILE__ ) . 'class-coolkidsnetwork.php';

/** Initialize the plugin.*/
new CoolKidsNetwork();
