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

// Load required class files.
require_once plugin_dir_path( __FILE__ ) . 'includes/class-charactermanager.php';
require_once plugin_dir_path( __FILE__ ) . 'class-coolkidsnetwork.php';

// Use the namespace.
use CoolKidsNetwork\CoolKidsNetwork;

// Initialize the plugin.
new CoolKidsNetwork();
