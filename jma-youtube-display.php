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



$options_array = get_option('jmayt_options_array');
$api_code = $options_array['api'];
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
$col_array = array( 0 => 'inherit', 1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6);
$settings = array(
    /*
     * start of a new section
     * */

    'setup' => array(
        'title'					=> __( 'Setup', 'jmayt_textdomain' ),
        'description'			=> __( 'Setup options.', 'jmayt_textdomain' ),

        /*
         * fields for this section section
         * */
        'fields'				=> array(
            array(
                'id' 			=> 'api',
                'label'			=> __( 'YouTube Api value' , 'jmayt_textdomain' ),
                'description'	=> __( 'Api credentials for youtube <a target="_blank" href="https://console.developers.google.com/apis/dashboard">here</a>.', 'jmayt_textdomain' ),
                'type'			=> 'text',
                'default'		=> ''
            ),
            array(
                'id' 			=> 'cache',
                'label'			=> __( 'Cache Time' , 'jmayt_textdomain' ),
                'description'	=> __( 'Frequency of checks back to YouTube for info. Larger number for quicker page loads and to avoid hitting YouTube Api limits (3600 = 1 hr or 0 for testing only).', 'jmayt_textdomain' ),
                'type'			=> 'number',
                'default'		=> '3600'
            ),
            array(
                'id' 			=> 'dev',
                'label'			=> __( 'Dev Mode', 'jmayt_textdomain' ),
                'description'	=> __( 'Dev may allow plugin to function on Windows localhost (Production in production for security)', 'jmayt_textdomain' ),
                'type'			=> 'radio',
                'options'		=> array( 0 => 'Production' , 1 => 'Dev'),
                'default'		=> 0
            ),
            array(
                'id' 			=> 'bootstrap',
                'label'			=> __( 'Bootstrap', 'jmayt_textdomain' ),
                'description'	=> __( 'Bootstrap will add bootstrap through the plugin. If your theme includes full standard Bootstrap you should be able to save a little load time by clicking none. THIS ALSO IS NECESSARY FOR 5 COLUMN DISPLAY', 'jmayt_textdomain' ),
                'type'			=> 'radio',
                'options'		=> array( 1 => 'Bootstrap', 0 => 'None' ),
                'default'		=> 1
            ),
        )
    ),
    /*
     * start of a new section
     * */
    'display' => array(
        'title'					=> __( 'Display Options', 'jmayt_textdomain' ),
        'description'			=> __( 'These are some default display settings (they can be overridden with shortcode)', 'jmayt_textdomain' ),

        /*
         * fields for this section section
         * */
        'fields'				=> array(
            array(
                'id' 			=> 'button_font',
                'label'			=> __( 'Font color for expansion buttons on grids (button_font)', 'jmayt_textdomain' ),
                'type'			=> 'color',
                'default'		=> '#21759B'
            ),
            array(
                'id' 			=> 'button_bg',
                'label'			=> __( 'Background color for expansion buttons on grids (button_bg)', 'jmayt_textdomain' ),
                'type'			=> 'color',
                'default'		=> '#cbe0e9'
            ),
            array(
                'id' 			=> 'lg_cols',
                'label'			=> __( 'Large device columns (lg_cols)', 'jmayt_textdomain' ),
                'description'	=> __( 'For window width 1200+ px (inherit uses value from setting below).', 'jmayt_textdomain' ),
                'type'			=> 'select',
                'options'		=> $col_array,
                'default'		=> '0'
            ),
            array(
                'id' 			=> 'md_cols',
                'label'			=> __( 'Medium device columns (md_cols)', 'jmayt_textdomain' ),
                'description'	=> __( 'For window width 992+ px (inherit uses value from setting below).', 'jmayt_textdomain' ),
                'type'			=> 'select',
                'options'		=> $col_array,
                'default'		=> '0'
            ),
            array(
                'id' 			=> 'sm_cols',
                'label'			=> __( 'Small device columns (sm_cols)', 'jmayt_textdomain' ),
                'description'	=> __( 'For window width 768+ px (inherit uses value from setting below).', 'jmayt_textdomain' ),
                'type'			=> 'select',
                'options'		=> $col_array,
                'default'		=> '3'
            ),
            array(
                'id' 			=> 'xs_cols',
                'label'			=> __( 'Extra small device columns (xs_cols)', 'jmayt_textdomain' ),
                'description'	=> __( 'For window width -768 px.', 'jmayt_textdomain' ),
                'type'			=> 'select',
                'options'		=> $col_array,
                'default'		=> '2'
            )
        )
    )
);



