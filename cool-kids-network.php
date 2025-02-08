<?php
/**
 * Plugin Name: Cool Kids Network
 * Description: A WordPress plugin for managing user roles in the Cool Kids Network.
 * Version: 1.0
 * Author: Joshua Osho
 * Namespace: CoolKidsNetwork
 *
 * @package CoolKidsNetwork
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load Composer Autoloader.
require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

// Use the namespace.
use CoolKidsNetwork\coolkidsnetwork;

// Initialize the plugin.
new CoolKidsNetwork();
