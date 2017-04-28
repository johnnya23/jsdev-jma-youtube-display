jQuery(document).ready(jmayt);
jQuery(document).ajaxComplete(jmayt);

function jmayt(){
    jQuery('.jmayt-list-wrap').each(function(){
        //make all title boxes the same height as the largest box
        $this = jQuery(this);
        var $title_max = Math.max.apply(null, $this.find('h3').map(function ()
        {
            return jQuery(this).outerHeight();
        }).get());
        $this.find('.jmayt-text-wrap').css('min-height', $title_max + 'px');
    });

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
        $parent = $this.parent('.jmayt-item');
        $parent_width = $parent.innerWidth();
        $button = $this.find('.jmayt-btn');
        $responsive = $this.find('.jma-responsive-wrap');

        $parent.css('min-height', $parent.height() + 'px');
        $button.html('&#xe097;');
        $button.animate({'font-size': '23px'});
        $responsive.animate({
            'width': '80%',
            'padding-bottom': '45%'
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

    function hide_lightbox() {
        $button.html('&#xe140;');
        $button.css({'font-size': ''});
        $this.animate({
            'top': 0,
            'left': 0,
            'width': ($parent_width) + 'px',
            'height': ($parent_width)/1.7778 + 'px'
        },400, 'swing', function(){
            jQuery(this).css({
                'height': '',
                'width': '',
                'padding-bottom': ''
            });
            jQuery(this).removeClass('jmayt-fixed');
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
    jQuery(window).scroll(hold_fixed);
    jQuery(window).resize(hold_fixed);
}