if( is_admin() )
    $jma_settings_page = new JMAYtSettings('jmayt', 'YouTube w/ Meta', $settings);

function yt_styles(){
    global $options_array;
    $active_sh_codes = yt_detect_shortcode();
    if($options_array['bootstrap'] && in_array('yt_grid', $active_sh_codes))
        $bootstrap = '.col-lg-020,.col-lg-1,.col-lg-2,.col-lg-3,.col-lg-4,.col-lg-6,.col-md-020,.col-md-1,.col-md-2,.col-md-3,.col-md-4,.col-md-6,.col-sm-020,.col-sm-1,.col-sm-2,.col-sm-3,.col-sm-4,.col-sm-6,.col-xs-020,.col-xs-1,.col-xs-2,.col-xs-3,.col-xs-4,.col-xs-6{position:relative;min-height:1px;padding-left:15px;padding-right:15px}.col-xs-020,.col-xs-1,.col-xs-2,.col-xs-3,.col-xs-4,.col-xs-6{float:left}.col-xs-6{width:50%}.col-xs-4{width:33.33333333%}.col-xs-020{width:20%}.col-xs-3{width:25%}.col-xs-2{width:16.66666667%}.col-xs-1{width:8.33333333%}@media (min-width:768px){.col-sm-020,.col-sm-1,.col-sm-2,.col-sm-3,.col-sm-4,.col-sm-6{float:left}.col-sm-6{width:50%}.col-sm-4{width:33.33333333%}.col-sm-3{width:25%}.col-sm-020{width:20%}.col-sm-2{width:16.66666667%}.col-sm-1{width:8.33333333%}}@media (min-width:992px){.col-md-020,.col-md-1,.col-md-2,.col-md-3,.col-md-4,.col-md-6{float:left}.col-md-6{width:50%}.col-md-4{width:33.33333333%}.col-md-3{width:25%}.col-md-020{width:20%}.col-md-2{width:16.66666667%}.col-md-1{width:8.33333333%}}@media (min-width:1200px){.col-lg-020,.col-lg-1,.col-lg-2,.col-lg-3,.col-lg-4,.col-lg-6{float:left}.col-lg-6{width:50%}.col-lg-4{width:33.33333333%}.col-lg-3{width:25%}.col-lg-020{width:20%}.col-lg-2{width:16.66666667%}.col-lg-1{width:8.33333333%}}';
    else
        $bootstrap = '';
    echo '<style type= "text/css">';
    echo $bootstrap;
    echo '
.clearfix:before, 
.clearfix:after {
	content: " "; 
	display: table; 
} 
.clearfix:after { 
	clear: both; 
}
.yt-item {
    box-sizing: border-box;
    margin-bottom: 20px
}
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


    .xs-break {
        clear: both
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
    global $options_array;
    $api_code = $options_array['api'];
    $atts = jma_sanitize_array($atts);

    $you_tube_list = new JMAYtList($atts['yt_list_id'], $api_code);
    //form array of column atts and set defaults
    foreach($options_array as $i => $option){
        if((strpos($i, '_cols') !== false) && $option){
            $i = str_replace('_cols', '', $i);
            $has_break .= ' has-' . $i;
            $responsive_cols[$i] = $option;
        }
    }
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
            $has_break .= ' has-' . $index;
            $responsive_cols[$index] = $att;
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
