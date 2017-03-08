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
		QTags.addButton( 'JMA_yt_video', 'yt_video', '[yt_video video_id="yt_video_id"' );

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
    $pattern = get_shortcode_regex();

    if (   preg_match_all( '/'. $pattern .'/s', $post->post_content, $matches )
        && array_key_exists( 2, $matches )
        && in_array( 'yt_grid', $matches[2] )
    ) {
        add_action('wp_head', 'yt_styles');
    }
}
add_action( 'wp', 'yt_detect_shortcode' );


//$api_code = 'AIzaSyAt1C_vZWgEp8Ba6ISkslwGCZzKUJaO5BE';//cowebop@gmail AIzaSyAtT4Wufdkhv1z_-omYM8XtkVxWMlsIfFA
//tracy@schloss AIzaSyAt1C_vZWgEp8Ba6ISkslwGCZzKUJaO5BE
$api_array = get_option( 'jma_yt_api');
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
    echo '<style type= "text/css">
.yt-item {margin-bottom: 20px}

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
    .sm-break {
        clear: both
    }
}
@media(min-width: 991px){
    .has-md .sm-break, .has-md .xs-break {
        clear: none
    }
    .md-break {
        clear: both
    }
}
}
</style>';
}


function jma_yt_grid($atts){
	global $api_code;
	extract(shortcode_atts(array(
		'yt_list_id' => '',
		'id' => '',
		'class' => '',
		'style' => ''
	), $atts));
	$you_tube_list = new JMAYtList($yt_list_id, $api_code);
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
    $attributes = array('id' => $id, 'class' => $class . $has_break . ' youtube-list-wrap clearfix', 'style' => 'margin-left: -15px; margin-right: -15px;clear: both;' . $style);
    echo '<div ';
    foreach ($attributes as $name => $attribute) {//build opening div ala html shortcode
        if ($attribute) {// check to make sure the attribute exists
            echo $name . '="' . $attribute . '" ';
        }
    }
    echo '>';
    echo $you_tube_list->markup($responsive_cols);
    echo '</div><!--youtube-list-wrap-->';
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
    ob_start();
    $html_attributes = array('id', 'class', 'style');
    $video_id = youtube_id_from_url($content);
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
add_shortcode('yt_video_wrap','jma_yt_video_wrap');
