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

require_once 'lib/calendar-event.php';
require_once 'lib/location.php';
require_once 'lib/host.php';
require_once 'lib/calendar-widget.php';

include 'config.php';

//include 'dziejesie-options.php';

function extra_admin_styles() {
    // @TODO: ładować tylko niezbędne
    wp_enqueue_style( 'wp-jquery-datetime-picker-css' , plugins_url('/assets/css/jquery.datetimepicker.css', __FILE__ ));
    wp_enqueue_style( 'leaflet-css' , plugins_url('/assets/css/leaflet.css', __FILE__ ));
    //wp_enqueue_style( 'leaflet-css', 'https://unpkg.com/leaflet@1.1.0/dist/leaflet.css');
}
add_action('admin_print_styles', 'extra_admin_styles');

function extra_admin_scripts() {
    // @TODO: ładować tylko niezbędne
    wp_enqueue_script( 'wp-jquery-datetime-picker-js', plugins_url('/assets/js/jquery.datetimepicker.full.min.js', __FILE__ ));
    wp_enqueue_script( 'wp-calendar-js', plugins_url('/assets/js/calendar.js', __FILE__ ));
    wp_enqueue_script( 'leaflet-js', plugins_url('/assets/js/leaflet.js', __FILE__ ));
    //wp_enqueue_script( 'leaflet-js', 'https://unpkg.com/leaflet@1.1.0/dist/leaflet.js' );
}
add_action('admin_enqueue_scripts', 'extra_admin_scripts');