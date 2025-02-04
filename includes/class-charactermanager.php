<?php
/**
 * CharacterManager Class
 *
 * Handles generating random user data from an external API.
 *
 * @package CoolKidsNetwork
 */

namespace CoolKidsNetwork;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class CharacterManager
 *
 * This class fetches random user data from an external API
 * and provides structured user information.
 */
class CharacterManager {
	/**
	 * Fetch random user data from API.
	 *
	 * @return array|null Random user data or null if an error occurs.
	 */
	public static function generate_random_user() {
		$response = wp_remote_get( 'https://randomuser.me/api/' );

		if ( is_wp_error( $response ) ) {
			return null;
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( $data && isset( $data['results'][0] ) ) {
			$user = $data['results'][0];
			return array(
				'first_name' => ucfirst( $user['name']['first'] ),
				'last_name'  => ucfirst( $user['name']['last'] ),
				'country'    => $user['location']['country'],
			);
		}

		return null;
	}
}
