<?php
class JMAYtVideo {
    var $id;
    var $api;
    var $col_space;
    var $box_string;
    var $button_string;
    var $h3_string;
    var $trans_atts_id;
    var $item_font_length;

    public function __construct($id_code, $api_code){
        $this->api = $api_code;
        $this->id = $id_code;

    }
    protected function curl($url){
        global $options_array;
        $curl = curl_init($url);

        $whitelist = array('127.0.0.1', "::1");
        if($options_array['dev'] && in_array($_SERVER['REMOTE_ADDR'], $whitelist)){
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//for localhost
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);//for localhost
        }

        //curl_setopt($curl, CURLOPT_SSLVERSION,3);//forMAMP
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        curl_close($curl);
        $return = json_decode($result, true);
        if(!$return || array_key_exists ('error', $return))
            $return = false;

        return $return;
    }

    /*
     * function video_snippet()
     * @param string $id  a video id
     * @uses string $this->api the api key for the youtube site
     * @return array the snippet array for an individual video
     *
     * */
    private function video_snippet($id){
        if(substr( $id, 0, 4 ) === "http"){
            if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match)) {
                $id = $match[1];
            }
        }
        $snippet = array();
        $youtube = 'https://www.googleapis.com/youtube/v3/videos?part=snippet&id=' . $id . '&fields=items%2Fsnippet&key=' . $this->api;
        $curl_array = JMAYtVideo::curl($youtube);
        if($curl_array)
            $snippet = $curl_array['items'][0]['snippet'];
        return $snippet;
    }

    /*
 * function map_meta()
 * @param $id string a video id (only for embed url)
 * @param $snippet the snippet array from youtube api
 * @return $yt_meta_array_items array schema values mapped to schema properties
 *
 * */
    private function map_meta($snippet, $id){//map youtude array to schema proprties
        $yt_meta_array_items['name'] = $snippet['title'];
        $yt_meta_array_items['publisher'] = $snippet['channelTitle'];
        $yt_meta_array_items['description'] = $snippet['description'];
        $yt_meta_array_items['thumbnailUrl'] = $snippet['thumbnails']['default']['url'];
        $yt_meta_array_items['embedURL'] = 'https://www.youtube.com/embed/' . $id;
        $yt_meta_array_items['uploadDate'] = $snippet['publishedAt'];
        return $yt_meta_array_items;
    }

    /*
     * function process_display_atts() processes relavent attributes (if present) into object properties
     * for use by single_html() and markup()
     * @param array $atts shortcode attributes to be processed
     *
     * @return array $return ('gutter', 'display') attribute - value pairs to be returned
     * to shortcode function
     *
     * */
    public function process_display_atts($atts){
        $this->col_space =
        $this->box_string =
        $this->button_string =
        $this->h3_string =
        $this->trans_atts_id = '';
        $this->item_font_length = -23;
        $return = array();
        //the relavent atributes to check for values
        $display_att_list = array( 'item_font_color', 'item_font_size', 'item_font_alignment', 'item_font_length', 'item_bg', 'item_border', 'item_gutter','item_spacing','button_font','button_bg', 'width', 'alignment' );
        //produce $display_atts with relavent values (if any)
        foreach($atts as $index => $att){
            if ( in_array( $index, $display_att_list ) ) {
                $trans_atts_id .= $index . $att;
                $display_atts[$index] = $att;
            }
        }
        //check for values and process producing style strings for each
        if(count($display_atts)){
            extract($display_atts);
            $this->trans_atts_id = $trans_atts_id;
            //number of characters in h3
            if(isset($item_font_length)) $this->item_font_length = $item_font_length;
            //box gutter and vertical spacing
            if($item_gutter || $item_spacing){
                if($item_gutter){
                    $item_gutter = floor($item_gutter/2);
                    $return['gutter'] = 'margin-left:-' . $item_gutter . 'px;margin-right:-' . $item_gutter . 'px;';
                }

                $gutter = $item_gutter? 'padding-left:' . $item_gutter . 'px;padding-right:' . $item_gutter . 'px;':'';
                $spacing = $item_spacing? 'margin-bottom:' . $item_spacing . 'px;':'';
                $format = ' style="%s%s" ';
                $col_space = sprintf($format, $spacing, $gutter);
                $this->col_space = $col_space;
            }
            //single box width and alignment
            if($width || $alignment){
                $return['display'] = $width? 'width:' . $width . ';': '';
                if($alignment == 'right' || $alignment == 'left') {
                    $return['display'] .= 'float:' . $alignment . ';';
                    $return['display'] .= 'margin-top: 5px;';
                    $op = $alignment == 'left'? 'right':'left';
                    $return['display'] .= 'margin-' . $op . ':20px;';
                }
            }
            //single or list box border and bg
            if($item_bg || $item_border){
                $bg = $item_bg? 'background-color:' . $item_bg . ';':'';
                $border = $item_border? 'border:solid 2px ' . $item_border . '':'';
                $format = ' style="%s%s" ';
                $box_string = sprintf($format, $bg, $border);
                $this->box_string = $box_string;
            }
            //expansion button font color and bg
            if($button_font || $button_bg){
                $color = $button_font? 'color:' . $button_font . ';':'';
                $bg = $button_bg? 'background-color:' . $button_bg . ';':'';
                $format = ' style="%s%s" ';
                $button_string = sprintf($format, $bg, $color);
                $this->button_string = $button_string;
            }
            //h3 color size and align
            if($item_font_color || $item_font_size || $item_font_alignment){
                $color = $item_font_color? 'color:' . $item_font_color . ';':'';
                $size = $item_font_size? 'font-size:' . $item_font_size . 'px;':'';
                $align = $item_font_alignment? 'text-align:' . $item_font_alignment . ';':'';
                $format = ' style="%s%s%s" ';
                $h3_string = sprintf($format, $color, $size, $align);
                $this->h3_string = $h3_string;
            }
        }
        return $return;
    }

    /*
     * function jma_youtube_schema_html()
     * returns schema html from $yt_meta_array_items array (see above)
     *
 * */
    function jma_youtube_schema_html($yt_meta_array_items){

        foreach($yt_meta_array_items as $prop => $yt_meta_array_item)
            $return .= '<meta itemprop="' . $prop . '" content="' . str_replace('"', '\'',$yt_meta_array_item)   . '" />';
        return $return;
    }

    /*
     * function single_html()
     * @param string $id - the video id
     * @uses $this->box_string $this->h3_string from process_display_atts()
     * returns video box html
     *
    * */
    protected function single_html($id){
        global $options_array;
        $snippet = JMAYtVideo::video_snippet($id);
        $meta_array = JMAYtVideo::map_meta($snippet, $id);
        $h3_title = $meta_array['name'];
        $elipsis = '';
        if($this->item_font_length == -23  && $options_array['item_font_length']){
            $length = $options_array['item_font_length'];
        }elseif($this->item_font_length > 0){
            $length = $this->item_font_length;
        }else{
            $length = 0;
        }
        if($length && (strlen($meta_array['name']) > $length)){
            $h3_title = wordwrap($meta_array['name'], $length);
            $h3_title = substr($h3_title, 0, strpos($h3_title, "\n"));
            $elipsis = '&nbsp;...';
        }
        $return .= '<div class="jmayt-item-wrap"' . $this->box_string . '>';
        $return .= '<div class="jmayt-item">';
        $return .= '<div class="jmayt-video-parent">';
        $return .= '<div class="jmayt-video-wrap">';
        $return .= '<div class="jma-responsive-wrap" itemprop="video" itemscope itemtype="http://schema.org/VideoObject">';
        $return .= '<button class="jmayt-btn"' . $this->button_string . '>&#xe140;</button>';
        $return .= JMAYtVideo::jma_youtube_schema_html($meta_array);
        $return .=  '<iframe src="https://www.youtube.com/embed/' . $id . '?rel=0&amp;showinfo=0" frameborder="0" allowfullscreen></iframe>';
        $return .= '</div><!--jma-responsive-wrap-->';
        $return .= '</div><!--jmayt-video-wrap-->';
        $return .= '</div><!--jmayt-video-parent-->';
        $return .= '<div class="jmayt-text-wrap">';
        $return .= '<h3 class="jmayt-title" ' . $this->h3_string . '>' . $h3_title . $elipsis . '</h3>';
        $return .= '</div><!--jmayt-text-wrap-->';
        $return .= '</div><!--jmayt-item-->';
        $return .= '</div><!--jmayt-item-wrap-->';
        return $return;
    }

    /*
     * function markup() creates transient id, checks fortransient and calls single_html()
     * if needed
     * @global $options_array - for cache period
     * returns video html
     *
    * */
    public function markup(){
        global $options_array;
        $trans_id = 'jmaytvideo' . $this->id . $this->trans_atts_id;
        $return = get_transient( $trans_id );
        if(false === $return || !$options_array['cache']) {//if cache at 0
            $return = JMAYtVideo::single_html($this->id);
            set_transient( $trans_id, $return, $options_array['cache'] );
        }
        return $return;
    }
}