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

if( is_admin() )
    $jma_settings_page = new JMAYtSettings();

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
