resize = function(){
    jQuery('.jmayt-list-wrap').each(function(){
        //make all title boxes the same height as the largest box
        $this = jQuery(this);
        var $title_max = Math.max.apply(null, $this.find('h3').map(function ()
        {
            return jQuery(this).outerHeight();
        }).get());
        $this.find('.jmayt-text-wrap').css('min-height', $title_max + 'px');
    });
}
jQuery(document).ready(jmayt);
jQuery(document).ready(resize);
jQuery(document).ajaxComplete(jmayt);
jQuery(document).ajaxComplete(resize);

jQuery(window).resize(resize);

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
        $contents = $this.contents();

        $parent.css('min-height', $parent.height() + 'px');
        $button.html('&#xe097;');
        $button.animate({'font-size': '23px'});
        $responsive.animate({
            'width': '80%',
            'padding-bottom': '45%'
        });
        //first we make it fixed and give it a size
        jQuery('body').prepend($contents);
        setTimeout(function() {
            $this.addClass('jmayt-fixed');
        $this.css({
            'width': ($parent_width) + 'px',
            'height': ($parent_width)/1.7778 + 'px',
            'padding-bottom': 0,
            'top': ($pos_top - $scroll) + 'px',
            'left': $pos_left + 'px',
        });//then we increase it's size while positioning it at the top left of the window
        }, 10);
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
        $button.html('&#xe140;');
        $button.css({'font-size': ''});
        $parent.prepend($contents);
        $this.removeClass('jmayt-fixed');
        $this.animate({
            'top': 0,
            'left': 0,
            'width': ($parent_width) + 'px',
            'height': ($parent_width)/1.7778 + 'px',
            'padding-bottom': ''
        });
        setTimeout(function() {
            $responsive.animate({
                'width': '100%',
                'padding-bottom': '56.25%'
            }, 200);
        }, 100);
        setTimeout(function() {
            $parent.css('min-height', '');
        }, 500);

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
    //jQuery(window).scroll(hold_fixed);
    //jQuery(window).resize(hold_fixed);
}
