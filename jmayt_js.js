title_resize = function(){
    jQuery('.jmayt-list-wrap').each(function(){
        //make all title boxes the same height as the largest box
        $this = jQuery(this);
        var $title_max = Math.max.apply(null, $this.find('h3').map(function ()
        {
            return jQuery(this).outerHeight();
        }).get());
        $this.find('.jmayt-text-wrap').css('min-height', $title_max + 'px');
    });
};
video_resize = function(){
    jQuery('.jmayt-fixed').css({
        'width': jQuery(window).width() + 'px',
        'height': window.innerHeight + 'px'
    });
};
jQuery(document).ready(jmayt);
jQuery(document).ready(title_resize);
jQuery(document).ajaxComplete(jmayt);
jQuery(document).ajaxComplete(title_resize);

jQuery(window).resize(title_resize);
jQuery(window).resize(video_resize);

function jmayt(){
    //create the toggle lightbox effect for the youtube items
    jQuery('.jmayt-video-wrap').each(function(){
        jQuery(this).toggle(show_lightbox, hide_lightbox);
    });

    function show_lightbox() {
        $this = jQuery(this);
        //distance the use has scrolled down the window (dynamic)
        $scroll = jQuery(document).scrollTop();
        //x and y coordinates of the div (static)
        $pos = $this.offset();
        $pos_top = $pos.top;
        $pos_left = $pos.left;
        $parent = $this.parents('.jmayt-item');
        $parent_width = $parent.innerWidth();
        $button = $this.find('.jmayt-btn');
        $responsive = $this.find('.jma-responsive-wrap');
        $holder = $this.parent();
        $contents = $holder.contents();

        $holder.css('min-height', $holder.height() + 'px');
        $button.html('&#xe097;');
        $button.animate({'font-size': '23px'});
        $responsive.animate({
            'width': '80%',
            'padding-bottom': '45%'
        });
        //first we make it fixed and give it a size
        jQuery('body').prepend($contents);
            $this.addClass('jmayt-fixed');
            $this.css({
                'width': ($parent_width) + 'px',
                'height': ($parent_width)/1.7778 + 'px',
                'padding-bottom': 0,
                'top': ($pos_top - $scroll) + 'px',
                'left': $pos_left + 'px',
            });//then we increase it's size while positioning it at the top left of the window
        setTimeout(function() {
            $this.animate({
                'top': 0,
                'left': 0,
                'width': jQuery(window).width() + 'px',
                'height': window.innerHeight + 'px'
            });
        }, 200);

    }

    function hide_lightbox() {
        $this = jQuery(this);
        $button.html('&#xe140;');
        $button.css({'font-size': ''});
        $this.animate({
            'width': ($parent_width) + 'px',
            'height': ($parent_width)/1.7778 + 'px',
            'top': ($pos_top - $scroll) + 'px',
            'left': $pos_left + 'px'
        }, 150);
        $responsive.animate({
            'width': '100%',
            'padding-bottom': '56.25%',
            'min-height': ''
        }, 150);
        setTimeout(function() {
            $this.removeClass('jmayt-fixed');
            $holder.prepend($contents);
            $holder.css('min-height', '');
            $this.css({
                'width': '',
                'height': '',
                'top': '',
                'left': '',
                'padding-bottom': ''
            });
            $responsive.css({
                'width': '',
                'padding-bottom': '',
                'min-height': ''
            });
        }, 160 );

    }
}
