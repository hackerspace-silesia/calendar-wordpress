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
    $organisedby = get_post_meta($post->ID, '_organisedby', true);
    $price_availability = get_post_meta($post->ID, '_price_available', true);
    $performer_type = get_post_meta($post->ID, '_performer_type', true);
    //print_r($performer_type); //die();
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
            $args = array( 'post_type' => 'host', 'nopaging' => true );
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
            <label for="event_price">Cena biletu: </label>
            <input type="text" id="event_price" name="event_price" value="<?php echo get_post_meta( $post->ID, '_event_price', true ); ?>" />
        </div>
        <div>
            <label for="price_available">Dostępność biletów/wejściówek: </label>
            <select name="price_available" id="price_available">
                <option value="InStock" <?php selected($price_availability, 'InStock'); ?> >Dostępne</option>
                <option value="SoldOut" <?php selected($price_availability, 'SoldOut'); ?> >Niedostępne</option>
                <option value="PreOrder" <?php selected($price_availability, 'PreOrder'); ?> >Przedsprzedaż</option>
                <option value="null" <?php selected($price_availability, 'null'); ?> >- nie pokazuj informacji -</option>
            </select>
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
        <div>
            <label>Wykonawcy: </label>
            <div id="performers">
            <?php foreach ($performer_type as $key => $type): ?>
            <div class="performer">
                <select name="performer_type[]" class="performer_type">
                    <option value="null" <?php selected($type, 'null'); ?> >---</option>
                    <option value="MusicGroup" <?php selected($type, 'MusicGroup'); ?> >Muzyka</option>
                    <option value="DanceGroup" <?php selected($type, 'DanceGroup'); ?> >Taniec</option>
                    <option value="TheaterGroup" <?php selected($type, 'TheaterGroup'); ?> >Teatr</option>
                    <option value="Person" <?php selected($type, 'Person'); ?> >Inni</option>
                </select>
                <input type="text" class="performer_name" name="performer_name[]" value="<?php $pn = get_post_meta( $post->ID, '_performer_name', true ); echo $pn[$key]; ?>" />
                <a href="#" onclick="return deleteParentDiv(this);">Usuń powyższego wykonawcę</a>
            </div>
            <?php endforeach; ?>
            </div>
            <div id="addPerformer">
                <a href="#" onclick="return addPerformer();">Dodaj nowego wykonawcę</a>
                <script>
                    function addPerformer(){
                        jQuery('#performers').append(`
                        <div class="performer">    
                            <select name="performer_type[]" class="performer_type">
                                <option value="null">---</option>
                                <option value="MusicGroup">Muzyka</option>
                                <option value="DanceGroup">Taniec</option>
                                <option value="TheaterGroup">Teatr</option>
                                <option value="Person">Inni</option>
                            </select>
                            <input type="text" class="performer_name" name="performer_name[]" />
                            <a href="#" onclick="return deleteParentDiv(this);">Usuń powyższego wykonawcę</a>
                        </div>        
                        `);
                        return false;
                    }
                    function deleteParentDiv(s){
                        jQuery(s).parent().remove();
                        return false;
                    }
                </script>
            </div>
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
    
    if(isset($_POST['event_price'])){
        $mydata = sanitize_text_field($_POST['event_price']);
        update_post_meta($post_id, '_event_price', $mydata);
    }
    
    if(isset($_POST['price_available'])){
        $mydata = sanitize_text_field($_POST['price_available']);
        update_post_meta($post_id, '_price_available', $mydata);
    }
    
    if(isset($_POST['performer_type'])){
        $mydata = array_map( 'sanitize_text_field', $_POST['performer_type'] );
        update_post_meta($post_id, '_performer_type', $mydata);
    }
    
    if(isset($_POST['performer_name'])){
        $mydata = array_map( 'sanitize_text_field', $_POST['performer_name'] );
        update_post_meta($post_id, '_performer_name', $mydata);
    }
    
    if(empty($_POST['performer_type'])){
        update_post_meta($post_id, '_performer_type', null);
        update_post_meta($post_id, '_performer_name', null);
    }
    
}
add_action('save_post', 'calendar_save_postdata');

