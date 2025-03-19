<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register custom post type "Locations".
 */
function lm_register_location_post_type() {
    $labels = array(
        'name'               => 'Locations',
        'singular_name'      => 'Location',
        'menu_name'          => 'Locations',
        'name_admin_bar'     => 'Location',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Location',
        'new_item'           => 'New Location',
        'edit_item'          => 'Edit Location',
        'view_item'          => 'View Location',
        'all_items'          => 'All Locations',
        'search_items'       => 'Search Locations',
        'not_found'          => 'No locations found.',
        'not_found_in_trash' => 'No locations found in Trash.',
    );

    $args = array(
        'labels'       => $labels,
        'public'       => true,
        'has_archive'  => true,
        'show_in_menu' => 'location_map', // Attach under our custom top‑level menu.
        'supports'     => array( 'title', 'editor' ),
    );

    register_post_type( 'lm_location', $args );
}
add_action( 'init', 'lm_register_location_post_type' );
