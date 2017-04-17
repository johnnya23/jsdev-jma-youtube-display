jQuery(document).ready(jmayt);
jQuery(document).ajaxComplete(jmayt);

function jmayt(){
    jQuery('.jmayt-list-wrap').each(function(){
        $this = jQuery(this);
        var $title_max = Math.max.apply(null, $this.find('h3').map(function ()
        {
            return jQuery(this).outerHeight();
        }).get());
        $this.find('.jmayt-text-wrap').css('min-height', $title_max + 'px');
    });
    jQuery('.jmayt-video-wrap').each(function(){
        jQuery(this).toggle(mouseIn, mouseOut);
    });

    function mouseIn() {
        $this = jQuery(this);
        $scroll = jQuery(document).scrollTop();
        $pos = $this.offset();
        $pos_top = $pos.top;
        $pos_left = $pos.left;
        $parent = $this.parent('.jmayt-item');
        $parent_width = $parent.innerWidth();
        $button = $this.find('.jmayt-btn');
        $responsive = $this.find('.responsive-wrap');

        $parent.css('min-height', $parent.height() + 'px');
        $button.html('&#xe097;');
        $button.animate({'font-size': '23px'});
        $responsive.animate({
            'width': '80%',
            'padding-bottom': '45%'
        });
        $this.addClass('jmayt-fixed');
        $this.css({
            'width': ($parent_width) + 'px',
            'height': ($parent_width)/1.7778 + 'px'
        }).animate({
            'top': -($pos_top - $scroll) + 'px',
            'left': -$pos_left + 'px',
            'width': jQuery(window).width() + 'px',
            'height': (jQuery(window).height()+100) + 'px',
        });
    }

    function mouseOut() {
        $this = jQuery(this);
        $parent = $this.parent('.jmayt-item');
        $parent_width = $parent.innerWidth();
        $button = $this.find('.jmayt-btn');
        $responsive = $this.find('.responsive-wrap');

        $parent.css('min-height', '');
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
            });
            jQuery(this).removeClass('jmayt-fixed');
        });
        $responsive.animate({
            'width': '100%',
            'padding-bottom': '56.25%'
        });
    }

    function hold_fixed(){
        jQuery('.jmayt-fixed').each(function(){
            $this = jQuery(this);
            $scroll = jQuery(document).scrollTop();
            $parent = $this.closest('.jmayt-item');
            $pos = $parent.offset();
            $pos_top = $pos.top;
            $pos_left = $pos.left;
            $this.css({
                'top': -($pos_top - $scroll) + 'px',
                'left': -$pos_left + 'px',
                'width': jQuery(window).width() + 'px',
                'height': (jQuery(window).height()+100) + 'px',
            })
        });
    }
    jQuery(window).scroll(hold_fixed);
    jQuery(window).resize(hold_fixed);
}
