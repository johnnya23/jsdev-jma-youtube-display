<?php

/**
 * Created by PhpStorm.
 * User: John
 * Date: 2/12/2017
 * Time: 8:00 PM
 */
class JMAYtSettings
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
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
            'YouTube Settings',
            'YouTube Settings',
            'manage_options',
            'jma-yt-setting-admin',
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'jma_yt_settings' );
        ?>
        <div class="wrap">
            <h1>Api Settings</h1>
            <form method="post" action="options.php">
                <?php
                // This prints out all hidden setting fields
                settings_fields( 'my_option_group' );
                do_settings_sections( 'jma-yt-setting-admin' );
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
            'my_option_group', // Option group
            'jma_yt_settings', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            'Insert Api', // Title
            array( $this, 'print_section_info' ), // Callback
            'jma-yt-setting-admin' // Page
        );

        add_settings_field(
            'api_number', // ID
            'Api Number', // Title
            array( $this, 'api_number_callback' ), // Callback
            'jma-yt-setting-admin', // Page
            'setting_section_id' // Section
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
        if( isset( $input['api_number'] ) )
            $new_input['api_number'] = sanitize_text_field( $input['api_number'] );

        return $new_input;
    }

    /**
     * Print the Section text
     */
    public function print_section_info()
    {
        print 'Enter api below:';
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function api_number_callback()
    {
        printf(
            '<input type="text" size="50" id="api_number" name="jma_yt_settings[api_number]" value="%s" />',
            isset( $this->options['api_number'] ) ? esc_attr( $this->options['api_number']) : ''
        );
    }
}
