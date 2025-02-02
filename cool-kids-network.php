<?php
/*
Plugin Name: Cool Kids Network
Description: A WordPress plugin for the Cool Kids Network user management system.
Version: 1.0
Author: Joshua Osho
*/

class CoolKidsNetwork {
	public function __construct() {
		// Hooks and actions
		add_action( 'init', array( $this, 'register_roles' ) );
		add_action( 'rest_api_init', array( $this, 'register_api_routes' ) );
		add_shortcode( 'cool_kids_registration', array( $this, 'registration_form' ) );
		add_shortcode( 'cool_kids_login', array( $this, 'login_form' ) );
		add_shortcode( 'cool_kids_character', array( $this, 'character_data' ) );
		add_shortcode( 'cool_kids_all_characters', array( $this, 'all_characters_data' ) );
		add_action( 'admin_menu', array( $this, 'add_admin_page' ) ); // Add admin menu
	}
	/**
	 * Add an admin page for assigning roles
	 */
	public function add_admin_page() {
		add_menu_page(
			'Assign User Roles',                // Page title
			'Cool Kids Roles',                  // Menu title
			'manage_options',                   // Capability
			'cool-kids-roles',                  // Menu slug
			array( $this, 'render_admin_page' ),       // Callback to display page
			'dashicons-admin-users',            // Menu icon
			20                                  // Menu position
		);
	}

