<?php
/**
 * @package Calendar Wordpress
 * @version 0.1
 */
/*
Plugin Name: Calendar Wordpress
Plugin URI: https://github.com/hackerspace-silesia/calendar-wordpress
Description: Calendar Wordpress
Author: Hackerspace Silesia
Version: 0.1
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
    ?>
    <iframe width="50%" height="350" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="http://www.openstreetmap.org/export/embed.html?bbox=19.874525070190433%2C50.82871023342679%2C20.017862319946293%2C50.88370519163835&amp;layer=mapnik&amp;marker=50.856215819093094%2C19.94619369506836" style="border: none;float:left"></iframe>
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
        
    </div>
    <div class="cleardiv" style="clear:both"></div>
    <?php
}

function query_post_type($query) {
    // @TODO: do przemyślenia jakieś lepsze rozwiązanie niż catch-all
     $query->set('post_type', array('post', 'calendar_event'));
}
add_filter('pre_get_posts', 'query_post_type');

function extra_admin_styles() {
    wp_enqueue_style( 'wp-jquery-datetime-picker-css' , plugins_url('/assets/css/jquery.datetimepicker.css', __FILE__ ));
}
add_action('admin_print_styles', 'extra_admin_styles');

function extra_admin_scripts() {
    wp_enqueue_script( 'wp-jquery-datetime-picker-js', plugins_url('/assets/js/jquery.datetimepicker.full.min.js', __FILE__ ));
    wp_enqueue_script( 'wp-calendar-js', plugins_url('/assets/js/calendar.js', __FILE__ ));
}
add_action('admin_enqueue_scripts', 'extra_admin_scripts');

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
}
add_action('save_post', 'calendar_save_postdata');