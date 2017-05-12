function jmayt_title_resize(){
    jQuery('.jmayt-list-wrap').each(function(){
        //make all title boxes the same height as the largest box
        $this = jQuery(this);
        var $title_max = Math.max.apply(null, $this.find('h3').map(function () {
            return jQuery(this).outerHeight();
        }).get());
        $this.find('.jmayt-text-wrap').css('min-height', $title_max + 'px');
    });
};
function jmayt_video_resize(){
    jQuery('.jmayt-fixed').css({
        'width': jQuery(window).width() + 'px',
        'height': window.innerHeight + 'px'
    });
};
jQuery(document).ready(jmayt_title_resize);
jQuery(document).ready(jmayt_toggle);

jQuery(window).resize(jmayt_title_resize);
jQuery(window).resize(jmayt_video_resize);

function jmayt_toggle(){
    //create the toggle lightbox effect for the youtube items
    jQuery('.jmayt-video-wrap').each(function(){
        jQuery(this).toggle(jmayt_show_lightbox, jmayt_hide_lightbox);
    });

    function jmayt_show_lightbox() {
        $this = jQuery(this);
        //distance the user has scrolled down the window (dynamic)
        $scroll = jQuery(document).scrollTop();
        //get rid of scroll
        jQuery('body, html').css('overflow-y','hidden');
        //x and y coordinates of the div (static)
        $pos = $this.offset();
        $pos_top = $pos.top;
        $pos_left = $pos.left;
        $parent = $this.parent('.jmayt-item');
        $parent_width = $parent.innerWidth();
        $button = $this.find('.jmayt-btn');
        $z_index = $this.parents('.jmayt-outer').parents().add($this);

        $parent.css('min-height', $parent.height() + 'px');
        $button.html('&#xe097;');
        //bring this section of the page to the top
        $z_index.each(function(){
            jQuery(this).css({'z-index': '2147483647'})
        });
        //first we make it absolute and give it a size
        $this.addClass('jmayt-fixed');
        $this.css({
            'width': ($parent_width) + 'px',
            'height': ($parent_width)/1.7778 + 'px',
            'padding-bottom': 0
        }).animate({//then we increase it's size while positioning it at the top left of the window
            'top': -($pos_top - $scroll) + 'px',
            'left': -$pos_left + 'px',
            'width': jQuery(window).width() + 'px',
            'height': window.innerHeight + 'px'
        });
    }

    function jmayt_hide_lightbox() {
        $button.html('&#xe140;');
        $this.animate({
            'top': 0,
            'left': 0,
            'width': ($parent_width) + 'px',
            'height': ($parent_width)/1.7778 + 'px'
        }, 300, 'swing',function(){
            $this.removeClass('jmayt-fixed');
            $this.css({
                'top': '',
                'left': '',
                'height': '',
                'width': '',
                'padding-bottom': ''
            });
            $parent.css('min-height', '');
            $z_index.each(function(){
                jQuery(this).css({'z-index': ''})
            });
        });
        jQuery('body, html').css('overflow-y','');
    }




    function hold_fixed(){
        //using the class that is added on show_lightbox
        jQuery('.jmayt-fixed').each(function(){
            $this = jQuery(this);
            //distance the use has scrolled down the window (dynamic)
            $scroll = jQuery(document).scrollTop();
            $parent = $this.closest('.jmayt-item');
            //x and y coordinates of the div (static)
            $pos = $parent.offset();
            $pos_top = $pos.top;
            $pos_left = $pos.left;
            $this.css({
                'top': -($pos_top - $scroll) + 'px',
                'left': -$pos_left + 'px',
                'width': jQuery(window).width() + 'px',
                'height': window.innerHeight + 'px',
            })
        });
    }
    //for width change and orientation change on mobile
    jQuery(window).resize(hold_fixed);
}
