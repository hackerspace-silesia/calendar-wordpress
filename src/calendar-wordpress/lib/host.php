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
            <label for="host_url">Strona WWW organizatora: </label>
            <input type="text" id="host_url" name="host_url" value="<?php echo get_post_meta( $post->ID, '_host_url', true ); ?>" />
        </div>
        <div>
            <label for="locationalias">Nazwa lokalizacji: </label>
            <select name="locationalias" id="locationalias">
            <?php
            //global $location;
            $args = array( 'post_type' => 'location', 'nopaging' => true );
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
    
    if(isset($_POST['locationalias'])){
        $mydata = sanitize_text_field( $_POST['locationalias'] );
        update_post_meta( $post_id, '_locationalias', $mydata );
    }
}
    add_action('save_post', 'host_save_postdata');