function query_post_type($query) {
    if( $query->is_main_query() && !is_admin() ){
        add_leaflet();
        $query->set( 'post_type', array( 'post', 'calendar_event' ) );
        // TODO: a może jednak paginacja?
        $query->set( 'posts_per_page', 1000 );
        return $query;
    }
    if( $query->is_main_query() && $query->is_archive() && !is_admin() ) {
        add_leaflet();
        $query->set( 'post_type', array( 'post', 'calendar_event' ) );
        return $query;
    }
    if( $query->is_main_query() && $query->is_single() && !is_admin() ) {
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
    // stara wersja "po kontencie"
    /*if ( get_the_post_meta('_event_start') ) {
        $content .= '<div class="time"><p>Czas wydarzenia: '.get_the_post_meta('_event_start');
        if (get_the_post_meta('_event_stop')) $content .= ' do '. get_the_post_meta('_event_stop');
        $content .= '</p></div>';
    }*/
    if ( get_the_post_meta('_event_start') ) {
        $content = '<div class="time"><p>Czas wydarzenia: '.get_the_post_meta('_event_start').
                (get_the_post_meta('_event_stop')?' do '. get_the_post_meta('_event_stop').'</p></div>':'').$content;
    }
    if ( get_the_tag_list() ) {$content .= '<div class="tagi"><p>Tagi:<br/>'.get_the_tag_list('',', ').'</p></div>';}
    if ( get_the_category() ) {$content .= '<div class="categories"><p>Kategorie:<br/>'.get_the_category_list(', ').'</p></div>';}
    if ( get_the_post_meta('_event_link') ) {
        $content .= '<div class="source"><p>Źródło:<br/><a href="'.get_the_post_meta('_event_link').'" target="_blank" >'.get_the_post_meta('_event_link').'</a></p></div>';
    }
    
    if ( get_the_post_meta('_locationalias') ) {
        $locID = get_the_post_meta('_locationalias');
        $content .= '<div class="location"><p>Lokacja:';
        $content .= '<br/>'. get_the_title($locID);
        if ( get_post_meta($locID,'_location_address',true) ) { $content .= '<br/>'.get_post_meta($locID,'_location_address',true); }
        if ( get_post_meta($locID,'_location_address_city',true) ) { $content .= '<br/>'.get_post_meta($locID,'_location_address_city',true); }
        //$content .= '<br/>'.get_post_meta($locID,'_location_lat',true).', '. get_post_meta($locID,'_location_lon',true);
        $content .= '<br/><a rel="lightbox" target="_blank" href="https://www.openstreetmap.org/?mlat='.get_post_meta($locID,'_location_lat',true).'&mlon='.get_post_meta($locID,'_location_lon',true).'&zoom=18">Zobacz na mapie</a>';
        $content .= '</p></div>';
    }
    
    if ( get_the_post_meta('_organisedby') ) {
        $orgID = get_the_post_meta('_organisedby');
        $content .= '<div class="organisedby"><p>Organizator:<br/>';
        if ( get_post_meta($orgID,'_host_url',true) ) { $content .= '<a href="'.get_post_meta($orgID,'_host_url',true).'">'; }
        $content .= get_the_title($orgID);
        if ( get_post_meta($orgID,'_host_url',true) ) { $content .= '</a>'; }
        $content .= '</p></div>';
    }
    
    if ( get_the_post_meta('_event_price') > 0 ) {
        $content .= '<div class="price"><p>Cena biletu:<br/>'.get_the_post_meta('_event_price').' PLN</p></div>';
    }
    
    if ( get_the_post_meta('_performer_name') ) {
        $content .= '<div class="performer"><p>Wykonawca:<br/>';
        $content .= implode(', ', get_the_post_meta('_performer_name'));
        $content .= '</p></div>';
    }
    
    return $content;
}

function get_the_post_meta($key){
    return get_post_meta( get_the_ID(), $key, true);
}

function add_custom_columns( $columns ){
    //TODO: tylko dla eventów
    return array_merge ( $columns,
    array(
        // @TODO: nie ma lepszej metody na sortowalne kolumny?
        'event_start' => '<a href="'.admin_url().'edit.php?post_type=calendar_event&orderby=event_start">Początek</a>',
        'event_stop' => '<a href="'.admin_url().'edit.php?post_type=calendar_event&orderby=event_stop">Koniec</a>',
    ));
}
add_filter('manage_calendar_event_posts_columns' , 'add_custom_columns');

add_action( 'manage_calendar_event_posts_custom_column' , 'custom_columns', 10, 2 );
function custom_columns( $column, $post_id ) {
    switch ( $column ) {
        case 'event_start':
            echo get_post_meta( $post_id, '_event_start', true ); 
            break;
        case 'event_stop':
            echo get_post_meta( $post_id, '_event_stop', true ); 
            break;
    }
}

add_filter( 'manage_calendar_event_sortable_columns', 'sortable_columns' );
function sortable_columns( $columns ) {
    $columns['event_start'] = 'event_start';
    $columns['event_stop'] = 'event_stop';
 
    //To make a column 'un-sortable' remove it from the array
    //unset($columns['date']);
 
    return $columns;
}

add_action( 'pre_get_posts', 'columns_orderby' );
function columns_orderby( $query ) {
    if( ! is_admin() )
        return;
 
    $orderby = $query->get( 'orderby');
 
    if( 'event_start' == $orderby ) {
        $query->set('meta_key','_event_start');
        $query->set('orderby','meta_value');
    }
    
    if( 'event_stop' == $orderby ) {
        $query->set('meta_key','_event_stop');
        $query->set('orderby','meta_value');
    }
}

add_action( 'pre_get_posts', 'posts_orderby' );
function posts_orderby( $query ) {
    if( is_admin() || !is_main_query())
        return;
 
    // sortowanie po dacie
    $query->set('meta_key','_event_start');
    $query->set('orderby','meta_value');
    $query->set('order','ASC');
    
    // tylko wydarzenia kończące się po "dzisiaj"
    $today = date( 'Y/m/d 00:00' );
    $query->set('meta_query', array( array(
        'key' => '_event_stop',
        'value' => $today,
        'compare' => '>=',
    )));
}

/* funkcja zamienia obcięte miniatury plakatów z artykułów na pełne;
 * wypadałoby się zastanowić, czy nie pytać o to użytkownika (wstawić w opcje wtyczki)
 * albo/i robić to tylko dla pojedynczego artykułu
 */
add_filter('post_thumbnail_html', 'modify_post_thumbnail_html', 99, 5);
function modify_post_thumbnail_html($html, $post_id, $post_thumbnail_id, $size, $attr) {
    $id = get_post_thumbnail_id();
    $src = wp_get_attachment_image_src($id, 'medium_large');
    $alt = get_the_title($id);
    $class = $attr['class'];

    $html = '<img src="' . $src[0] . '" alt="' . $alt . '" class="' . $class . '" />';

    return $html;
}

add_filter('the_content', 'add_structured_data_to_content', 5);
function add_structured_data_to_content($content){
    $performersNames = get_the_post_meta('_performer_name');
    $performersTypes = get_the_post_meta('_performer_type');
    ?>
    <script type="application/ld+json">
        {
            "@context": "http://schema.org",
            "@type": "Event",
            "name": "<?php echo get_the_title(); ?>",
            "startDate": "<?php echo get_the_post_meta('_event_start'); ?>",
            "endDate": "<?php echo get_the_post_meta('_event_stop'); ?>",
            "location": {
                "@type": "Place",
                "address": {
                  "@type": "PostalAddress",
                  "addressLocality": "<?php $locID = get_the_post_meta('_locationalias'); echo get_post_meta($locID,'_location_address_city',true);?>",
                  //"addressRegion": "CO",
                  //"postalCode": "80209",
                  "streetAddress": "<?php echo get_post_meta($locID,'_location_address',true);?>"
                },
                "name": "<?php $orgID = get_the_post_meta('_organisedby'); echo get_the_title($orgID); ?>"
            },
            "description": "<?php echo htmlspecialchars(strip_tags($content)); ?>",
            "image": "<?php $img = wp_get_attachment_image_src(get_post_thumbnail_id(), 'large'); echo $img[0];?>"
            <?php if ( $performersNames ): ?>
                ,"performer": [
                    <?php $len = count($performersNames); foreach($performersNames as $key => $performerName): ?>
                            {
                                "@type": "<?php echo $performersTypes[$key]; ?>",
                                "name": "<?php echo addslashes($performerName); ?>"
                            }<?php if ($key != $len-1) {echo ',';} ?>
                    <?php endforeach; ?>
                ]
            <?php endif; ?>
            <?php if(get_the_post_meta('_event_price') != '') : ?>
            ,"offers": {
              "@type": "Offer",
              "price": "<?php echo get_the_post_meta('_event_price'); ?>",
              "priceCurrency": "PLN"
              <?php $pa = get_the_post_meta('_price_available'); if($pa == 'InStock' || $pa == 'PreOrder' || $pa == 'SoldOut'):?>,"availability": "http://schema.org/<?php echo $pa;?>"<?php endif;?>
              //"validFrom": "2017-01-20T16:20-08:00",
              //"url": "https://example.com"
            }
            <?php endif; ?>
        }
    </script>
    <?php
    return $content;
}