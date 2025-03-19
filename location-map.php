<?php
/*
Plugin Name: Location Map
Description: A plugin to manage and display location maps with a Gutenberg block using Leaflet.js.
Version: 1.0
Author: 3n3a
*/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants.
define( 'LM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'LM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Include required files.
require_once LM_PLUGIN_DIR . 'includes/post-types.php';
require_once LM_PLUGIN_DIR . 'includes/meta-boxes.php';
require_once LM_PLUGIN_DIR . 'admin/settings-page.php';

/**
 * Enqueue admin scripts & styles for our map picker.
 */
function lm_admin_scripts( $hook ) {
    // Load only on our plugin pages or the location meta box.
    if ( strpos( $hook, 'location_map' ) !== false || in_array( $hook, array( 'post.php', 'post-new.php' ) ) ) {
        // Enqueue Leaflet assets from CDN.
        wp_enqueue_style( 'leaflet-css', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.css' );
        wp_enqueue_script( 'leaflet-js', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.js', array(), '1.7.1', true );

        // Enqueue our custom map picker script and styles.
        wp_enqueue_script( 'lm-map-picker', LM_PLUGIN_URL . 'admin/map-picker.js', array( 'jquery', 'leaflet-js' ), '1.0', true );
        wp_enqueue_style( 'lm-admin-style', LM_PLUGIN_URL . 'admin/admin-style.css' );
    }
}
add_action( 'admin_enqueue_scripts', 'lm_admin_scripts' );

/**
 * Enqueue frontend Leaflet assets.
 */
function lm_enqueue_frontend_leaflet() {
    wp_enqueue_style( 'leaflet-css', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.css' );
    wp_enqueue_script( 'leaflet-js', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.js', array(), '1.7.1', true );
}
add_action( 'wp_enqueue_scripts', 'lm_enqueue_frontend_leaflet' );

/**
 * Register Gutenberg block.
 */
function lm_register_block() {
    // Register block editor script.
    wp_register_script(
        'lm-block-editor',
        LM_PLUGIN_URL . 'blocks/block.js',
        array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components' ),
        '1.0',
        true
    );

    // Localize locations data for the block.
    $locations = get_posts( array(
        'post_type'   => 'lm_location',
        'numberposts' => -1,
    ) );
    $data = array();
    foreach ( $locations as $location ) {
        $data[] = array(
            'id'    => $location->ID,
            'title' => $location->post_title,
        );
    }
    wp_localize_script( 'lm-block-editor', 'lmLocations', $data );

    // Register the block.
    register_block_type( 'location-map/block', array(
        'editor_script'   => 'lm-block-editor',
        'render_callback' => 'lm_render_block',
        'attributes'      => array(
            'locationID' => array(
                'type' => 'number',
            ),
        ),
    ) );
}
add_action( 'init', 'lm_register_block' );

/**
 * Render callback for the Gutenberg block.
 */
function lm_render_block( $attributes ) {
    if ( empty( $attributes['locationID'] ) ) {
        return '<div>No location selected.</div>';
    }
    $location = get_post( $attributes['locationID'] );
    if ( ! $location ) {
        return '<div>Invalid location.</div>';
    }
    $longitude   = get_post_meta( $location->ID, '_lm_longitude', true );
    $latitude    = get_post_meta( $location->ID, '_lm_latitude', true );
    $description = get_post_meta( $location->ID, '_lm_description', true );
    
    // Generate a unique map container ID.
    $map_id = 'lm-map-' . $location->ID . '-' . uniqid();
    
    ob_start(); ?>
    <div class="lm-location-map" data-longitude="<?php echo esc_attr( $longitude ); ?>" data-latitude="<?php echo esc_attr( $latitude ); ?>">
        <div id="<?php echo esc_attr( $map_id ); ?>" class="lm-map-container" style="width: 100%; height: 400px;"></div>
        <p><?php echo esc_html( $description ); ?></p>
    </div>
    <script type="text/javascript">
    window.addEventListener('load', function(){
        var mapEl = document.getElementById('<?php echo esc_js( $map_id ); ?>');
        if(mapEl && typeof L !== 'undefined'){
            var lng = mapEl.parentNode.getAttribute('data-longitude');
            var lat = mapEl.parentNode.getAttribute('data-latitude');
            var map = L.map('<?php echo esc_js( $map_id ); ?>').setView([lat, lng], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);
            L.marker([lat, lng]).addTo(map);
        }
    });
    </script>
    <?php
    return ob_get_clean();
}
