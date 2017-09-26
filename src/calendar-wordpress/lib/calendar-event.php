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
                'title', 'editor', 'thumbnail', 'revisions', 'comments'
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
            'side',
            'high'
    );
}
add_action('add_meta_boxes', 'create_metabox');

function metadata_metabox_html($post)
{
    wp_nonce_field('calendar_metabox_html', 'calendar_metabox_html_nonce');
    $locationalias = get_post_meta($post->ID, '_locationalias', true);
    $organisedby= get_post_meta($post->ID, '_organisedby', true);
    ?>
    <div class="metabox">
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

function query_post_type($query) {
    if( $query->is_main_query() && !is_admin() ){
        add_leaflet();
        $query->set( 'post_type', array( 'post', 'calendar_event' ) );
        $query->set( 'posts_per_page', 1000 );
        return $query;
    }
    if( $query->is_archive() && !is_admin() ) {
        add_leaflet();
        $query->set( 'post_type', array( 'post', 'calendar_event' ) );
        return $query;
    }
    if( $query->is_single() && !is_admin() ) {
        add_leaflet();
        $query->set( 'post_type', array( 'post', 'calendar_event' ) );
        return $query;
    }
    return $query;
}
add_filter('pre_get_posts', 'query_post_type');

function add_leaflet() {
    wp_enqueue_style( 'leaflet-css' , plugins_url('../assets/css/leaflet.css', __FILE__ ));
    wp_enqueue_style( 'wp-calendarevents-css' , plugins_url('../assets/css/style.css', __FILE__ ));
    wp_enqueue_script( 'leaflet-js', plugins_url('../assets/js/leaflet.js', __FILE__ ));
    wp_enqueue_script( 'leaflet-color-markers-js', plugins_url('../assets/js/leaflet-color-markers.js', __FILE__ ));
}

add_filter('the_content', 'add_tags_to_content');
function add_tags_to_content($content){
    //$tags = get_the_tags();
    if ( get_the_post_meta('_event_start') ) {
        $content .= '<div class="time"><p>Czas wydarzenia: '.get_the_post_meta('_event_start');
        if (get_the_post_meta('_event_stop')) $content .= ' do '. get_the_post_meta('_event_stop');
        $content .= '</p></div>';
    }
    if ( get_the_tag_list() ) $content .= '<div class="tagi"><p>Tagi: '.get_the_tag_list('',', ').'</p></div>';
    if ( get_the_category() ) $content .= '<div class="categories"><p>Kategorie: '.get_the_category_list(', ').'</p></div>';
    if ( get_the_post_meta('_event_link') )
        $content .= '<div class="source"><p>Źródło: <a href="'.get_the_post_meta('_event_link').'" target="_blank" >'.get_the_post_meta('_event_link').'</a></p></div>';
    return $content;
}

function get_the_post_meta($key){
    return get_post_meta( get_the_ID(), $key, true);
}