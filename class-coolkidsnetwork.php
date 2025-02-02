<?php
/**
 * Cool Kids Network - Main Plugin Class
 *
 * Handles user roles, admin pages, and API endpoints.
 *
 * @package CoolKidsNetwork
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; /** Prevent direct access. */
}

/**
 * Class CoolKidsNetwork
 */
class CoolKidsNetwork {
	public function __construct() {
		/** Hooks and actions */
		add_action( 'init', array( $this, 'register_roles' ) );
		add_action( 'rest_api_init', array( $this, 'register_api_routes' ) );
		add_action( 'admin_menu', array( $this, 'add_admin_page' ) ); /** Admin page */
		add_shortcode( 'cool_kids_registration', array( $this, 'registration_form' ) );
		add_shortcode( 'cool_kids_login', array( $this, 'login_form' ) );
		add_shortcode( 'cool_kids_character', array( $this, 'character_data' ) );
		add_shortcode( 'cool_kids_all_characters', array( $this, 'all_characters_data' ) );
	}

	/**
	 * Register custom roles
	 */
	public function register_roles() {
		add_role( 'cool_kid', 'Cool Kid', array( 'read' => true ) );
		add_role(
			'cooler_kid',
			'Cooler Kid',
			array(
				'read'             => true,
				'view_users_basic' => true,
			)
		);
		add_role(
			'coolest_kid',
			'Coolest Kid',
			array(
				'read'                => true,
				'view_users_basic'    => true,
				'view_users_advanced' => true,
			)
		);
	}

