<?php
/*
Plugin Name: JMA YouTube Display
Description: This plugin adds schema markup to youtube embeds and creates YouTube Lists
Version: 2.0
Author: John Antonacci
Author URI: http://cleansupersites.com
License: GPL2
*/

/*
 * function jma_yt_quicktags
 * add shortcode tags to text toolbar
 *
 * */
function jma_yt_quicktags() {

    if (wp_script_is('quicktags')){ ?>
        <script language="javascript" type="text/javascript">
            QTags.addButton( 'JMA_yt_wrap', 'yt_wrap', '[yt_video_wrap width="100%" alignment="none"]', '[/yt_video_wrap]' );
            QTags.addButton( 'JMA_yt_video', 'yt_video', '[yt_video video_id="yt_video_id" width="100%" alignment="none"]' );

            QTags.addButton( 'JMA_yt_grid', 'yt_grid', '[yt_grid yt_list_id="yt_list_id"]' );
        </script>
    <?php }
}
add_action('admin_print_footer_scripts','jma_yt_quicktags');

function jmayt_scripts() {

    wp_enqueue_style( 'jmayt_bootstrap_css', plugins_url('/jmayt_bootstrap.css', __FILE__) );
    wp_enqueue_script( 'jmayt_js', plugins_url('/jmayt_js.js', __FILE__), array( 'jquery' ) );
    $custom_css = yt_styles();
    wp_add_inline_style( 'jmayt_bootstrap_css', $custom_css );

}

function jmayt_add_classes($classes) {
    $classes[] = 'jmaty-css';
    return $classes;
}

function jma_yt_template_redirect(){
    if(jma_yt_detect_shortcode(array('yt_grid', 'yt_video', 'yt_video_wrap'))){
        add_action( 'wp_enqueue_scripts', 'jmayt_scripts' );
        add_filter('body_class','jmayt_add_classes');
    }
}
add_action('template_redirect', 'jma_yt_template_redirect');


/**
 * function jma_yt_detect_shortcode Detect shortcodes in a post object,
 *  from a post id or from global $post.
 * @param string or array $needle - the shortcode(s) to search for
 * use array for multiple values
 * @param int or object $post_item - the post to search (defaults to current)
 * @return boolean $return
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
        elseif(is_string($needle))
            $pattern = get_shortcode_regex(array($needle));
        else
            $pattern = get_shortcode_regex();

        preg_match_all( '/'. $pattern .'/s', $post->post_content, $matches );


        if(//if shortcode(s) to be searched for were passed and not found $return false

            array_key_exists( 2, $matches ) &&
            count( $matches[2] )
        ){
            $return = $matches;
        }else{
            $return = false;
        }

        return $return;
    }
}

//helper function for yt_styles()
function jmayt_output($inputs) {
    $output = array();
    foreach($inputs as $input){
        $numArgs = count($input);
        if($numArgs < 2)
            return;	//bounces input if no property => value pairs are present
        $pairs = array();
        for($i = 1; $i < $numArgs; $i++){
            $x = $input[$i];
            $pairs[] = array(
                'property' => $x[0],
                'value' => $x[1]
            );
        }
        $add = array($input[0] => $pairs);
        $output = array_merge_recursive($output, $add);
    }
    return $output;
}

//helper function for yt_styles()
// media queries in format max(or min)-$width@$selector, .....
// so we explode around @, then around - (first checking to see if @ symbol is present)
function jmayt_build_css($css_values) {
    $return = ' {}';
    foreach($css_values as  $k => $css_value) {
        $has_media_query = (strpos ( $k , '@' ));
        if($has_media_query){
            $exploded = explode('@', $k);
            $media_query_array = explode('-', $exploded[0]);
            $k = $exploded[1];

            $return .= '@media (' . $media_query_array[0] . '-width:' . $media_query_array[1] . "px) {\n";
        }
        $return .= $k . "{\n";
        foreach($css_value as $value){
            if($value['value'])
                $return .= $value['property'] . ': ' . $value['value'] . ";\n";
        }
        $return .= "}\n";
        if($has_media_query){
            $return .= "}\n";
        }
    }
    return $return;
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
                'id' 			=> 'item_font_color',
                'label'			=> __( 'Font color for grid item titles - blank your theme\'s title color (item_font_color)', 'jmayt_textdomain' ),
                'type'			=> 'color',
                'default'		=> 0
            ),
            array(
                'id' 			=> 'item_font_size',
                'label'			=> __( 'Font size for grid item titles - 0 your theme\'s title size (item_font_size)', 'jmayt_textdomain' ),
                'type'			=> 'number',
                'default'		=> 0
            ),
            array(
                'id' 			=> 'item_font_alignment',
                'label'			=> __( 'Font alignment for grid item titles (item_font_alignment)', 'jmayt_textdomain' ),
                'type'			=> 'radio',
                'options'		=> array( 'left' => 'left' , 'center' => 'center', 'right' => 'right'),
                'default'		=> 'left'
            ),
            array(
                'id' 			=> 'item_font_char',
                'label'			=> __( 'The maximun number of characters for grid item titles - 0 for whole title (item_font_char)', 'jmayt_textdomain' ),
                'type'			=> 'number',
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

/**
 * function yt_styles add the plugin specific styles
 * @return $css the css string
 */
