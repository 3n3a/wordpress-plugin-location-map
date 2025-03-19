<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Add top-level admin menu and sub-menu.
 */
function lm_add_admin_menu() {
    // Top-level menu.
    add_menu_page(
        'Location Map Settings',
        'Location Map',
        'manage_options',
        'location_map',
        'lm_render_settings_page',
        'dashicons-location',
        6
    );

    // Submenu for Locations (custom post type).
    add_submenu_page(
        'location_map',
        'Locations',
        'Locations',
        'manage_options',
        'edit.php?post_type=lm_location'
    );
}
add_action( 'admin_menu', 'lm_add_admin_menu' );

/**
 * Render the settings page.
 */
function lm_render_settings_page() {
    ?>
    <div class="wrap">
        <h1>Location Map Settings</h1>
        <p>Configure your plugin settings here.</p>
        <!-- Add settings form fields as needed -->
    </div>
    <?php
}
