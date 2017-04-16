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
    jQuery('.jmayt-btn').each(function(){ 
        jQuery(this).toggle(mouseIn, mouseOut);
    });

    function mouseIn() {
        $this = jQuery(this);
        $parent = $this.closest('.jmayt-video-wrap');
        $scroll = jQuery(document).scrollTop();
        $wrapper = $this.closest('.jmayt-item');
        $wrapper.css('min-height', $wrapper.height() + 'px');
        $parent_width = $wrapper.innerWidth();
        $pos = $parent.offset();
        $pos_top = $pos.top;
        $pos_left = $pos.left;
        $this.html('&#xe097;');
        $this.animate({'font-size': '23px'});
        $parent.addClass('jmayt-fixed');
        $parent.css({
            'position': 'absolute',
            'z-index': 9999,
            'top': 0,
            'left': 0,
            'width': ( $parent_width) + 'px',
            'height': $parent_width + 'px',
            'padding-bottom': 'inherit'
        }).animate({
            'top': -($pos_top - $scroll) + 'px',
            'left': -$pos_left + 'px',
            'width': jQuery(window).width() + 'px',
            'height': (jQuery(window).height()) + 'px',
            'padding-left': '12%',
            'padding-right': '12%',
            'padding-top': '40px'
        });
    }

    function mouseOut() {
        $this = jQuery(this);
        $parent = $this.closest('.jmayt-video-wrap');
        $wrapper = $this.closest('.jmayt-item');
        $wrapper.css('min-height', '');
        $parent_width = $wrapper.innerWidth();
        $this.html('&#xe140;');
        $this.css({'font-size': ''});
        $parent.animate({
            'top': 0,
            'left': 0,
            'width': ($parent_width) + 'px',
            'height': ($parent_width)/1.7778 + 'px'
        },400, 'swing', function(){
            jQuery(this).css({
                'z-index':  '',
                'position': '',
                'padding': '',
                'top': '',
                'left': '',
                'height': '',
                'width': ''
            });
            jQuery(this).removeClass('jmayt-fixed');
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
                'height': jQuery(window).height() + 'px',
            })
        });
    }
    jQuery(window).scroll(hold_fixed);
    jQuery(window).resize(hold_fixed);
}
