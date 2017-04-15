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

//helper function for styles-builder.php
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

//helper function for dynamic-styles-builder.php
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
    $item_gutter = $options_array['item_gutter'] % 2 == 0? $options_array['item_gutter']/2: ($options_array['item_gutter']-1)/2;
    // FORMAT FOR INPUT
// $dynamic_styles[] = array($selector, array($property, $value)[,array($property, $value)...])

//in format above format media queries  i.e. max-768@$selector, ...
// $dynamic_styles[] = array(max(or min)-$width@$selector, array($property, $value)[,array($property, $value)...])
    $jmayt_styles[10] =  array('.yt-list-item' ,
        array('position', 'relative'),
        array('min-height', '1px'),
        array('padding-left', $item_gutter . 'px'),
        array('padding-right', $item_gutter . 'px'),
    );
    $jmayt_styles[20] =  array('.jmayt-list-wrap' ,
        array('clear', 'both'),
        array('margin-left', -$item_gutter . 'px'),
        array('margin-right', -$item_gutter . 'px'),
    );
    $jmayt_styles[30] =  array('.jmayt-item-wrap' ,
        array('box-sizing', 'border-box'),
        array('margin-bottom', $options_array['item_spacing'] . 'px'),
    );
    if($options_array['item_border'] || $options_array['item_bg']) {
        $jmayt_styles[50] = array('.jmayt-item h3',
            array('padding-left', '5px'),
            array('padding-right', '5px'),
        );
        if ($options_array['item_bg']) {
            $jmayt_styles[60] = array('.jmayt-item',
                array('background', $options_array['item_bg']),
            );
        }
        if ($options_array['item_border']){
            $jmayt_styles[40] = array('.jmayt-item',
                array('border', 'solid 2px ' . $options_array['item_border']),
            );
        }
    }
    $font_size = $options_array['item_font_size']? array('font-size', $options_array['item_font_size'] . 'px'):array();
    $jmayt_styles[70] =  array('.jmayt-item h3' ,
        array('padding-top', '5px'),
        array('padding-bottom', '5px'),
        array('margin', ' 0'),
        array('color', $options_array['item_font_color']),
        array('text-align', $options_array['item_font_alignment']),
        $font_size
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
    echo '<style type= "text/css">';
    echo '
.col-xs-020{float:left}.col-xs-020{width:20%}@media (min-width:768px){.col-sm-020{float:left}.col-sm-020{width:20%}}@media (min-width:992px){.col-md-020{float:left}.col-md-020{width:20%}}@media (min-width:1200px){.col-lg-020{float:left}.col-lg-020{width:20%}}
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
}' . $jmayt_css . '</style>';
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