function yt_styles(){
    global $options_array;
    $item_gutter = floor($options_array['item_gutter']/2);
    // FORMAT FOR INPUT
// $jmayt_styles[] = array($selector, array($property, $value)[,array($property, $value)...])

//in format above format media queries  i.e. max-768@$selector, ...
// $jmayt_styles[] = array(max(or min)-$width@$selector, array($property, $value)[,array($property, $value)...])
    $jmayt_styles[10] =  array('.jmayt-list-wrap' ,
        array('clear', 'both'),
        array('margin-left', -$item_gutter . 'px'),
        array('margin-right', -$item_gutter . 'px'),
    );
    $jmayt_styles[20] =  array('.jmayt-item-wrap' ,
        array('position', 'relative'),
        array('box-sizing', 'border-box'),
    );
    $jmayt_styles[30] =  array('.jmayt-list-item.col' ,
        array('min-height', '1px'),
        array('padding-left', $item_gutter . 'px'),
        array('padding-right', $item_gutter . 'px'),
        array('margin-bottom', $options_array['item_spacing'] . 'px'),
    );
    if ($options_array['item_border'] || $options_array['item_bg']){
        $border_array = $options_array['item_border']? array('border', 'solid 2px ' . $options_array['item_border']):
            array();
        $bg_array = $options_array['item_bg']? array('background', $options_array['item_bg']): array();
        $jmayt_styles[50] = array('.jmayt-item-wrap',
            $border_array,
            $bg_array
        );
    }
    $font_size = $lg_font_size = $options_array['item_font_size'];
    if($font_size)
        $font_size = ceil($font_size*0.7);
    $font_size_str = $options_array['item_font_size']? array('font-size', $font_size . 'px')
        :array();
    $lg_font_size_str = $options_array['item_font_size']? array('font-size', $lg_font_size . 'px')
        :array();
    $jmayt_styles[60] =  array('.jmayt-item h3' ,
        array('padding', '5px'),
        array('margin', ' 0'),
        array('color', $options_array['item_font_color']),
        array('text-align', $options_array['item_font_alignment']),
        $font_size_str
    );
    $jmayt_styles[70] =  array('.jmayt-item h3:first-line' ,
        $lg_font_size_str
    );
    $jmayt_styles[80] =  array('.jmayt-btn, .jmayt-btn:focus' ,
        array('position', 'absolute'),
        array('z-index', '10'),
        array('top', ' 0'),
        array('left', ' 0'),
        array('padding', '7px 10px'),
        array('font-size', '18px'),
        array('font-family', 'Glyphicons Halflings'),
        array('color', $options_array['button_font']),
        array('background', $options_array['button_bg']),
        array('border', 'solid 1px ' . $options_array['button_font']),
        array('-webkit-transition', 'all .2s'),
        array('transition', 'all .2s'),
    );
    $jmayt_styles[90] =  array('.jmayt-btn:hover' ,
        array('color', $options_array['button_bg']),
        array('background', $options_array['button_font']),
    );

    $jmayt_values =  jmayt_output($jmayt_styles);
    /* create html output from  $jma_css_values */


    $jmayt_css = jmayt_build_css($jmayt_values);
    $css = '
.col-xs-020{float:left;width:20%}@media (min-width:768px){.col-sm-020{float:left;width:20%}}@media (min-width:992px){.col-md-020{float:left;width:20%}}@media (min-width:1200px){.col-lg-020{float:left;width:20%}}
.clearfix:before, .clearfix:after {
    zoom:1;
    display: table;
    content: "";
}
.clearfix:after {
    clear: both
}
.jmayt-video-wrap .jma-responsive-wrap iframe {
	 position: absolute;
	 top: 0;
	 left: 0;
	 width: 100%;
	 height: 100%;
}
.jmayt-video-wrap {
    background: rgba(0,0,0,0.8);
    padding-bottom: 56.25%;
    position: relative;
}
.jmayt-text-wrap {
    position: relative;
}
.jmayt-list-wrap, .jmayt-single-item {
    margin-bottom: 20px
}
.jmayt-list-wrap .jmayt-text-wrap h3 {
    padding: 5px;
    position: absolute; 
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 90%;
}
.jmayt-item-wrap .jmayt-text-wrap h3 {
    width: 90%;
}
.jmayt-video-wrap .jma-responsive-wrap {
	padding-bottom: 56.25%;
	overflow: hidden;
    position: absolute; 
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 100%;
}
.jmayt-fixed {
    position: absolute;
    z-index: 9999;
    top: 0;
    left: 0;
}
.jmayt-list-wrap .xs-break {
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
}' . $jmayt_css;
    return $css;
}

