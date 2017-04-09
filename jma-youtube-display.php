<?php
/*
Plugin Name: JMA YouTube Display
Description: This plugin adds schema markup to youtube embeds and creates YouTube Lists
Version: 2.0
Author: John Antonacci
Author URI: http://cleansupersites.com
License: GPL2
*/

function jma_yt_quicktags() {

    if (wp_script_is('quicktags')){ ?>
        <script language="javascript" type="text/javascript">
            QTags.addButton( 'JMA_yt_wrap', 'yt_wrap', '[yt_video_wrap class="alignright" style="width: 300px; max-width: 50%"]', '[/yt_video_wrap]' );
            QTags.addButton( 'JMA_yt_video', 'yt_video', '[yt_video video_id="yt_video_id"]' );

            QTags.addButton( 'JMA_yt_grid', 'yt_grid', '[yt_grid yt_list_id="yt_list_id"]' );
        </script>
    <?php }
}
add_action('admin_print_footer_scripts','jma_yt_quicktags');

/**
 * Detect shortcodes in the global $post.
 */
function yt_detect_shortcode() {
    global $post;
    $shortcodes = array('yt_grid', 'yt_video', 'yt_wrap');
    $pattern = get_shortcode_regex($shortcodes);

    if (   preg_match_all( '/'. $pattern .'/s', $post->post_content, $matches )
        && array_key_exists( 2, $matches )
        && count($matches[2]) ) {
        add_action('wp_head', 'yt_styles');
    }
    return $matches[2];
}
add_action( 'wp', 'yt_detect_shortcode' );



$api_array = get_option( 'jma_yt_settings');
$api_code = $api_array['api_number'];
spl_autoload_register( 'jma_yt_autoloader' );
function jma_yt_autoloader( $class_name ) {
    if ( false !== strpos( $class_name, 'JMAYt' ) ) {
        $classes_dir = realpath(plugin_dir_path(__FILE__));
        $class_file = $class_name . '.php';
        require_once $classes_dir . DIRECTORY_SEPARATOR . $class_file;
    }
}



/**
 * Build settings fields
 * @return array Fields to be displayed on settings page
 */
$settings = array(
    /*
     * start of a new section
     * */

    'standard' => array(
        'title'					=> __( 'Standard', 'jmayt_textdomain' ),
        'description'			=> __( 'These are fairly standard form input fields.', 'jmayt_textdomain' ),

        /*
         * fields for this section section
         * */
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
    ),
    /*
     * start of a new section
     * */
    'extra' => array(
        'title'					=> __( 'Extra', 'jmayt_textdomain' ),
        'description'			=> __( 'These are some extra input fields that maybe aren\'t as common as the others.', 'jmayt_textdomain' ),

        /*
         * fields for this section section
         * */
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
    )
);



if( is_admin() )
    $jma_settings_page = new JMAYtSettings('jmayt', 'YouTube w/ Meta', $settings);

function yt_styles(){
    $active_sh_codes = yt_detect_shortcode();
    if(in_array('yt_grid', $active_sh_codes))
        $bootstrap = '.col-lg-020,.col-lg-1,.col-lg-2,.col-lg-3,.col-lg-4,.col-lg-6,.col-md-020,.col-md-1,.col-md-2,.col-md-3,.col-md-4,.col-md-6,.col-sm-020,.col-sm-1,.col-sm-2,.col-sm-3,.col-sm-4,.col-sm-6,.col-xs-020,.col-xs-1,.col-xs-2,.col-xs-3,.col-xs-4,.col-xs-6{position:relative;min-height:1px;padding-left:15px;padding-right:15px}.col-xs-020,.col-xs-1,.col-xs-2,.col-xs-3,.col-xs-4,.col-xs-6{float:left}.col-xs-6{width:50%}.col-xs-4{width:33.33333333%}.col-xs-020{width:20%}.col-xs-3{width:25%}.col-xs-2{width:16.66666667%}.col-xs-1{width:8.33333333%}@media (min-width:768px){.col-sm-020,.col-sm-1,.col-sm-2,.col-sm-3,.col-sm-4,.col-sm-6{float:left}.col-sm-6{width:50%}.col-sm-4{width:33.33333333%}.col-sm-3{width:25%}.col-sm-020{width:20%}.col-sm-2{width:16.66666667%}.col-sm-1{width:8.33333333%}}@media (min-width:992px){.col-md-020,.col-md-1,.col-md-2,.col-md-3,.col-md-4,.col-md-6{float:left}.col-md-6{width:50%}.col-md-4{width:33.33333333%}.col-md-3{width:25%}.col-md-020{width:20%}.col-md-2{width:16.66666667%}.col-md-1{width:8.33333333%}}@media (min-width:1200px){.col-lg-020,.col-lg-1,.col-lg-2,.col-lg-3,.col-lg-4,.col-lg-6{float:left}.col-lg-6{width:50%}.col-lg-4{width:33.33333333%}.col-lg-3{width:25%}.col-lg-020{width:20%}.col-lg-2{width:16.66666667%}.col-lg-1{width:8.33333333%}}';
    else
        $bootstrap = '';
    echo '<style type= "text/css">';
    echo $bootstrap;
    echo '.yt-item {margin-bottom: 20px}

.yt-list-wrap {
    margin-left: -15px; 
    margin-right: -15px;
    clear: both;
}
.yt-item .responsive-wrap {
	 position: relative;
	 padding-bottom: 56.25%;
	 height: 0;
	 padding-top: 0;
	 overflow: hidden;
}

.yt-item .responsive-wrap iframe,  
.yt-item .responsive-wrap object,  
.yt-item .responsive-wrap embed {
	 position: absolute;
	 top: 0;
	 left: 0;
	 width: 100%;
	 height: 100%;
}

@media(min-width: 535px){
    .xs-break {
        clear: both
    }
} 
@media(min-width: 767px){
    .has-sm .xs-break {
        clear: none
    }
    .yt-list-wrap .sm-break {
        clear: both
    }
}
@media(min-width: 767px){
    .has-sm .xs-break {
        clear: none
    }
    .yt-list-wrap .sm-break {
        clear: both
    }
}
@media(min-width: 991px){
    .has-md .sm-break, .has-md .xs-break {
        clear: none
    }
    .yt-list-wrap .md-break {
        clear: both
    }
}
@media(min-width: 1200px){
    .has-lg .md-break, .has-lg .sm-break, .has-lg .xs-break {
        clear: none
    }
    .yt-list-wrap .lg-break {
        clear: both
    }
}
}
</style>';
}

