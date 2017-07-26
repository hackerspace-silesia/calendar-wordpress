<?php
/**
 * @package Calendar Wordpress
 * @version 0.1.1
 */
/*
Plugin Name: Calendar Wordpress
Plugin URI: https://github.com/hackerspace-silesia/calendar-wordpress
Description: Calendar Wordpress
Author: Hackerspace Silesia
Version: 0.1.1
Author URI: https://github.com/hackerspace-silesia/
*/

add_action( 'init', 'create_post_type' );

function create_post_type()
{
    register_post_type( 'calendar_event',
        array(
            'labels' => array(
                'name' => __( 'Wydarzenia' ),
                'singular_name' => __( 'Wydarzenie' ),
                'add_new_item' => __('Dodaj nowe wydarzenie'),
                'add_new' => __('Dodaj nowe'),
                'edit_item' => __('Edytuj wydarzenie'),
                'new_item' => __('Nowe wydarzenie'),
                'view_item' => __('Wyświetl wydarzenie'),
                'view_items' => __('Wyświetl wydarzenia'),
                'search_items' => __('Szukaj wydarzeń'), // "przeszukaj wydarzenia"?
                'not_found' => __('Brak wydarzeń'),
                'not_found_in_trash' => __('Brak wydarzeń w koszu'),
                'not_found_in_trash' => __('Brak wydarzeń w koszu'),
                'parent_item_colon' => __('Wydarzenie nadrzędne:'),
                'all_items' => __('Wszystkie wydarzenia'),
                'archives' => __('Archiwum wydarzeń'),
                'insert_into_item' => __('Wstaw do wydarzenia'),
                'uploaded_to_this_item' => __('Wgrane do tego wydarzenia'), // "wstawione do tego wydarzenia"?
                'featured_image' => __('Plakat wydarzenia'),
                'set_featured_image' => __('Ustaw plakat wydarzenia'),
                'remove_featured_image' => __('Usuń plakat wydarzenia'),
                'use_featured_image' => __('Wybierz plakat wydarzenia')
            ),
            'rewrite' => array(
                'slug'       => 'calendar_event',
                'with_front' => false,
            ),
            'description' => __('Wydarzenia w kalendarium - Dzieje się'),
            'public' => true,
            'has_archive' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => true,
            'show_in_admin_bar' => true,
            'menu_position' => 3,
            'menu_icon' => 'dashicons-calendar-alt',
            'hierarchical' => true,
            'supports' => array(
                'title', 'editor', 'thumbnail', 'page-attributes', 'custom-fields'
            ),
            'taxonomies' => array(
                'category', 'post_tag'
            ),
            'show_in_rest' => true,
        )
    );
}

function create_metabox()
{
    add_meta_box(
            'metadata_metabox_id',
            'Informacje o wydarzeniu',
            'metadata_metabox_html',
            'calendar_event',
            'after_title',
            'high'
    );
}
add_action('add_meta_boxes', 'create_metabox');

function move_metabox_after_title()
{
        global $post, $wp_meta_boxes;
        do_meta_boxes( get_current_screen(), 'after_title', $post );
        unset($wp_meta_boxes['post']['after_title']);
}
add_action('edit_form_after_title', 'move_metabox_after_title');

function metadata_metabox_html($post)
{
    wp_nonce_field('calendar_metabox_html', 'calendar_metabox_html_nonce');
    $locationalias = get_post_meta($post->ID, '_locationalias', true);
    $organisedby= get_post_meta($post->ID, '_organisedby', true);
    ?>
    <div class="metabox_right" style="float:right; width: 50%; text-align: right">
        <div>
            <label for="event_start">Początek wydarzenia: </label>
            <input type="text" id="event_start" name="event_start" class="datetimepicker" value="<?php echo get_post_meta( $post->ID, '_event_start', true ); ?>" />
        </div>
        <div>
            <label for="event_stop">Koniec wydarzenia: </label>
            <input type="text" id="event_stop" name="event_stop" class="datetimepicker" value="<?php echo get_post_meta( $post->ID, '_event_stop', true ); ?>" />    
        </div>
        <div>
            <label for="event_link">Główny adres WWW wydarzenia: </label>
            <input type="url" id="event_link" name="event_link" value="<?php echo get_post_meta( $post->ID, '_event_link', true ); ?>" />
        </div>
        <div>
            <label for="organisedby">Organizator wydarzenia: </label>
            <select name="organisedby" id="organisedby">
            <?php
            //global $location;
            $args = array( 'post_type' => 'host' );
            $hosts = get_posts( $args );
            foreach ( $hosts as $host ) :
            ?>
                <option value="<?php echo $host->ID; ?>" <?php selected($organisedby, $host->ID); ?> ><?php echo $host->post_title; ?></option>
            <?php endforeach; 
            wp_reset_postdata();
            ?>
            </select>
        </div>
        <div>
            <label for="locationalias">Nazwa lokalizacji: </label>
            <select name="locationalias" id="locationalias">
            <?php
            //global $location;
            $args = array( 'post_type' => 'location' );
            $locations = get_posts( $args );
            foreach ( $locations as $location ) :
            ?>
                <option value="<?php echo $location->ID; ?>" <?php selected($locationalias, $location->ID); ?> ><?php echo $location->post_title; ?></option>
            <?php endforeach; 
            wp_reset_postdata();
            ?>
            </select>
        </div>
    </div>
    <div class="cleardiv" style="clear:both"></div>
    <?php
}

