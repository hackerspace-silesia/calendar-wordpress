<?php

/**
 * @package Calendar Wordpress
 * @version 0.1.2
 */
/*
Plugin Name: Calendar Wordpress
Plugin URI: https://github.com/hackerspace-silesia/calendar-wordpress
Description: Calendar Wordpress
Author: Hackerspace Silesia
Version: 0.1.2
Author URI: https://github.com/hackerspace-silesia/
*/

add_action( 'init', 'create_post_type_location' );
function create_post_type_location()
{
    register_post_type( 'location',
        array(
            'labels' => array(
                'name' => __( 'Lokacje' ),
                'singular_name' => __( 'Lokacja' ),
                'add_new_item' => __('Dodaj nową lokację'),
                'add_new' => __('Dodaj nową'),
                'edit_item' => __('Edytuj lokację'),
                'new_item' => __('Nowa lokacja'),
                'view_item' => __('Wyświetl lokację'),
                'view_items' => __('Wyświetl lokacje'),
                'search_items' => __('Szukaj lokacji'), // "przeszukaj wydarzenia"?
                'not_found' => __('Brak lokacji'),
                'not_found_in_trash' => __('Brak lokacji w koszu'),
                'parent_item_colon' => __('Lokacja nadrzędna:'),
                'all_items' => __('Lokacje'),
                'archives' => __('Archiwum lokacji'),
                'insert_into_item' => __('Wstaw do lokacji'),
                'uploaded_to_this_item' => __('Wgrane do tej lokacji'), // "wstawione do tego wydarzenia"?
                'featured_image' => __('Zdjęcie lokacji'),
                'set_featured_image' => __('Ustaw zdjęcie lokacji'),
                'remove_featured_image' => __('Usuń zdjęcie lokacji'),
                'use_featured_image' => __('Wybierz zdjęcie lokacji')
            ),
            'rewrite' => array(
                'slug'       => 'lokacja',
                'with_front' => false,
            ),
            'description' => __('Lokacje wydarzeń w kalendarium - Dzieje się'),
            'public' => true,
            'has_archive' => true,
            'show_ui' => true,
            //'show_in_menu' => true,
            'show_in_menu' => 'edit.php?post_type=calendar_event',
            //'show_in_nav_menus' => true,
            //'show_in_admin_bar' => true,
            'menu_position' => 1,
            'menu_icon' => 'dashicons-calendar-alt',
            'hierarchical' => true,
            'supports' => array(
                'title'/*, 'custom-fields'*/
            ),
            //'taxonomies' => array(
            //    'category', 'post_tag'
            //),
            'show_in_rest' => true,
        )
    );
}

function create_metabox_location()
{
    add_meta_box(
            'metadata_metabox_id',
            'Informacje o lokacji',
            'metadata_metabox_location_html',
            'location',
            'normal',
            'high'
    );
}
add_action('add_meta_boxes', 'create_metabox_location');

function metadata_metabox_location_html($post)
{
    wp_nonce_field('location_metabox_html', 'location_metabox_html_nonce');
    $options = get_option('dziejesie_option',array());
    // @TODO: sprawdzić czy użytkownik wpisał klucz API i domyślną lokalizację;
    // jeśli nie - wyświetlić odpowiedni komunikat
    ?>
    <div id="map-location" style="height: 400px; width: 600px; float: left;"></div>
    <div class="metabox_right" style="float:right; width: 50%; text-align: right">
        <div>
            <label for="location_alias">Alias lokalizacji: </label>
            <input type="text" id="location_alias" name="location_alias" value="<?php echo get_post_meta( $post->ID, '_location_alias', true ); ?>" />
        </div>
        <div>
            <label for="location_url">Strona WWW lokalizacji: </label>
            <input type="text" id="location_url" name="location_url" value="<?php echo get_post_meta( $post->ID, '_location_url', true ); ?>" />
        </div>
        <div>
            <label for="location_lat">Lat: </label>
            <input type="text" id="location_lat" name="location_lat" readonly="true" value="<?php echo get_post_meta( $post->ID, '_location_lat', true ); ?>" />
        </div>
        <div>
            <label for="location_lan">Lon: </label>
            <input type="text" id="location_lon" name="location_lon" readonly="true" value="<?php echo get_post_meta( $post->ID, '_location_lon', true ); ?>" />
        </div>
    </div>
    <div class="cleardiv" style="clear:both"></div>
    <script>
        <?php if(get_post_meta( $post->ID, '_location_lon', true ) && get_post_meta( $post->ID, '_location_lat', true )): ?>
        var position = L.latLng(<?php echo get_post_meta( $post->ID, '_location_lat', true );?>, <?php echo get_post_meta( $post->ID, '_location_lon', true ); ?>);
        <?php else: ?>
        var position = L.latLng(<?php echo $options['default_location']; ?>);
        <?php endif; ?>
        var mymap = L.map('map-location').setView(position, 13); //50.856215819093094, 19.94619369506836
        L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token={accessToken}', {
            attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="http://mapbox.com">Mapbox</a>',
            maxZoom: 18,
            id: 'mapbox.streets',
            accessToken: '<?php echo $options['leaflet_API']; ?>'
        }).addTo(mymap);
        var marker = L.marker(position,{draggable:'true'}).addTo(mymap);
        
        marker.on('dragend', function(event){
            var marker = event.target;
            var position = marker.getLatLng();
            //alert(position);
            document.getElementById('location_lat').value = position.lat;
            document.getElementById('location_lon').value = position.lng;
            //marker.setLatLng(new L.LatLng(position.lat, position.lng),{draggable:'true'});
            mymap.panTo(new L.LatLng(position.lat, position.lng))
        });
    </script>
    <?php
}

function location_save_postdata($post_id)
{
    // Check nonce and autosave
    if ( ! isset( $_POST['location_metabox_html_nonce'] ) ) { return $post_id; }
    $nonce = $_POST['location_metabox_html_nonce'];
    if ( ! wp_verify_nonce( $nonce, 'location_metabox_html' ) ) { return $post_id; }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return $post_id; }

    // Check the user's permissions.
    if ( 'page' == $_POST['location'] ) {
        if ( ! current_user_can( 'edit_page', $post_id ) ) {
            return $post_id;
        }
    } else {
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return $post_id;
        }
    }

    // Save data
    if(isset($_POST['location_url'])){
        $mydata = sanitize_text_field( $_POST['location_url'] );
        update_post_meta( $post_id, '_location_url', $mydata );
    }
    
    if(isset($_POST['location_alias'])){
        $mydata = sanitize_text_field( $_POST['location_alias'] );
        update_post_meta( $post_id, '_location_alias', $mydata );
    }
    
    if(isset($_POST['location_lon'])){
        $mydata = sanitize_text_field( $_POST['location_lon'] );
        update_post_meta( $post_id, '_location_lon', $mydata );
    }
    
    if(isset($_POST['location_lat'])){
        $mydata = sanitize_text_field( $_POST['location_lat'] );
        update_post_meta( $post_id, '_location_lat', $mydata );
    }

}
add_action('save_post', 'location_save_postdata');

