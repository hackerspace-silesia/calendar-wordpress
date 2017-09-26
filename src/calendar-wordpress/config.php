<?php

/* Source: https://codex.wordpress.org/Creating_Options_Pages */

class MySettingsPage
{
    private $options;

    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Ustawienia wtyczki', 
            'Dzieje się', 
            'manage_options', 
            'dziejesie-setting-admin', 
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'dziejesie_option' );
        ?>
        <div class="wrap">
            <h1>Ustawienia wtyczki</h1>
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'dziejesie_option_group' );
                do_settings_sections( 'my-setting-admin' );
                submit_button();
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {        
        register_setting(
            'dziejesie_option_group', // Option group
            'dziejesie_option', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            'Ustawienia podstawowe', // Title
            array( $this, 'print_section_info' ), // Callback
            'my-setting-admin' // Page
        );  

        add_settings_field(
            'leaflet_API', // ID
            'Klucz do API Leaflet', // Title 
            array( $this, 'leaflet_API_callback' ), // Callback
            'my-setting-admin', // Page
            'setting_section_id' // Section           
        );      

        add_settings_field(
            'default_location', 
            'Domyślna lokalizacja', 
            array( $this, 'default_location_callback' ), 
            'my-setting-admin', 
            'setting_section_id'
        );      
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();
        // @TODO
        if( isset( $input['default_location'] ) )
            $new_input['default_location'] = $input['default_location'];

        if( isset( $input['leaflet_API'] ) )
            $new_input['leaflet_API'] = $input['leaflet_API'];
        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
        print 'Wpisz poniżej swoje ustawienia dotyczące klucza API do map Leaflet oraz wprowadź domyślne współrzędne dla dodawania lokalizacji:';
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function leaflet_API_callback()
    {
        printf(
            '<input type="text" id="leaflet_API" name="dziejesie_option[leaflet_API]" value="%s" />',
            isset( $this->options['leaflet_API'] ) ? esc_attr( $this->options['leaflet_API']) : ''
        );
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function default_location_callback()
    {
        printf(
            '<input type="text" id="default_location" name="dziejesie_option[default_location]" value="%s" />',
            isset( $this->options['default_location'] ) ? esc_attr( $this->options['default_location']) : ''
        );
    }
}

if( is_admin() )
    $my_settings_page = new MySettingsPage();