function calendar_save_postdata($post_id)
{
    // Check nonce and autosave
    if ( ! isset( $_POST['calendar_metabox_html_nonce'] ) ) { return $post_id; }
    $nonce = $_POST['calendar_metabox_html_nonce'];
    if ( ! wp_verify_nonce( $nonce, 'calendar_metabox_html' ) ) { return $post_id; }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return $post_id; }

    // Check the user's permissions.
    if ( 'page' == $_POST['calendar_event'] ) {
        if ( ! current_user_can( 'edit_page', $post_id ) ) {
            return $post_id;
        }
    } else {
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return $post_id;
        }
    }

    // Save data
    if(isset($_POST['event_start'])){
        $mydata = sanitize_text_field( $_POST['event_start'] );
        update_post_meta( $post_id, '_event_start', $mydata );
    }
    
    if(isset($_POST['event_stop'])){
        $mydata = sanitize_text_field( $_POST['event_stop'] );
        update_post_meta( $post_id, '_event_stop', $mydata );
    }
    
    if(isset($_POST['event_link'])){
        $mydata = sanitize_text_field( $_POST['event_link'] );
        update_post_meta( $post_id, '_event_link', $mydata );
    }
    
    if(isset($_POST['locationalias'])){
        $mydata = sanitize_text_field($_POST['locationalias']);
        update_post_meta($post_id, '_locationalias', $mydata);
    }
    
    if(isset($_POST['organisedby'])){
        $mydata = sanitize_text_field($_POST['organisedby']);
        update_post_meta($post_id, '_organisedby', $mydata);
    }
    
    if(isset($_POST['organisedbyurl'])){
        $mydata = sanitize_text_field($_POST['organisedbyurl']);
        update_post_meta($post_id, '_organisedbyurl', $mydata);
    }
}
add_action('save_post', 'calendar_save_postdata');


