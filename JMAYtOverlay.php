<?php

/**
 * Created by PhpStorm.
 * User: John
 * Date: 6/25/2017
 * Time: 4:31 PM
 */
class JMAYtOverlay {
    var $urls;
    var $id;
    var $add;

    public function __construct($urls, $id, $add){//echo $urls;
        $this->urls = $urls;
        $this->id = $id;
        $this->add = $add;
    }

    public function get_url(){
        $sep = DIRECTORY_SEPARATOR;
        $urls = $this->urls;
        $trans_id = 'jmaytoverlay' . $this->id;
        $return = get_transient( $trans_id );
        $i = 0;
        $complete = false;
        do {
            if(array_key_exists($i, $urls)){
                $ex = explode('.', basename($urls[$i]));
                $ext = $ex[1];
                $filename = realpath(plugin_dir_path(__FILE__)) . $sep . 'overlays' . $sep . $this->id . '.' . $ext;
                if(false === $return || !file_exists($filename)){
                    $store_dir = realpath(plugin_dir_path(__FILE__)) . $sep . 'overlays';//echo $this->fetch_image($urls[$i], $store_dir, $this->id);
                    if($this->fetch_image($urls[$i], $store_dir, $this->id)){
                        $add = false === $return? 0: $this->add;
                        $return = plugins_url($sep . 'overlays' . $sep . $this->id . '.' . $ext, __FILE__);
                        set_transient( $trans_id, $return, 604800 + $add );
                        $complete = true;
                    }
                }
                $i++;
            }else
                $complete = true;
        }
        while(!$complete);

        return $return;
    }

    /**
     * Fetch JPEG or PNG or GIF Image
     *
     * A custom function in PHP which lets you fetch jpeg or png images from remote server to your local server
     * Can also prevent duplicate by appending an increasing _xxxx to the filename. You can also overwrite it.
     *
     * Also gives a debug mode to check where the problem is, if this is not working for you.
     *
     * @author Swashata <swashata ~[at]~ intechgrity ~[dot]~ com>
     * @copyright Do what ever you wish - I like GPL <img draggable="false" class="emoji" alt="🙂" src="https://s.w.org/images/core/emoji/2.3/svg/1f642.svg"> (& love tux ;))
     * @link https://www.intechgrity.com/?p=808
     *
     * @param string $img_url The URL of the image. Should start with http or https followed by :// and end with .png or .jpeg or .jpg or .gif. Else it will not pass the validation
     * @param string $store_dir The directory where you would like to store the images.
     * @return string the location of the image (either relative with the current script or abosule depending on $store_dir_type)
     */
    protected function fetch_image($img_url, $store_dir = 'images', $filename = 'default') {
        //first get the base name of the image
        $i_name = $filename;

        //now try to guess the image type from the given url
        //it should end with a valid extension...
        //good for security too
        if(preg_match('/https?:\/\/.*\.png$/i', $img_url)) {
            $img_type = 'png';
        }
        else if(preg_match('/https?:\/\/.*\.(jpg|jpeg)$/i', $img_url)) {
            $img_type = 'jpg';
        }
        else if(preg_match('/https?:\/\/.*\.gif$/i', $img_url)) {
            $img_type = 'gif';
        }
        else {
            return ''; //possible error on the image URL
        }

        $dir_name = rtrim($store_dir, '/') . '/';

        //create the directory if not present
        if(!file_exists($dir_name))
            mkdir($dir_name, 0777, true);

        //calculate the destination image path
        $i_dest = $dir_name . $i_name . '.' . $img_type;

        //first check if the image is fetchable
        $img_info = @getimagesize($img_url);

        //is it a valid image?
        if(false == $img_info || !isset($img_info[2]) || !($img_info[2] == IMAGETYPE_JPEG || $img_info[2] == IMAGETYPE_PNG || $img_info[2] == IMAGETYPE_JPEG2000 || $img_info[2] == IMAGETYPE_GIF)) {
            return ''; //return empty string
        }

        //now try to create the image
        if($img_type == 'jpg') {
            $m_img = @imagecreatefromjpeg($img_url);
        } else if($img_type == 'png') {
            $m_img = @imagecreatefrompng($img_url);
            @imagealphablending($m_img, false);
            @imagesavealpha($m_img, true);
        } else if($img_type == 'gif') {
            $m_img = @imagecreatefromgif($img_url);
        } else {
            $m_img = FALSE;
        }

        //was the attempt successful?
        if(FALSE === $m_img) {
            return '';
        }

        //now attempt to save the file on local server
        if($img_type == 'jpg') {
            if(imagejpeg($m_img, $i_dest, 100))
                return $i_dest;
            else
                return '';
        } else if($img_type == 'png') {
            if(imagepng($m_img, $i_dest, 0))
                return $i_dest;
            else
                return '';
        } else if($img_type == 'gif') {
            if(imagegif($m_img, $i_dest))
                return $i_dest;
            else
                return '';
        }

        return '';
    }

//a quick test? just uncomment the line below
//echo itg_fetch_image('http://tuxpaint.org/stamps/stamps/animals/birds/cartoon/tux.png');


}