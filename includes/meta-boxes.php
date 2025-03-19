<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Add meta box for location details.
 */
function lm_add_meta_boxes() {
    add_meta_box(
        'lm_location_meta',
        'Location Details',
        'lm_render_meta_box',
        'lm_location',
        'normal',
        'default'
    );
}
add_action( 'add_meta_boxes', 'lm_add_meta_boxes' );

/**
 * Render meta box content.
 */
function lm_render_meta_box( $post ) {
    wp_nonce_field( 'lm_save_meta_box_data', 'lm_meta_box_nonce' );
    $longitude   = get_post_meta( $post->ID, '_lm_longitude', true );
    $latitude    = get_post_meta( $post->ID, '_lm_latitude', true );
    $description = get_post_meta( $post->ID, '_lm_description', true );
    $displayGmapsUrl = get_post_meta( $post->ID, '_lm_display_gmaps_url', true );
    $tile_theme = get_post_meta( $post->ID, '_lm_tile_theme', true );
    if ( empty( $tile_theme ) ) {
        $tile_theme = 'osm_standard';
    }
    $customTileUrl = get_post_meta( $post->ID, '_lm_tile_url', true );
    ?>
    <p>
        <label for="lm_longitude">Longitude:</label>
        <input type="text" id="lm_longitude" name="lm_longitude" value="<?php echo esc_attr( $longitude ); ?>" />
    </p>
    <p>
        <label for="lm_latitude">Latitude:</label>
        <input type="text" id="lm_latitude" name="lm_latitude" value="<?php echo esc_attr( $latitude ); ?>" />
    </p>
    <p>
        <label for="lm_description">Description:</label>
        <textarea id="lm_description" name="lm_description"><?php echo esc_textarea( $description ); ?></textarea>
    </p>
    <p>
        <label for="lm_display_gmaps_url">Display Description as Google Maps Url</label>
        <input type="checkbox" id="lm_display_gmaps_url" name="lm_display_gmaps_url" <?php checked( $displayGmapsUrl, '1' ); ?> />
    </p>
    <p>
        <label for="lm_tile_theme">Tile Theme:</label>
        <select id="lm_tile_theme" name="lm_tile_theme">
            <option value="osm_standard" <?php selected( $tile_theme, 'osm_standard' ); ?>>OpenStreetMap Standard</option>
            <option value="osm_hot" <?php selected( $tile_theme, 'osm_hot' ); ?>>OpenStreetMap HOT</option>
            <option value="lima_labs" <?php selected( $tile_theme, 'lima_labs' ); ?>>Lima Labs</option>
            <option value="custom" <?php selected( $tile_theme, 'custom' ); ?>>Custom</option>
        </select>
    </p>
    <p id="custom_tile_url" <?php if ($tile_theme != 'custom') echo 'hidden'; ?>>
        <label for="lm_tile_url">Custom Tile Url:</label>
        <input type="text" id="lm_tile_url" name="lm_tile_url" value="<?php echo esc_attr( $customTileUrl ); ?>" />
    </p>
    <p>
        <strong>Select Location on Map:</strong>
    </p>
    <div style="margin-bottom:10px;">
        <input type="text" id="lm-address-search" placeholder="Enter address to search" style="width:70%;" />
        <button type="button" id="lm-search-btn">Search</button>
    </div>
    <div id="lm-map-picker" style="width: 100%; height: 300px;"></div>
    <?php
}

/**
 * Save meta box data.
 */
function lm_save_meta_box_data( $post_id ) {
    if ( ! isset( $_POST['lm_meta_box_nonce'] ) ) {
        return;
    }
    if ( ! wp_verify_nonce( $_POST['lm_meta_box_nonce'], 'lm_save_meta_box_data' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( isset( $_POST['lm_longitude'] ) ) {
        update_post_meta( $post_id, '_lm_longitude', sanitize_text_field( $_POST['lm_longitude'] ) );
    }
    if ( isset( $_POST['lm_latitude'] ) ) {
        update_post_meta( $post_id, '_lm_latitude', sanitize_text_field( $_POST['lm_latitude'] ) );
    }
    if ( isset( $_POST['lm_description'] ) ) {
        update_post_meta( $post_id, '_lm_description', sanitize_textarea_field( $_POST['lm_description'] ) );
    }
    if ( isset( $_POST['lm_display_gmaps_url'] ) ) {
        update_post_meta( $post_id, '_lm_display_gmaps_url', 1 );
    } else {
        update_post_meta( $post_id, '_lm_display_gmaps_url', 0 );
    }
    if ( isset( $_POST['lm_tile_theme'] ) ) {
        update_post_meta( $post_id, '_lm_tile_theme', sanitize_text_field( $_POST['lm_tile_theme'] ) );
    }
    if ( isset( $_POST['lm_tile_url'] ) ) {
        update_post_meta( $post_id, '_lm_tile_url', sanitize_text_field( $_POST['lm_tile_url'] ) );
    }
}
add_action( 'save_post', 'lm_save_meta_box_data' );
