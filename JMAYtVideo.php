<?php
class JMAYtVideo {
    var $id;
    var $api;

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

    protected function single_html($id, $grid = true){
        $snippet = JMAYtVideo::video_snippet($id);
        $meta_array = JMAYtVideo::map_meta($snippet, $id);
        $return .= '<div class="jmayt-item">';
        $return .= '<div class="jmayt-video-wrap">';
        $return .= '<div class="responsive-wrap" itemprop="video" itemscope itemtype="http://schema.org/VideoObject">';
        if($grid)
            $return .= '<button class="jmayt-btn">&#xe140;</button>';
        $return .= JMAYtVideo::jma_youtube_schema_html($meta_array);
        $return .=  '<iframe src="https://www.youtube.com/embed/' . $id . '?rel=0&amp;showinfo=0" frameborder="0" allowfullscreen></iframe>';
        $return .= '</div><!--responsive-wrap-->';
        $return .= '</div><!--yt-video-wrap-->';
        $return .= '<h3>' . $meta_array['name'] . '</h3>';
        $return .= '</div><!--yt-item-->';
        return $return;
    }

    public function markup(){
        global $options_array;
        $trans_id = 'jmaytvideo' . $this->id;
        $return = get_transient( $trans_id );
        if(false === $return || !$options_array['cache']) {//if cache at 0
            $return = JMAYtVideo::single_html($this->id);
            set_transient( $trans_id, $return, $options_array['cache'] );
        }
        return $return;
    }
}