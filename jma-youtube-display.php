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

function jmayt_scripts() {
    wp_register_script( 'bootstrap-js', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js', array('jquery'), NULL, true );
    wp_register_style( 'bootstrap-css', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css', false, NULL,
        'all' );

    wp_enqueue_script( 'bootstrap-js' );
    wp_enqueue_style( 'bootstrap-css' );
    wp_enqueue_script( 'jmayt_js', plugins_url('/jmayt_js.js', __FILE__), array( 'jquery' ) );
}



function jma_yt_template_redirect(){
    if(jma_yt_detect_shortcode(array('yt_grid'))){
        add_action('wp_head', 'yt_styles');
        add_action( 'wp_enqueue_scripts', 'jmayt_scripts' );
    }
}
add_action('template_redirect', 'jma_yt_template_redirect');


/**
 * Detect shortcodes in the global $post.
 */
if(!function_exists('jma_yt_detect_shortcode')){
    function jma_yt_detect_shortcode( $needle = '', $post_item = 0 ){

        if($post_item){
            if(is_object($post_item))
                $post = $post_item;
            else
                $post = get_post($post_item);
        }else{
            global $post;
        }
        if(is_array($needle))
            $pattern = get_shortcode_regex($needle);
        else
            $pattern = get_shortcode_regex();

        preg_match_all( '/'. $pattern .'/s', $post->post_content, $matches );


        if(//if shortcode(s) to be searched for were passed and not found $return false

            array_key_exists( 2, $matches ) &&
            count( $matches[2] )

        ){
            if(!is_array($needle)){
                $post_sh_codes = $matches[2];
                $good_indexes = array();
                foreach ($post_sh_codes as $i => $post_sh_code){
                    if (strpos($post_sh_code, $needle) !== false)
                        $good_indexes[] = $i;
                }
                if(!count($good_indexes)){
                    $return = false;
                }else{
                    $count = count($matches);
                    for ($x = 0; $x < $count; $x++){
                        $sub_count = count($matches[$x]);
                        for ($y = 0; $y < $sub_count; $y++)
                            if(!in_array($y, $good_indexes))
                                unset($matches[$x][$y]);
                    }
                    $return = $matches;
                }
            }else{
                $return = $matches;
            }
        }else{
            $return = false;
        }

        return $return;
    }
}



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
$xs_col = array(  1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6);
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
                'description'	=> __( 'Dev may allow plugin to function on Windows localhost (Use Production in production for security)', 'jmayt_textdomain' ),
                'type'			=> 'radio',
                'options'		=> array( 0 => 'Production' , 1 => 'Dev'),
                'default'		=> 0
            )
        )
    ),
    /*
     * start of a new section
     * */
    'display' => array(
        'title'					=> __( 'Grid Display Options', 'jmayt_textdomain' ),
        'description'			=> __( 'These are some default display settings (they can be overridden with shortcode)', 'jmayt_textdomain' ),

        /*
         * fields for this section section
         * */
        'fields'				=> array(
            array(
                'id' 			=> 'item_font',
                'label'			=> __( 'Font color for grid item titles - blank your theme\'s title color (item_font)', 'jmayt_textdomain' ),
                'type'			=> 'color',
                'default'		=> 0
            ),
            array(
                'id' 			=> 'item_bg',
                'label'			=> __( 'Background color for grid items - blank for no bg (item_bg)', 'jmayt_textdomain' ),
                'type'			=> 'color',
                'default'		=> 0
            ),
            array(
                'id' 			=> 'item_border',
                'label'			=> __( 'Border color for grid items - blank for no border (item_border)', 'jmayt_textdomain' ),
                'type'			=> 'color',
                'default'		=> 0
            ),
            array(
                'id' 			=> 'item_gutter',
                'label'			=> __( 'Horizontal distance in px between grid items - best results even number between 0 and 30 (item_gutter)', 'jmayt_textdomain' ),
                'type'			=> 'number',
                'default'		=> '30'
            ),
            array(
                'id' 			=> 'item_spacing',
                'label'			=> __( 'Vertical distance in px between grid items (item_spacing)', 'jmayt_textdomain' ),
                'type'			=> 'number',
                'default'		=> '15'
            ),
            array(
                'id' 			=> 'button_font',
                'class'         => 'picker',
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
                'default'		=> 0
            ),
            array(
                'id' 			=> 'md_cols',
                'label'			=> __( 'Medium device columns (md_cols)', 'jmayt_textdomain' ),
                'description'	=> __( 'For window width 992+ px (inherit uses value from setting below).', 'jmayt_textdomain' ),
                'type'			=> 'select',
                'options'		=> $col_array,
                'default'		=> 0
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
                'options'		=> $xs_col,
                'default'		=> '2'
            )
        )
    )
);



if( is_admin() )
    $jma_settings_page = new JMAYtSettings('jmayt', 'YouTube w/ Meta', $settings);

function yt_styles(){
    global $options_array;
    echo '<style type= "text/css">';
    echo '
.col-md-020{position:relative;min-height:1px;padding-left:15px;padding-right:15px}.col-xs-020{float:left}.col-xs-020{width:20%}@media (min-width:768px){.col-sm-020{float:left}.col-sm-020{width:20%}}@media (min-width:992px){.col-md-020{float:left}.col-md-020{width:20%}}@media (min-width:1200px){.col-lg-020{float:left}.col-lg-020{width:20%}}
.clearfix:before, 
.clearfix:after {
	content: " "; 
	display: table; 
} 
.clearfix:after { 
	clear: both; 
}
.jmayt-item-wrap {
    box-sizing: border-box;
    margin-bottom: 20px
}
.jmayt-list-wrap {
    margin-left: -15px; 
    margin-right: -15px;
    clear: both;
}
.jmayt-item-wrap .responsive-wrap {
	 position: relative;
	 padding-bottom: 56.25%;
	 height: 0;
	 padding-top: 0;
	 overflow: hidden;
}
.jmayt-item-wrap .responsive-wrap iframe {
	 position: absolute;
	 top: 0;
	 left: 0;
	 width: 100%;
	 height: 100%;
}
.jmayt-video-wrap {
    background: rgba(0,0,0,0.8);
}
.jmayt-btn, button:focus {
    position: absolute;
    z-index: 10;
    top: 0;
    left: 0;
    padding: 7px 10px;
    font-size: 20px;
    font-family: \'Glyphicons Halflings\';
    color: ' . $options_array['button_font'] . ';
    background: ' . $options_array['button_bg'] . ';
    border: solid 1px ' . $options_array['button_font'] . ';
    -webkit-transition: all .2s; /* Safari */
    transition: all .2s;
    
}
.jmayt-btn:hover {
    color: ' . $options_array['button_bg'] . ';
    background: ' . $options_array['button_font'] . ';
}
.xs-break {
    clear: both
}
@media(min-width: 767px){
    .has-sm .xs-break {
        clear: none
    }
    .jmayt-list-wrap .sm-break {
        clear: both
    }
}
@media(min-width: 991px){
    .has-md .sm-break, .has-md .xs-break {
        clear: none
    }
    .jmayt-list-wrap .md-break {
        clear: both
    }
}
@media(min-width: 1200px){
    .has-lg .md-break, .has-lg .sm-break, .has-lg .xs-break {
        clear: none
    }
    .jmayt-list-wrap .lg-break {
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
    $attributes = array('id' => $atts['id'], 'class' => $atts['class'] . $has_break . ' jmayt-list-wrap clearfix', 'style' =>  $atts['style']);
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