// ==========================================
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
        var position = L.latLng(50.856215819093094, 19.94619369506836);    
        <?php endif; ?>
        var mymap = L.map('map-location').setView(position, 13); //50.856215819093094, 19.94619369506836
        L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token={accessToken}', {
            attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="http://mapbox.com">Mapbox</a>',
            maxZoom: 18,
            id: 'mapbox.streets',
            accessToken: 'pk.eyJ1IjoiYmFydG5pa2oiLCJhIjoiY2o1a3JoYnNvMm1vMzMzbnoxNnFoamFrcyJ9.37uf-5-t9gZhlkL4wQx3Lw'
        }).addTo(mymap);
        var marker = L.marker(position,{draggable:'true'}).addTo(mymap);
        
        marker.on('dragend', function(event){
            var marker = event.target;
            var position = marker.getLatLng();
            //alert(position);
            document.getElementById('location_lat').value = position.lat;
            document.getElementById('location_lon').value = position.lng;
            //marker.setLatLng(new L.LatLng(position.lat, position.lng),{draggable:'true'});
            //map.panTo(new L.LatLng(position.lat, position.lng))
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

// ==========================================
add_action( 'init', 'create_post_type_host' );
function create_post_type_host()
{
    register_post_type( 'host',
        array(
            'labels' => array(
                'name' => __( 'Organizatorzy' ),
                'singular_name' => __( 'Organizator' ),
                //'add_new_item' => __('Dodaj nową lokację'),
                //'add_new' => __('Dodaj nową'),
                //'edit_item' => __('Edytuj lokację'),
                //'new_item' => __('Nowa lokacja'),
                //'view_item' => __('Wyświetl lokację'),
                //'view_items' => __('Wyświetl lokacje'),
                //'search_items' => __('Szukaj lokacji'), // "przeszukaj wydarzenia"?
                //'not_found' => __('Brak lokacji'),
                //'not_found_in_trash' => __('Brak lokacji w koszu'),
                //'parent_item_colon' => __('Lokacja nadrzędna:'),
                //'all_items' => __('Lokacje'),
                //'archives' => __('Archiwum lokacji'),
                //'insert_into_item' => __('Wstaw do lokacji'),
                //'uploaded_to_this_item' => __('Wgrane do tej lokacji'), // "wstawione do tego wydarzenia"?
                //'featured_image' => __('Zdjęcie lokacji'),
                //'set_featured_image' => __('Ustaw zdjęcie lokacji'),
                //'remove_featured_image' => __('Usuń zdjęcie lokacji'),
                //'use_featured_image' => __('Wybierz zdjęcie lokacji')
            ),
            'rewrite' => array(
                'slug'       => 'host',
                'with_front' => false,
            ),
            'description' => __('Organizatorzy wydarzeń w kalendarium - Dzieje się'),
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
                'title', 'thumbnail' /*, 'custom-fields'*/
            ),
            //'taxonomies' => array(
            //    'category', 'post_tag'
            //),
            'show_in_rest' => true,
        )
    );
}

function create_metabox_host()
{
    add_meta_box(
            'metadata_metabox_id',
            'Informacje o organizatorze',
            'metadata_metabox_host_html',
            'host',
            'normal',
            'high'
    );
}
add_action('add_meta_boxes', 'create_metabox_host');

function metadata_metabox_host_html($post)
{
    wp_nonce_field('host_metabox_html', 'host_metabox_html_nonce');
    $locationalias = get_post_meta($post->ID, '_locationalias', true);
    ?>
    <div class="metabox_right" style="float:right; width: 50%; text-align: right">
        <div>
            <label for="host_alias">Alias organizatora: </label>
            <input type="text" id="host_alias" name="host_alias" value="<?php echo get_post_meta( $post->ID, '_host_alias', true ); ?>" />
        </div>
        <div>
            <label for="host_url">Strona WWW organizatora: </label>
            <input type="text" id="host_url" name="host_url" value="<?php echo get_post_meta( $post->ID, '_host_url', true ); ?>" />
        </div>
        <div>
            <label for="locationalias">Nazwa lokalizacji: </label>
            <select name="locationalias" id="locationalias">
            <?php
            //global $location;
            $args = array( 'post_type' => 'location' );
            $locations = get_posts( $args );
            foreach ( $locations as $location ) :
            ?>
                <option value="<?php echo $location->ID; ?>" <?php selected($locationalias, $location->ID); ?> ><?php echo $location->post_title; ?></option>
            <?php endforeach; 
            wp_reset_postdata();
            ?>
            </select>
        </div>
    </div>
    <div class="cleardiv" style="clear:both"></div>
    <?php
}

function host_save_postdata($post_id)
{
    // Check nonce and autosave
    if ( ! isset( $_POST['host_metabox_html_nonce'] ) ) { return $post_id; }
    $nonce = $_POST['host_metabox_html_nonce'];
    if ( ! wp_verify_nonce( $nonce, 'host_metabox_html' ) ) { return $post_id; }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return $post_id; }

    // Check the user's permissions.
    if ( 'page' == $_POST['host'] ) {
        if ( ! current_user_can( 'edit_page', $post_id ) ) {
            return $post_id;
        }
    } else {
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return $post_id;
        }
    }

    // Save data
    if(isset($_POST['host_url'])){
        $mydata = sanitize_text_field( $_POST['host_url'] );
        update_post_meta( $post_id, '_host_url', $mydata );
    }
    
    if(isset($_POST['host_alias'])){
        $mydata = sanitize_text_field( $_POST['host_alias'] );
        update_post_meta( $post_id, '_host_alias', $mydata );
    }
    
    if(isset($_POST['locationalias'])){
        $mydata = sanitize_text_field( $_POST['locationalias'] );
        update_post_meta( $post_id, '_locationalias', $mydata );
    }
}
    add_action('save_post', 'host_save_postdata');

// ----------------------------------------------------
// ----------------------------------------------------

function query_post_type($query) {
    // @TODO: do przemyślenia jakieś lepsze rozwiązanie
    if($query->get('post_type') == 'post'){
        $query->set('post_type', array('post', 'calendar_event'));
    }
    
}
add_filter('pre_get_posts', 'query_post_type');

function extra_admin_styles() {
    // @TODO: ładować tylko niezbędne
    wp_enqueue_style( 'wp-jquery-datetime-picker-css' , plugins_url('/assets/css/jquery.datetimepicker.css', __FILE__ ));
    wp_enqueue_style( 'leaflet-css', 'https://unpkg.com/leaflet@1.1.0/dist/leaflet.css');
}
add_action('admin_print_styles', 'extra_admin_styles');

function extra_admin_scripts() {
    // @TODO: ładować tylko niezbędne
    wp_enqueue_script( 'wp-jquery-datetime-picker-js', plugins_url('/assets/js/jquery.datetimepicker.full.min.js', __FILE__ ));
    wp_enqueue_script( 'wp-calendar-js', plugins_url('/assets/js/calendar.js', __FILE__ ));
    wp_enqueue_script( 'leaflet-js', 'https://unpkg.com/leaflet@1.1.0/dist/leaflet.js' );
}
add_action('admin_enqueue_scripts', 'extra_admin_scripts');