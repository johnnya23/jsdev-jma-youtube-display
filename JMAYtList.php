<?php
class JMAYtList extends JMAYtVideo {

    /*
     * function jma_get_yt_loop()
     * returns the youotube api array for a list
     * using $yt_list_id and  $API_CODE
     * */
    function yt_loop($yt_list_id){
        $return = array();

        $youtube_array = 'https://www.googleapis.com/youtube/v3/playlistItems?part=snippet&maxResults=50&playlistId=' . $yt_list_id . '&fields=items%2Fsnippet&key=' . $this->api;

        $curl_array = JMAYtList::curl($youtube_array);
        if($curl_array)
            $return = $curl_array;
        return $return;
    }

    public function markup($res_cols = array()){
        global $options_array;
        $col_class = '';
        $trans_id = 'jmaytlist' . $post_id;
        foreach($res_cols as $break => $res_col){
            $trans_id .= $break . $res_col;
        }

        $trans_id .= $this->id;
        $return = get_transient( $trans_id );
        if(false === $return || !$options_array['cache']) {//if cache at 0
            $yt_api_array = JMAYtList::yt_loop($this->id);
            $yt_loop_items = $yt_api_array['items'];
            $count = count($yt_loop_items);
            $i = 0;
            if ($count > 0) {
                foreach($res_cols as $break => $res_col){
                    $res_col = $res_col < $count? $res_col: $count;
                    switch ($res_col) {
                        case 1:
                        case 2:
                        case 3:
                        case 4:
                        case 6:
                            $val = 12/$res_col;
                            break;
                        default:
                            $val = '020';
                    }
                    $col_class .= ' col-' . $break . '-' . $val;
                }
                $return = '';
                foreach($yt_loop_items as $yt_loop_item){
                    // add bootstrap column classses for each screen size
                    $br_cl = '';
                    foreach($res_cols as $break => $res_col){
                        //add clearing class for each screen size to left column items
                        if((!($i % $res_col)) && ($res_col != 1)){
                            $br_cl .= ' ' . "{$break}" . '-break';
                        }
                    }
                    $yt_snippet = $yt_loop_item['snippet'];
                    $yt_id = $yt_snippet['resourceId']['videoId'];
                    $return .= '<div class="yt-item yt-list-item col' . $col_class . $br_cl .'">';
                    $return .= JMAYtList::single_html($yt_id);
                    $return .= '</div><!--col-->';
                    $i++;
                }
                set_transient( $trans_id, $return, $options_array['cache'] );
            }
        }
        return $return;
    }
}