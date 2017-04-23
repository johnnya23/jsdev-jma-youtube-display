<?php
class JMAYtVideo {
    var $id;
    var $api;
    var $col_space;
    var $box_string;
    var $button_string;
    var $h3_string;
    var $trans_atts_id;
    var $item_font_char;

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
        if(array_key_exists ('error', $return))
            $return = false;

        return $return;
    }

    /*
     * function meta_snippet()
     * @param string $id  a video id
     * @param string $api the api key for the youtube site
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
 * $id string a video id (only for embed url)
 * $snippet the snippet array from youtube api
 * returns schema values mapped to schema properties in the $yt_meta_array_items array
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

    public function process_display_atts($atts){
        $this->col_space =
        $this->box_string =
        $this->button_string =
        $this->h3_string =
        $this->trans_atts_id =
        $this->item_font_char =
        $return = '';
        $display_att_list = array( 'item_font_color', 'item_font_size', 'item_font_alignment', 'item_font_char', 'item_bg', 'item_border', 'item_gutter','item_spacing','button_font','button_bg' );
        foreach($atts as $index => $att){
            if ( in_array( $index, $display_att_list ) ) {
                $trans_atts_id .= $index . $att;
                $display_atts[$index] = $att;
            }
        }
        if(count($display_atts)){
            extract($display_atts);
            $this->trans_atts_id = $trans_atts_id;
            if($item_font_char) $this->item_font_char = $item_font_char;
            if($item_gutter || $item_spacing){
                if($item_gutter){
                    $item_gutter = floor($item_gutter/2);
                    $return = 'margin-left:-' . $item_gutter . 'px;margin-right:-' . $item_gutter . 'px';
                }

                $gutter = $item_gutter? 'padding-left:' . $item_gutter . 'px;padding-right:' . $item_gutter . 'px;':'';
                $spacing = $item_spacing? 'margin-bottom:' . $item_spacing . 'px;':'';
                $format = ' style="%s%s" ';
                $col_space = sprintf($format, $spacing, $gutter);
                $this->col_space = $col_space;
            }
            if($item_bg || $item_border){
                $bg = $item_bg? 'background-color:' . $item_bg . ';':'';
                $border = $item_border? 'border:solid 2px ' . $item_border . '':'';
                $format = ' style="%s%s" ';
                $box_string = sprintf($format, $bg, $border);
                $this->box_string = $box_string;
            }
            if($button_font || $button_bg){
                $color = $button_font? 'color:' . $button_font . ';':'';
                $bg = $button_bg? 'background-color:' . $button_bg . ';':'';
                $format = ' style="%s%s" ';
                $button_string = sprintf($format, $bg, $color);
                $this->button_string = $button_string;
            }
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

    protected function single_html($id){
        global $options_array;
        $snippet = JMAYtVideo::video_snippet($id);
        $meta_array = JMAYtVideo::map_meta($snippet, $id);
        $h3_title = $meta_array['name'];
        $elipsis = '';
        if ( $options_array['item_font_char'] && (strlen($meta_array['name']) > $options_array['item_font_char'])){
            $h3_title = wordwrap($meta_array['name'], $options_array['item_font_char']);
            $h3_title = substr($h3_title, 0, strpos($h3_title, "\n"));
            $elipsis = '&nbsp;...';
        }
        $return .= '<div class="jmayt-item-wrap"' . $this->box_string . '>';
        $return .= '<div class="jmayt-item">';
        $return .= '<div class="jmayt-video-wrap">';
        $return .= '<div class="jma-responsive-wrap" itemprop="video" itemscope itemtype="http://schema.org/VideoObject">';
        $return .= '<button class="jmayt-btn"' . $this->button_string . '>&#xe140;</button>';
        $return .= JMAYtVideo::jma_youtube_schema_html($meta_array);
        $return .=  '<iframe src="https://www.youtube.com/embed/' . $id . '?rel=0&amp;showinfo=0" frameborder="0" allowfullscreen></iframe>';
        $return .= '</div><!--jma-responsive-wrap-->';
        $return .= '</div><!--yt-video-wrap-->';
        $return .= '<div class="jmayt-text-wrap">';
        $return .= '<h3' . $this->h3_string . '>' . $h3_title . $elipsis . '</h3>';
        $return .= '</div><!--jmayt-text-wrap-->';
        $return .= '</div><!--yt-item-->';
        $return .= '</div><!--yt-item-wrap-->';
        return $return;
    }

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