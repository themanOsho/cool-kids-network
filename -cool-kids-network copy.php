<?php
/*
Plugin Name: Cool Kids Network
Description: A WordPress plugin for the Cool Kids Network user management system.
Version: 1.0
Author: Joshua Osho
*/

// Hook to add a shortcode for the registration form
add_shortcode('cool_kids_registration', 'cool_kids_registration_form');

function cool_kids_registration_form() {
    ob_start(); ?>
    <form id="cool-kids-signup" method="post">
        <label for="email">Email Address:</label>
        <input type="email" id="email" name="email" required>
        <button type="submit">Confirm</button>
    </form>
    <?php
    // Process the form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
        $email = sanitize_email($_POST['email']);
        if (!email_exists($email)) {
            // Call RandomUser API to get fake data
            $random_data = cool_kids_generate_random_user();
            if ($random_data) {
                // Create a new user
                $user_id = wp_insert_user([
                    'user_login' => $email,
                    'user_email' => $email,
                    'role'       => 'cool_kid',
                ]);

                if (!is_wp_error($user_id)) {
                    // Store additional character data as user meta
                    update_user_meta($user_id, 'first_name', $random_data['first_name']);
                    update_user_meta($user_id, 'last_name', $random_data['last_name']);
                    update_user_meta($user_id, 'country', $random_data['country']);
                    echo "<p>Registration successful! Welcome, Cool Kid!</p>";
                } else {
                    echo "<p>Error creating user: " . $user_id->get_error_message() . "</p>";
                }
            }
        } else {
            echo "<p>Email is already registered!</p>";
        }
    }

    return ob_get_clean();
}

// Helper function to fetch random data
function cool_kids_generate_random_user() {
    $response = wp_remote_get('https://randomuser.me/api/');
    if (is_wp_error($response)) {
        return null;
    }
    $data = json_decode(wp_remote_retrieve_body($response), true);
    if ($data && isset($data['results'][0])) {
        $user = $data['results'][0];
        return [
            'first_name' => ucfirst($user['name']['first']),
            'last_name'  => ucfirst($user['name']['last']),
            'country'    => $user['location']['country'],
        ];
    }
    return null;
}

// Shortcode for login form
add_shortcode('cool_kids_login', 'cool_kids_login_form');

function cool_kids_login_form() {
    ob_start(); ?>
    <form id="cool-kids-login" method="post">
        <label for="email">Email Address:</label>
        <input type="email" id="email" name="email" required>
        <button type="submit">Login</button>
    </form>
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
        $email = sanitize_email($_POST['email']);
        $user = get_user_by('email', $email);
        if ($user) {
            wp_set_current_user($user->ID);
            wp_set_auth_cookie($user->ID);
            echo "<p>Login successful! Welcome back, " . esc_html($email) . ".</p>";
        } else {
            echo "<p>No account found with that email!</p>";
        }
    }
    return ob_get_clean();
}

// Shortcode to display character data
add_shortcode('cool_kids_character', 'cool_kids_character_data');

function cool_kids_character_data() {
    if (!is_user_logged_in()) {
        return "<p>Please log in to see your character data.</p>";
    }

    // Fetch logged-in user data
    $current_user = wp_get_current_user();
    $first_name = get_user_meta($current_user->ID, 'first_name', true);
    $last_name = get_user_meta($current_user->ID, 'last_name', true);
    $country = get_user_meta($current_user->ID, 'country', true);

    // Map roles to readable names
    $role_map = [
        'cool_kid' => 'Cool Kid',
        'cooler_kid' => 'Cooler Kid',
        'coolest_kid' => 'Coolest Kid',
    ];

    // Get the first role of the user
    $roles = $current_user->roles;
    $formatted_role = isset($role_map[$roles[0]]) ? $role_map[$roles[0]] : ucfirst($roles[0]);

    // Custom heading and data display
    return "<h1>My Character's Data</h1>
            <p><strong>Name:</strong> $first_name $last_name</p>
            <p><strong>Country:</strong> $country</p>
            <p><strong>Email:</strong> {$current_user->user_email}</p>
            <p><strong>Role:</strong> $formatted_role</p>";
}

// Shortcode for viewing all user data (restricted by roles)
add_shortcode('cool_kids_all_characters', 'cool_kids_all_characters');

function cool_kids_all_characters() {
    if (!is_user_logged_in()) {
        return "<p>Please log in to view user data.</p>";
    }

    $current_user = wp_get_current_user();
    if (in_array('cool_kid', $current_user->roles)) {
        return "<p>You don't have permission to view this data.</p>";
    }

    $users = get_users();
    $output = "<table><thead><tr><th>Name</th><th>Country</th>";
    if (in_array('coolest_kid', $current_user->roles)) {
        $output .= "<th>Email</th><th>Role</th>";
    }
    $output .= "</tr></thead><tbody>";

    foreach ($users as $user) {
        $first_name = get_user_meta($user->ID, 'first_name', true);
        $last_name = get_user_meta($user->ID, 'last_name', true);
        $country = get_user_meta($user->ID, 'country', true);

        $output .= "<tr><td>{$first_name} {$last_name}</td><td>{$country}</td>";
        if (in_array('coolest_kid', $current_user->roles)) {
            $roles = implode(', ', $user->roles);
            $output .= "<td>{$user->user_email}</td><td>{$roles}</td>";
        }
        $output .= "</tr>";
    }
    $output .= "</tbody></table>";

    return $output;
}

// Register REST API endpoint
add_action('rest_api_init', function () {
    register_rest_route('cool-kids/v1', '/update-role', [
        'methods' => 'POST',
        'callback' => 'cool_kids_update_role',
        'permission_callback' => function () {
            return current_user_can('manage_options'); // Only admin users
        },
    ]);
});

function cool_kids_update_role(WP_REST_Request $request) {
    $email = sanitize_email($request->get_param('email'));
    $first_name = sanitize_text_field($request->get_param('first_name'));
    $last_name = sanitize_text_field($request->get_param('last_name'));
    $new_role = sanitize_text_field($request->get_param('role'));

    if (!in_array($new_role, ['cool_kid', 'cooler_kid', 'coolest_kid'])) {
        return new WP_Error('invalid_role', 'Invalid role specified.', ['status' => 400]);
    }

    $user = get_user_by('email', $email);
    if (!$user && $first_name && $last_name) {
        $users = get_users([
            'meta_query' => [
                'relation' => 'AND',
                ['key' => 'first_name', 'value' => $first_name, 'compare' => '='],
                ['key' => 'last_name', 'value' => $last_name, 'compare' => '='],
            ],
        ]);
        $user = !empty($users) ? $users[0] : null;
    }

    if (!$user) {
        return new WP_Error('user_not_found', 'No user found.', ['status' => 404]);
    }

    wp_update_user(['ID' => $user->ID, 'role' => $new_role]);
    return rest_ensure_response(['message' => 'User role updated successfully.']);
}


// Register custom roles on plugin activation
register_activation_hook(__FILE__, 'cool_kids_register_roles');

function cool_kids_register_roles() {
    add_role('cool_kid', 'Cool Kid', [
        'read' => true,
    ]);

    add_role('cooler_kid', 'Cooler Kid', [
        'read' => true,
        'view_users_basic' => true, // Custom capability for viewing name and country
    ]);

    add_role('coolest_kid', 'Coolest Kid', [
        'read' => true,
        'view_users_basic' => true,
        'view_users_advanced' => true, // Custom capability for viewing email and role
    ]);
}