<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class JMAYtSettings {
    private $dir;
    private $file;
    private $assets_dir;
    private $assets_url;
    private $settings_base;
    private $settings;

    public function __construct() {
        $this->file =  __FILE__ ;
        $this->dir = dirname( $this->file );
        $this->assets_dir = trailingslashit( $this->dir ) . 'assets';
        $this->assets_url = esc_url($this->file );
        $this->settings_base = 'jmayt_';

        // Initialise settings
        add_action( 'admin_init', array( $this, 'init' ) );

        // Register plugin settings
        add_action( 'admin_init' , array( $this, 'register_settings' ) );

        // Add settings page to menu
        add_action( 'admin_menu' , array( $this, 'add_menu_item' ) );

        // Add settings link to plugins page
        add_filter( 'plugin_action_links_' . plugin_basename( $this->file ) , array( $this, 'add_settings_link' ) );
    }

    /**
     * Initialise settings
     * @return void
     */
    public function init() {
        $this->settings = $this->settings_fields();
    }

    /**
     * Add settings page to admin menu
     * @return void
     */
    public function add_menu_item() {
        $page = add_options_page( __( 'YouTube w/ Meta', 'jmayt_textdomain' ) , __( 'YouTube w/ Meta', 'jmayt_textdomain' ) , 'manage_options' , 'jmayt_settings' ,  array( $this, 'settings_page' ) );
        add_action( 'admin_print_styles-' . $page, array( $this, 'settings_assets' ) );
    }

    /**
     * Load settings JS & CSS
     * @return void
     */
    public function settings_assets() {

        // We're including the farbtastic script & styles here because they're needed for the colour picker
        // If you're not including a colour picker field then you can leave these calls out as well as the farbtastic dependency for the wpt-admin-js script below
        wp_enqueue_style( 'farbtastic' );
        wp_enqueue_script( 'farbtastic' );

        // We're including the WP media scripts here because they're needed for the image upload field
        // If you're not including an image upload then you can leave this function call out
        wp_enqueue_media();

        wp_register_script( 'wpt-admin-js', $this->assets_url . '/settings.js', array( 'farbtastic', 'jquery' ), '1.0.0' );
        wp_enqueue_script( 'wpt-admin-js' );
    }

    /**
     * Add settings link to plugin list table
     * @param  array $links Existing links
     * @return array 		Modified links
     */
    public function add_settings_link( $links ) {
        $settings_link = '<a href="options-general.php?page=jmayt_settings">' . __( 'Settings', 'jmayt_textdomain' ) . '</a>';
        array_push( $links, $settings_link );
        return $links;
    }

    /**
     * Build settings fields
     * @return array Fields to be displayed on settings page
     */
    private function settings_fields() {

        $settings['standard'] = array(
            'title'					=> __( 'Standard', 'jmayt_textdomain' ),
            'description'			=> __( 'These are fairly standard form input fields.', 'jmayt_textdomain' ),
            'fields'				=> array(
                array(
                    'id' 			=> 'text_field',
                    'label'			=> __( 'Some Text' , 'jmayt_textdomain' ),
                    'description'	=> __( 'This is a standard text field.', 'jmayt_textdomain' ),
                    'type'			=> 'text',
                    'default'		=> '',
                    'placeholder'	=> __( 'Placeholder text', 'jmayt_textdomain' )
                ),
                array(
                    'id' 			=> 'password_field',
                    'label'			=> __( 'A Password' , 'jmayt_textdomain' ),
                    'description'	=> __( 'This is a standard password field.', 'jmayt_textdomain' ),
                    'type'			=> 'password',
                    'default'		=> '',
                    'placeholder'	=> __( 'Placeholder text', 'jmayt_textdomain' )
                ),
                array(
                    'id' 			=> 'secret_text_field',
                    'label'			=> __( 'Some Secret Text' , 'jmayt_textdomain' ),
                    'description'	=> __( 'This is a secret text field - any data saved here will not be displayed after the page has reloaded, but it will be saved.', 'jmayt_textdomain' ),
                    'type'			=> 'text_secret',
                    'default'		=> '',
                    'placeholder'	=> __( 'Placeholder text', 'jmayt_textdomain' )
                ),
                array(
                    'id' 			=> 'text_block',
                    'label'			=> __( 'A Text Block' , 'jmayt_textdomain' ),
                    'description'	=> __( 'This is a standard text area.', 'jmayt_textdomain' ),
                    'type'			=> 'textarea',
                    'default'		=> '',
                    'placeholder'	=> __( 'Placeholder text for this textarea', 'jmayt_textdomain' )
                ),
                array(
                    'id' 			=> 'single_checkbox',
                    'label'			=> __( 'An Option', 'jmayt_textdomain' ),
                    'description'	=> __( 'A standard checkbox - if you save this option as checked then it will store the option as \'on\', otherwise it will be an empty string.', 'jmayt_textdomain' ),
                    'type'			=> 'checkbox',
                    'default'		=> ''
                ),
                array(
                    'id' 			=> 'select_box',
                    'label'			=> __( 'A Select Box', 'jmayt_textdomain' ),
                    'description'	=> __( 'A standard select box.', 'jmayt_textdomain' ),
                    'type'			=> 'select',
                    'options'		=> array( 'drupal' => 'Drupal', 'joomla' => 'Joomla', 'wordpress' => 'WordPress' ),
                    'default'		=> 'wordpress'
                ),
                array(
                    'id' 			=> 'radio_buttons',
                    'label'			=> __( 'Some Options', 'jmayt_textdomain' ),
                    'description'	=> __( 'A standard set of radio buttons.', 'jmayt_textdomain' ),
                    'type'			=> 'radio',
                    'options'		=> array( 'superman' => 'Superman', 'batman' => 'Batman', 'ironman' => 'Iron Man' ),
                    'default'		=> 'batman'
                ),
                array(
                    'id' 			=> 'multiple_checkboxes',
                    'label'			=> __( 'Some Items', 'jmayt_textdomain' ),
                    'description'	=> __( 'You can select multiple items and they will be stored as an array.', 'jmayt_textdomain' ),
                    'type'			=> 'checkbox_multi',
                    'options'		=> array( 'square' => 'Square', 'circle' => 'Circle', 'rectangle' => 'Rectangle', 'triangle' => 'Triangle' ),
                    'default'		=> array( 'circle', 'triangle' )
                )
            )
        );

        $settings['extra'] = array(
            'title'					=> __( 'Extra', 'jmayt_textdomain' ),
            'description'			=> __( 'These are some extra input fields that maybe aren\'t as common as the others.', 'jmayt_textdomain' ),
            'fields'				=> array(
                array(
                    'id' 			=> 'number_field',
                    'label'			=> __( 'A Number' , 'jmayt_textdomain' ),
                    'description'	=> __( 'This is a standard number field - if this field contains anything other than numbers then the form will not be submitted.', 'jmayt_textdomain' ),
                    'type'			=> 'number',
                    'default'		=> '',
                    'placeholder'	=> __( '42', 'jmayt_textdomain' )
                ),
                array(
                    'id' 			=> 'colour_picker',
                    'label'			=> __( 'Pick a colour', 'jmayt_textdomain' ),
                    'description'	=> __( 'This uses WordPress\' built-in colour picker - the option is stored as the colour\'s hex code.', 'jmayt_textdomain' ),
                    'type'			=> 'color',
                    'default'		=> '#21759B'
                ),
                array(
                    'id' 			=> 'an_image',
                    'label'			=> __( 'An Image' , 'jmayt_textdomain' ),
                    'description'	=> __( 'This will upload an image to your media library and store the attachment ID in the option field. Once you have uploaded an imge the thumbnail will display above these buttons.', 'jmayt_textdomain' ),
                    'type'			=> 'image',
                    'default'		=> '',
                    'placeholder'	=> ''
                ),
                array(
                    'id' 			=> 'multi_select_box',
                    'label'			=> __( 'A Multi-Select Box', 'jmayt_textdomain' ),
                    'description'	=> __( 'A standard multi-select box - the saved data is stored as an array.', 'jmayt_textdomain' ),
                    'type'			=> 'select_multi',
                    'options'		=> array( 'linux' => 'Linux', 'mac' => 'Mac', 'windows' => 'Windows' ),
                    'default'		=> array( 'linux' )
                )
            )
        );

        $settings = apply_filters( 'jmayt_settings_fields', $settings );

        return $settings;
    }

    /**
     * Register plugin settings
     * @return void
     */
    public function register_settings() {
        if( is_array( $this->settings ) ) {

            $option_name = 'jmaty_options_array';

            foreach( $this->settings as $section => $data ) {

                // Add section to page
                add_settings_section( $section, $data['title'], array( $this, 'settings_section' ), 'jmayt_settings' );

                foreach( $data['fields'] as $field ) {

                    // Validation callback for field
                    $validation = '';
                    if( isset( $field['callback'] ) ) {
                        $validation = $field['callback'];
                    }

                    // Register field

                    register_setting( 'jmayt_settings', $option_name, $validation );

                    // Add field to page
                    add_settings_field( $field['id'], $field['label'], array( $this, 'display_field' ), 'jmayt_settings', $section, array( 'field' => $field ) );
                }
            }
        }
    }

    public function settings_section( $section ) {
        $html = '<p> ' . $this->settings[ $section['id'] ]['description'] . '</p>' . "\n";
        echo $html;
    }

    /**
     * Generate HTML for displaying fields
     * @param  array $args Field data
     * @return void
     */
    public function display_field( $args ) {

        $field = $args['field'];

        $html = '';
        $option_array = array();
;       $option_array_name = $this->settings_base . $field['id'];
        $option_name = 'jmaty_options_array[' . $this->settings_base . $field['id'] . ']';
        $option_array = get_option( 'jmaty_options_array' );
        if(is_array($option_array))
        $option = $option_array[$option_array_name];

        $data = '';
        if( isset( $field['default'] ) ) {
            $data = $field['default'];
            if( $option ) {
                $data = $option;
            }
        }

        switch( $field['type'] ) {

            case 'text':
            case 'password':
            case 'number':
                $html .= '<input id="' . esc_attr( $field['id'] ) . '" type="' . $field['type'] . '" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" value="' . $data . '"/>' . "\n";
                break;

            case 'text_secret':
                $html .= '<input id="' . esc_attr( $field['id'] ) . '" type="text" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" value=""/>' . "\n";
                break;

            case 'textarea':
                $html .= '<textarea id="' . esc_attr( $field['id'] ) . '" rows="5" cols="50" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '">' . $data . '</textarea><br/>'. "\n";
                break;

            case 'checkbox':
                $checked = '';
                if( $option && 'on' == $option ){
                    $checked = 'checked="checked"';
                }
                $html .= '<input id="' . esc_attr( $field['id'] ) . '" type="' . $field['type'] . '" name="' . esc_attr( $option_name ) . '" ' . $checked . '/>' . "\n";
                break;

            case 'checkbox_multi':
                foreach( $field['options'] as $k => $v ) {
                    $checked = false;
                    if( in_array( $k, $data ) ) {
                        $checked = true;
                    }
                    $html .= '<label for="' . esc_attr( $field['id'] . '_' . $k ) . '"><input type="checkbox" ' . checked( $checked, true, false ) . ' name="' . esc_attr( $option_name ) . '[]" value="' . esc_attr( $k ) . '" id="' . esc_attr( $field['id'] . '_' . $k ) . '" /> ' . $v . '</label> ';
                }
                break;

            case 'radio':
                foreach( $field['options'] as $k => $v ) {
                    $checked = false;
                    if( $k == $data ) {
                        $checked = true;
                    }
                    $html .= '<label for="' . esc_attr( $field['id'] . '_' . $k ) . '"><input type="radio" ' . checked( $checked, true, false ) . ' name="' . esc_attr( $option_name ) . '" value="' . esc_attr( $k ) . '" id="' . esc_attr( $field['id'] . '_' . $k ) . '" /> ' . $v . '</label> ';
                }
                break;

            case 'select':
                $html .= '<select name="' . esc_attr( $option_name ) . '" id="' . esc_attr( $field['id'] ) . '">';
                foreach( $field['options'] as $k => $v ) {
                    $selected = false;
                    if( $k == $data ) {
                        $selected = true;
                    }
                    $html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $k ) . '">' . $v . '</option>';
                }
                $html .= '</select> ';
                break;

            case 'select_multi':
                $html .= '<select name="' . esc_attr( $option_name ) . '[]" id="' . esc_attr( $field['id'] ) . '" multiple="multiple">';
                foreach( $field['options'] as $k => $v ) {
                    $selected = false;
                    if( in_array( $k, $data ) ) {
                        $selected = true;
                    }
                    $html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $k ) . '" />' . $v . '</label> ';
                }
                $html .= '</select> ';
                break;

            case 'image':
                $image_thumb = '';
                if( $data ) {
                    $image_thumb = wp_get_attachment_thumb_url( $data );
                }
                $html .= '<img id="' . $option_name . '_preview" class="image_preview" src="' . $image_thumb . '" /><br/>' . "\n";
                $html .= '<input id="' . $option_name . '_button" type="button" data-uploader_title="' . __( 'Upload an image' , 'jmayt_textdomain' ) . '" data-uploader_button_text="' . __( 'Use image' , 'jmayt_textdomain' ) . '" class="image_upload_button button" value="'. __( 'Upload new image' , 'jmayt_textdomain' ) . '" />' . "\n";
                $html .= '<input id="' . $option_name . '_delete" type="button" class="image_delete_button button" value="'. __( 'Remove image' , 'jmayt_textdomain' ) . '" />' . "\n";
                $html .= '<input id="' . $option_name . '" class="image_data_field" type="hidden" name="' . $option_name . '" value="' . $data . '"/><br/>' . "\n";
                break;

            case 'color':
                ?><div class="color-picker" style="position:relative;">
                <input type="text" name="<?php esc_attr_e( $option_name ); ?>" class="color" value="<?php esc_attr_e( $data ); ?>" />
                <div style="position:absolute;background:#FFF;z-index:99;border-radius:100%;" class="colorpicker"></div>
                </div>
                <?php
                break;

        }

        switch( $field['type'] ) {

            case 'checkbox_multi':
            case 'radio':
            case 'select_multi':
                $html .= '<br/><span class="description">' . $field['description'] . '</span>';
                break;

            default:
                $html .= '<label for="' . esc_attr( $field['id'] ) . '"><span class="description">' . $field['description'] . '</span></label>' . "\n";
                break;
        }

        echo $html;
    }

    /**
     * Validate individual settings field
     * @param  string $data Inputted value
     * @return string       Validated value
     */
    public function validate_field( $data ) {
        if( $data && strlen( $data ) > 0 && $data != '' ) {
            $data = urlencode( strtolower( str_replace( ' ' , '-' , $data ) ) );
        }
        return $data;
    }

    /**
     * Load settings page content
     * @return void
     */
    public function settings_page() {

        // Build page HTML
        $html = '<div class="wrap" id="jmayt_settings">' . "\n";
        $html .= '<h2>' . __( 'YouTube w/ Meta Settings' , 'jmayt_textdomain' ) . '</h2>' . "\n";
        $html .= '<form method="post" action="options.php" enctype="multipart/form-data">' . "\n";

        // Setup navigation
        $html .= '<ul id="settings-sections" class="subsubsub hide-if-no-js">' . "\n";
        $html .= '<li><a class="tab all current" href="#all">' . __( 'All' , 'jmayt_textdomain' ) . '</a></li>' . "\n";

        foreach( $this->settings as $section => $data ) {
            $html .= '<li>| <a class="tab" href="#' . $section . '">' . $data['title'] . '</a></li>' . "\n";
        }

        $html .= '</ul>' . "\n";

        $html .= '<div class="clear"></div>' . "\n";

        // Get settings fields
        ob_start();
        settings_fields( 'jmayt_settings' );
        do_settings_sections( 'jmayt_settings' );
        $html .= ob_get_clean();

        $html .= '<p class="submit">' . "\n";
        $html .= '<input name="Submit" type="submit" class="button-primary" value="' . esc_attr( __( 'Save Settings' , 'jmayt_textdomain' ) ) . '" />' . "\n";
        $html .= '</p>' . "\n";
        $html .= '</form>' . "\n";
        $html .= '</div>' . "\n";

        echo $html;
    }

}