	/**
	 * Render the admin page
	 */
	public function render_admin_page() {
		// Handle form submission
		if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['cool_kids_assign_role'] ) ) {
			$this->assign_role(); // Call role assignment method
		}

		// Fetch users with plugin-specific roles
		$plugin_roles = array( 'cool_kid', 'cooler_kid', 'coolest_kid' );
		$users        = get_users(
			array(
				'role__in' => $plugin_roles,
			)
		);

		// HTML form
		?>
		<div class="wrap">
			<h1>Assign Roles to Cool Kids</h1>
			<form method="POST">
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
	 * Handle role assignment
	 */
	private function assign_role() {
		// Sanitize and validate input
		$user_id     = intval( $_POST['user_id'] );
		$new_role    = sanitize_text_field( $_POST['new_role'] );
		$valid_roles = array( 'cool_kid', 'cooler_kid', 'coolest_kid' );

		if ( ! $user_id || ! in_array( $new_role, $valid_roles ) ) {
			echo "<div class='error notice'><p>Invalid user or role.</p></div>";
			return;
		}

		// Update the user's role
		$user = get_user_by( 'ID', $user_id );
		if ( $user ) {
			wp_update_user(
				array(
					'ID'   => $user_id,
					'role' => $new_role,
				)
			);
			echo "<div class='updated notice'><p>Role updated successfully for {$user->display_name}.</p></div>";
		} else {
			echo "<div class='error notice'><p>User not found.</p></div>";
		}
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
	 * Handle the REST API request to update user roles
	 */
	public function update_user_role( WP_REST_Request $request ) {
		$email      = sanitize_email( $request->get_param( 'email' ) );
		$first_name = sanitize_text_field( $request->get_param( 'first_name' ) );
		$last_name  = sanitize_text_field( $request->get_param( 'last_name' ) );
		$new_role   = sanitize_text_field( $request->get_param( 'role' ) );

		if ( ! in_array( $new_role, array( 'cool_kid', 'cooler_kid', 'coolest_kid' ) ) ) {
			error_log( "Invalid role: {$new_role}" );
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
			error_log( "User not found: {$email}" );
			return new WP_Error( 'user_not_found', 'No user found.', array( 'status' => 404 ) );
		}

		wp_update_user(
			array(
				'ID'   => $user->ID,
				'role' => $new_role,
			)
		);
		error_log( "Role updated for {$email} to {$new_role}" );
		return rest_ensure_response( array( 'message' => 'User role updated successfully.' ) );
	}

	/**
	 * Registration form shortcode
	 */
	public function registration_form() {
		ob_start();
		?>
		<form id="cool-kids-signup" method="post">
			<label for="email">Email Address:</label>
			<input type="email" id="email" name="email" required>
			<button type="submit">Confirm</button>
		</form>
		<?php
		if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['email'] ) ) {
			$email = sanitize_email( $_POST['email'] );
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
						echo '<p>Registration successful! Welcome, Cool Kid!</p>';
					} else {
						echo '<p>Error creating user: ' . $user_id->get_error_message() . '</p>';
					}
				}
			} else {
				echo '<p>Email is already registered!</p>';
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
			// Verify nonce for security
			if ( ! isset( $_POST['cool_kids_nonce'] ) || ! wp_verify_nonce( $_POST['cool_kids_nonce'], 'cool_kids_login' ) ) {
				wp_die( 'Security check failed!' );
			}

			$email = sanitize_email( wp_unslash( $_POST['email'] ) ); // Unsanitize input for security
			$user  = get_user_by( 'email', $email );

			if ( $user ) {
				wp_set_current_user( $user->ID );
				wp_set_auth_cookie( $user->ID );

				// Redirect to /profile page after successful login.
				wp_safe_redirect( home_url( '/profile' ) );
				exit;
			} else {
				echo '<p>No account found with that email!</p>';
			}
		}

		return ob_get_clean();
	}

	/**
	 * Display character data
	 */
	public function character_data() {
		if ( ! is_user_logged_in() ) {
			return '<p>Please log in to see your character data.</p>';
		}

		// Get the current logged-in user
		$current_user = wp_get_current_user();
		$first_name   = get_user_meta( $current_user->ID, 'first_name', true );
		$last_name    = get_user_meta( $current_user->ID, 'last_name', true );
		$country      = get_user_meta( $current_user->ID, 'country', true );

		// Map roles to readable labels
		$role_map = array(
			'cool_kid'    => 'Cool Kid',
			'cooler_kid'  => 'Cooler Kid',
			'coolest_kid' => 'Coolest Kid',
		);

		// Fetch the first role of the user (WordPress stores roles as an array)
		$user_roles     = $current_user->roles; // Returns an array of roles
		$formatted_role = isset( $role_map[ $user_roles[0] ] ) ? $role_map[ $user_roles[0] ] : ucfirst( $user_roles[0] );

		// Return the character data
		return "<p><strong>Name:</strong> $first_name $last_name</p>
                <p><strong>Country:</strong> $country</p>
                <p><strong>Email:</strong> {$current_user->user_email}</p>
                <p><strong>Role:</strong> $formatted_role</p>";
	}


	/**
	 * Display all user data based on roles
	 */
	public function all_characters_data() {
		if ( ! is_user_logged_in() ) {
			return '<p>Please log in to view user data.</p>';
		}

		$current_user = wp_get_current_user();

		// Restrict access for 'cool_kid' role
		if ( in_array( 'cool_kid', $current_user->roles ) ) {
			return "<p>You don't have permission to view this data.</p>";
		}

		// Define plugin-specific roles
		$plugin_roles = array( 'cool_kid', 'cooler_kid', 'coolest_kid' );

		// Fetch all users with plugin-specific roles
		$users = get_users(
			array(
				'role__in' => $plugin_roles,
			)
		);

		// Start the table
		$output = '<table><thead><tr><th>Name</th><th>Country</th>';

		// Add email and role columns if the user is 'coolest_kid'
		if ( in_array( 'coolest_kid', $current_user->roles ) ) {
			$output .= '<th>Email</th><th>Role</th>';
		}

		$output .= '</tr></thead><tbody>';

		// Map roles to readable labels
		$role_map = array(
			'cool_kid'    => 'Cool Kid',
			'cooler_kid'  => 'Cooler Kid',
			'coolest_kid' => 'Coolest Kid',
		);

		// Loop through users and output their data
		foreach ( $users as $user ) {
			$first_name = get_user_meta( $user->ID, 'first_name', true );
			$last_name  = get_user_meta( $user->ID, 'last_name', true );
			$country    = get_user_meta( $user->ID, 'country', true );

			$output .= "<tr><td>{$first_name} {$last_name}</td><td>{$country}</td>";

			// Show email and role only for 'coolest_kid'
			if ( in_array( 'coolest_kid', $current_user->roles ) ) {
				$roles          = $user->roles;
				$formatted_role = isset( $role_map[ $roles[0] ] ) ? $role_map[ $roles[0] ] : ucfirst( $roles[0] );
				$output        .= "<td>{$user->user_email}</td><td>{$formatted_role}</td>";
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



// Initialize the plugin
new CoolKidsNetwork();