function jma_sanitize_array($inputs){
    foreach($inputs as $i => $input){
        $i = sanitize_text_field($i);
        $input = sanitize_text_field($input);
        $output[$i] =  $input;
    }
    return $output;
}

/**
 * function jma_yt_grid shortcode for the grid
 * @param array $atts - the shortcode attributes
 * @return the shortcode string
 */
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
    $style = $you_tube_list->process_display_atts($atts);
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
    $attributes = array(
        'id' => $atts['id'],
        'class' => $atts['class'] . $has_break . ' jmayt-list-wrap clearfix',
        'style' => $style['gutter'] . $style['display'] . $atts['style']
    );
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
 * function jma_yt_video_wrap_html shortcode for the grid
 * @param array $atts - the shortcode attributes
 * @param string $video_id - the video id (either previously extracted from $atts or from content
 * (depending on whether its the wrap shortcode)
 * @return the shortcode string
 */
function jma_yt_video_wrap_html($atts, $video_id){
    global $api_code;
    $atts = jma_sanitize_array($atts);
    $html_attributes = array('id', 'class', 'style');
    $yt_video = new JMAYtVideo(sanitize_text_field($video_id), $api_code);
    $style = $yt_video->process_display_atts($atts);
    $attributes = array(
        'id' => $atts['id'],
        'class' => $atts['class'] . ' jmayt-single-item clearfix',
        'style' => $style['display'] . $atts['style']
    );
    echo '<div ';
    foreach($attributes as $name => $attribute){
        if($attribute && in_array($name, $html_attributes)){// check to make sure the attribute exists
            echo $name . '="' . $attribute . '" ';
        }
    }
    echo '>';
    echo $yt_video->markup();
    echo '</div><!--jmayt-item-wrap-->';
}

/**
 * @param $atts
 * @uses jma_yt_video_wrap_html
 * @return mixed
 */
function jma_yt_video($atts){
    $video_id = $atts['video_id'];
    ob_start();
    jma_yt_video_wrap_html($atts, $video_id);
    $x = ob_get_contents();
    ob_end_clean();
    return str_replace("\r\n", '', $x);
}
add_shortcode('yt_video','jma_yt_video');

/**
 * @param $atts
 * @param null $content
 * @uses jma_yt_video_wrap_html
 * @return mixed
 */
function jma_yt_video_wrap($atts, $content = null){
    $video_id = youtube_id_from_url($content);
    ob_start();
    jma_yt_video_wrap_html($atts, $video_id);
    $x = ob_get_contents();
    ob_end_clean();
    return str_replace("\r\n", '', $x);
}
add_shortcode('yt_video_wrap','jma_yt_video_wrap');