	/**
	 * Register REST API routes
	 */
	public function register_api_routes() {
		register_rest_route(
			'cool-kids/v1',
			'/update-role',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'update_user_role' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			)
		);
	}

	/**
	 * Handle role updates via REST API
	 */
	public function update_user_role( WP_REST_Request $request ) {
		$email      = sanitize_email( $request->get_param( 'email' ) );
		$first_name = sanitize_text_field( $request->get_param( 'first_name' ) );
		$last_name  = sanitize_text_field( $request->get_param( 'last_name' ) );
		$new_role   = sanitize_text_field( $request->get_param( 'role' ) );

		if ( ! in_array( $new_role, array( 'cool_kid', 'cooler_kid', 'coolest_kid' ), true ) ) {
			return new WP_Error( 'invalid_role', 'Invalid role specified.', array( 'status' => 400 ) );
		}

		$user = get_user_by( 'email', $email );
		if ( ! $user && $first_name && $last_name ) {
			$users = get_users(
				array(
					'meta_query' => array(
						'relation' => 'AND',
						array(
							'key'     => 'first_name',
							'value'   => $first_name,
							'compare' => '=',
						),
						array(
							'key'     => 'last_name',
							'value'   => $last_name,
							'compare' => '=',
						),
					),
				)
			);
			$user  = ! empty( $users ) ? $users[0] : null;
		}

		if ( ! $user ) {
			return new WP_Error( 'user_not_found', 'No user found.', array( 'status' => 404 ) );
		}

		wp_update_user(
			array(
				'ID'   => $user->ID,
				'role' => $new_role,
			)
		);
		return rest_ensure_response( array( 'message' => 'User role updated successfully.' ) );
	}

	/**
	 * Add an admin page for assigning roles
	 */
	public function add_admin_page() {
		add_menu_page(
			'Assign User Roles',
			'Cool Kids Roles',
			'manage_options',
			'cool-kids-roles',
			array( $this, 'render_admin_page' ),
			'dashicons-admin-users',
			20
		);
	}

	/**
	 * Render the admin page
	 */
	public function render_admin_page() {
		if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['cool_kids_assign_role'] ) ) {
			check_admin_referer( 'cool_kids_form' ); /** Verify nonce */
			$this->assign_role();
		}

		$plugin_roles = array( 'cool_kid', 'cooler_kid', 'coolest_kid' );
		$users        = get_users( array( 'role__in' => $plugin_roles ) );
		?>
		<div class="wrap">
			<h1>Assign Roles to Cool Kids</h1>
			<form method="POST">
				<?php wp_nonce_field( 'cool_kids_form' ); ?>
				<table class="form-table">
					<tr>
						<th scope="row"><label for="user_id">Select User</label></th>
						<td>
							<select name="user_id" id="user_id" required>
								<option value="">-- Select a User --</option>
								<?php foreach ( $users as $user ) : ?>
									<option value="<?php echo esc_attr( $user->ID ); ?>">
										<?php echo esc_html( $user->display_name . " ({$user->user_email})" ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="new_role">Select Role</label></th>
						<td>
							<select name="new_role" id="new_role" required>
								<option value="">-- Select a Role --</option>
								<option value="cool_kid">Cool Kid</option>
								<option value="cooler_kid">Cooler Kid</option>
								<option value="coolest_kid">Coolest Kid</option>
							</select>
						</td>
					</tr>
				</table>
				<?php submit_button( 'Assign Role', 'primary', 'cool_kids_assign_role' ); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Handle role assignment from the admin page
	 */
	private function assign_role() {
		$user_id     = intval( $_POST['user_id'] );
		$new_role    = sanitize_text_field( $_POST['new_role'] );
		$valid_roles = array( 'cool_kid', 'cooler_kid', 'coolest_kid' );

		if ( ! $user_id || ! in_array( $new_role, $valid_roles, true ) ) {
			echo "<div class='error notice'><p>Invalid user or role.</p></div>";
			return;
		}

		$user = get_user_by( 'ID', $user_id );
		if ( $user ) {
			wp_update_user(
				array(
					'ID'   => $user_id,
					'role' => $new_role,
				)
			);
			echo "<div class='updated notice'><p>Role updated successfully for " . esc_html( $user->display_name ) . '.</p></div>';
		} else {
			echo "<div class='error notice'><p>User not found.</p></div>";
		}
	}

	/**
	 * Registration form shortcode
	 */
	public function registration_form() {
		ob_start();
		?>
		<form id="cool-kids-signup" method="post">
			<?php wp_nonce_field( 'cool_kids_register', 'cool_kids_nonce' ); ?>
			<label for="email">Email Address:</label>
			<input type="email" id="email" name="email" required>
			<button type="submit">Confirm</button>
		</form>
		<?php

		if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['email'] ) ) {
			/** Verify nonce for security */
			if ( ! isset( $_POST['cool_kids_nonce'] ) || ! wp_verify_nonce( $_POST['cool_kids_nonce'], 'cool_kids_register' ) ) {
				wp_die( 'Security check failed ! ' );
			}

			$email = sanitize_email( wp_unslash( $_POST['email'] ) ); /** Unsanitize for security */

			if ( ! email_exists( $email ) ) {
				$random_data = CharacterManager::generate_random_user();
				if ( $random_data ) {
					$user_id = wp_insert_user(
						array(
							'user_login' => $email,
							'user_email' => $email,
							'role'       => 'cool_kid',
						)
					);

					if ( ! is_wp_error( $user_id ) ) {
						update_user_meta( $user_id, 'first_name', $random_data['first_name'] );
						update_user_meta( $user_id, 'last_name', $random_data['last_name'] );
						update_user_meta( $user_id, 'country', $random_data['country'] );
						echo '<p>' . esc_html__( 'Registration successful !  Welcome, Cool Kid ! ', 'cool-kids' ) . '</p>';
					} else {
						echo '<p>' . esc_html__( 'Error creating user: ', 'cool-kids' ) . esc_html( $user_id->get_error_message() ) . '</p>';
					}
				}
			} else {
				echo '<p>' . esc_html__( 'Email is already registered ! ', 'cool-kids' ) . '</p>';
			}
		}
		return ob_get_clean();
	}


	/**
	 * Login form shortcode
	 */
	public function login_form() {
		ob_start();
		?>
		<form id="cool-kids-login" method="post">
			<?php wp_nonce_field( 'cool_kids_login', 'cool_kids_nonce' ); ?>
			<label for="email">Email Address:</label>
			<input type="email" id="email" name="email" required>
			<button type="submit">Login</button>
		</form>
		<?php

		if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['email'] ) ) {
			if ( ! isset( $_POST['cool_kids_nonce'] ) || ! wp_verify_nonce( $_POST['cool_kids_nonce'], 'cool_kids_login' ) ) {
				wp_die( 'Security check failed ! ' );
			}

			$email = sanitize_email( wp_unslash( $_POST['email'] ) );
			$user  = get_user_by( 'email', $email );

			if ( $user ) {
				wp_set_current_user( $user->ID );
				wp_set_auth_cookie( $user->ID );
				wp_safe_redirect( home_url( '/profile' ) );
				exit;
			} else {
				echo '<p>' . esc_html__( 'No account found with that email ! ', 'cool-kids' ) . '</p>';
			}
		}

		return ob_get_clean();
	}

	/**
	 * Display character data
	 */
	public function character_data() {
		if ( ! is_user_logged_in() ) {
			return '<p>' . esc_html__( 'Please log in to see your character data.', 'cool-kids' ) . '</p>';
		}

		$current_user = wp_get_current_user();
		$first_name   = get_user_meta( $current_user->ID, 'first_name', true );
		$last_name    = get_user_meta( $current_user->ID, 'last_name', true );
		$country      = get_user_meta( $current_user->ID, 'country', true );

		$role_map = array(
			'cool_kid'    => 'Cool Kid',
			'cooler_kid'  => 'Cooler Kid',
			'coolest_kid' => 'Coolest Kid',
		);

		$user_roles     = $current_user->roles;
		$formatted_role = isset( $role_map[ $user_roles[0] ] ) ? $role_map[ $user_roles[0] ] : ucfirst( $user_roles[0] );

		return '<p><strong>' . esc_html__( 'Name:', 'cool-kids' ) . '</strong> ' . esc_html( $first_name . ' ' . $last_name ) . '</p>
				<p><strong>' . esc_html__( 'Country:', 'cool-kids' ) . '</strong> ' . esc_html( $country ) . '</p>
				<p><strong>' . esc_html__( 'Email:', 'cool-kids' ) . '</strong> ' . esc_html( $current_user->user_email ) . '</p>
				<p><strong>' . esc_html__( 'Role:', 'cool-kids' ) . '</strong> ' . esc_html( $formatted_role ) . '</p>';
	}

	/**
	 * Display all user data based on roles
	 */
	public function all_characters_data() {
		if ( ! is_user_logged_in() ) {
			return '<p>' . esc_html__( 'Please log in to view user data.', 'cool-kids' ) . '</p>';
		}

		$current_user = wp_get_current_user();

		if ( in_array( 'cool_kid', $current_user->roles, true ) ) {
			return '<p>' . esc_html__( "You don't have permission to view this data.", 'cool-kids' ) . '</p>';
		}

		$plugin_roles = array( 'cool_kid', 'cooler_kid', 'coolest_kid' );
		$users        = get_users( array( 'role__in' => $plugin_roles ) );

		$output = '<table><thead><tr><th>' . esc_html__( 'Name', 'cool-kids' ) . '</th><th>' . esc_html__( 'Country', 'cool-kids' ) . '</th>';

		if ( in_array( 'coolest_kid', $current_user->roles, true ) ) {
			$output .= '<th>' . esc_html__( 'Email', 'cool-kids' ) . '</th><th>' . esc_html__( 'Role', 'cool-kids' ) . '</th>';
		}

		$output .= '</tr></thead><tbody>';

		foreach ( $users as $user ) {
			$first_name = esc_html( get_user_meta( $user->ID, 'first_name', true ) );
			$last_name  = esc_html( get_user_meta( $user->ID, 'last_name', true ) );
			$country    = esc_html( get_user_meta( $user->ID, 'country', true ) );

			$output .= "<tr><td>{$first_name} {$last_name}</td><td>{$country}</td>";

			if ( in_array( 'coolest_kid', $current_user->roles, true ) ) {
				$output .= '<td>' . esc_html( $user->user_email ) . '</td><td>' . esc_html( ucfirst( $user->roles[0] ) ) . '</td>';
			}

			$output .= '</tr>';
		}

		$output .= '</tbody></table>';

		return $output;
	}
}

class CharacterManager {
	public static function generate_random_user() {
		$response = wp_remote_get( 'https://randomuser.me/api/' );
		if ( is_wp_error( $response ) ) {
			error_log( 'Error fetching random user: ' . $response->get_error_message() );
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