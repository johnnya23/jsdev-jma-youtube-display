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

function jmayt_toggle(){
    //create the toggle lightbox effect for the youtube items
    jQuery('.jmayt-fixed, .jmayt-btn').each(function(){
        jQuery(this).toggle(jmayt_show_lightbox, jmayt_hide_lightbox);
    });

    function jmayt_show_lightbox() {
        $this = jQuery(this);
        //distance the user has scrolled down the window (dynamic)
        $scroll = jQuery(document).scrollTop();
        //get rid of scroll
        jQuery('body, html').css('overflow-y','hidden');
        $parent = $this.parent('.jmayt-item');
        $parent_width = $parent.innerWidth();
        $button = $this;
        $fixed = $this.parents('.jmayt-video-wrap');
        $z_index = $fixed.parents('.jmayt-outer').parents().add($fixed).not(jQuery('body, html'));
        $parent.css('min-height', $parent.height() + 'px');
        $this.html('&#xe097;');
        //bring this section of the page to the top
        $z_index.each(function(){
            jQuery(this).css({'z-index': '2147483647', 'overflow': 'visible'})
        });
        //first we make it absolute and give it a size
        $fixed.addClass('jmayt-fixed');
        //x and y coordinates of the div (static)
        $pos = $this.offset();
        $pos_top = $pos.top;
        $pos_left = $pos.left;
        $fixed.css({
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
        $this.html('&#xe140;');
        $fixed.animate({
            'top': 0,
            'left': 0,
            'width': ($parent_width) + 'px',
            'height': ($parent_width)/1.7778 + 'px'
        }, 300, 'swing',function(){
            $fixed.removeClass('jmayt-fixed');
            $fixed.css({
                'top': '',
                'left': '',
                'height': '',
                'width': '',
                'padding-bottom': ''
            });
            $parent.css('min-height', '');
            $z_index.each(function(){
                jQuery(this).css({'z-index': '', 'overflow': ''})
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
    jQuery(window).scroll(hold_fixed);
    jQuery(window).resize(hold_fixed);
}

function jmayt_play_video(){
    jQuery('.jmayt-overlay-button').bind('click', function(e){
        e.preventDefault();
        $this = jQuery(this);
        videoUrl = $this.data('embedurl');
        $iframe = $this.next();
        $iframe.attr("src", videoUrl);
        $iframe.css({'display': 'block'});
    });
}

jQuery(document).ready(jmayt_title_resize);
jQuery(document).ready(jmayt_play_video);
jQuery(document).ready(jmayt_toggle);

jQuery(window).resize(jmayt_title_resize);
jQuery(window).resize(jmayt_video_resize);