function jma_sanitize_array($inputs){
    foreach($inputs as $i => $input){
        $i = sanitize_text_field($i);
        $input = sanitize_text_field($input);
        $output[$i] =  $input;
    }
    return $output;
}

function jma_yt_grid($atts){
    global $api_code;
    $atts = jma_sanitize_array($atts);

    $you_tube_list = new JMAYtList($atts['yt_list_id'], $api_code);
    //form array of column atts and set defaults
    $responsive_cols = array(  'sm' => 3, 'xs' => 2);
    $has_break = ' has-sm has-xs';
    $count = 0;
    foreach($atts as $index => $att){
        if (strpos($index, '_cols') !== false) {
            //clear defaults the first time we find a _cols attribute
            if(!$count){
                $responsive_cols = array();
                $has_break = '';
            }
            $count++;
            $index = str_replace('_cols', '', $index);
            $responsive_cols[$index] = $att;
            $has_break .= ' has-' . "{$index}";
        }
    }
    ob_start();
    $attributes = array('id' => $atts['id'], 'class' => $atts['class'] . $has_break . ' yt-list-wrap clearfix', 'style' =>  $atts['style']);
    echo '<div ';
    foreach ($attributes as $name => $attribute) {//build opening div ala html shortcode
        if ($attribute) {// check to make sure the attribute exists
            echo $name . '="' . $attribute . '" ';
        }
    }
    echo '>';
    echo $you_tube_list->markup($responsive_cols);
    echo '</div><!--yt-list-wrap-->';
    $x = ob_get_contents();
    ob_end_clean();

    return str_replace("\r\n", '', $x);
}
add_shortcode('yt_grid','jma_yt_grid');

/**
 * @param $atts
 * @param null $content
 * @return mixed
 */
function jma_yt_video($atts){
    global $api_code;
    $atts = jma_sanitize_array($atts);
    ob_start();
    $html_attributes = array('id', 'class', 'style');
    $video_id = $atts['video_id'];
    $yt_video = new JMAYtVideo($video_id, $api_code);
    echo '<div ';
    foreach($atts as $name => $attribute){
        if($attribute && in_array($name, $html_attributes)){// check to make sure the attribute exists
            if($name == 'class')
                $attribute .= ' yt-item';
            echo $name . '="' . $attribute . '" ';
        }
    }
    echo '>';
    echo $yt_video->markup();
    echo '</div>';
    $x = ob_get_contents();
    ob_end_clean();
    return str_replace("\r\n", '', $x);
}
add_shortcode('yt_video','jma_yt_video');

/**
 * get YouTube video ID from URL
 *
 * @param string $url
 * @return string YouTube video id or FALSE if none found.
 */
function youtube_id_from_url($url) {
    if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match)) {
        $id = $match[1];
        return $id;
    }
    return false;
}

/**
 * @param $atts
 * @param null $content
 * @return mixed
 */
function jma_yt_video_wrap($atts, $content = null){
    global $api_code;
    $atts = jma_sanitize_array($atts);
    ob_start();
    $html_attributes = array('id', 'class', 'style');
    $video_id = youtube_id_from_url($content);
    $yt_video = new JMAYtVideo(sanitize_text_field($video_id), $api_code);
    echo '<div ';
    foreach($atts as $name => $attribute){
        if($attribute && in_array($name, $html_attributes)){// check to make sure the attribute exists
            if($name == 'class')
                $attribute .= ' yt-item';
            echo $name . '="' . $attribute . '" ';
        }
    }
    echo '>';
    echo $yt_video->markup();
    echo '</div>';
    $x = ob_get_contents();
    ob_end_clean();
    return str_replace("\r\n", '', $x);
}
add_shortcode('yt_video_wrap','jma_yt_video_wrap');
