jQuery(document).ready(function($){
    $('.jmayt-list-wrap').each(function(){
        $this = $(this);
        var $title_max = Math.max.apply(null, $this.find('h3').map(function ()
        {
            return $(this).outerHeight();
        }).get());
        $this.find('.jmayt-text-wrap').css('min-height', $title_max + 'px');
    });
    $('.jmayt-btn').each(function(){
        $(this).click(function(){
            $this = $(this);
            $parent = $this.closest('.jmayt-video-wrap');
            $scroll = $(document).scrollTop();
            $wrapper = $this.closest('.jmayt-item');
            $wrapper.css('min-height', $wrapper.height() + 'px');
            $parent_width = $wrapper.innerWidth();
            $pos = $parent.offset();
            $pos_top = $pos.top;
            $pos_left = $pos.left;
            if($parent.css('z-index') != 9999){
                $this.html('&#xe097;');
                $this.animate({'font-size': '23px'});
                $parent.addClass('jmayt-fixed');
                $parent.css({
                    'position': 'absolute',
                    'z-index':  9999,
                    'top': 0,
                    'left': 0,
                    'width': ( $parent_width) + 'px',
                    'height': $parent_width + 'px',
                    'padding-bottom': 'inherit',
                }).animate({
                    'top': -($pos_top - $scroll) + 'px',
                    'left': -$pos_left + 'px',
                    'width': $(window).width() + 'px',
                    'height': ($(window).height()) + 'px',
                    'padding-left': '12%',
                    'padding-right': '12%',
                    'padding-top': '40px'
                });
            }else{
                $this.html('&#xe140;');
                $this.animate({'font-size': '18px'});
                $parent.animate({
                    'top': 0,
                    'left': 0,
                    'width': ($parent_width) + 'px',
                    'height': ($parent_width)/1.7778 + 'px'
                },400, 'swing', function(){
                    $(this).css({
                        'z-index':  '',
                        'position': '',
                        'padding': '',
                        'top': '',
                        'left': '',
                        'height': '',
                        'width': '',
                    });
                    $(this).removeClass('jmayt-fixed');
                });
            }
        });
    });
    function hold_fixed(){
        $('.jmayt-fixed').each(function(){
            $this = $(this);
            $scroll = $(document).scrollTop();
            $parent = $this.closest('.jmayt-item');
            $pos = $parent.offset();
            $pos_top = $pos.top;
            $pos_left = $pos.left;
            $this.css({
                'top': -($pos_top - $scroll) + 'px',
                'left': -$pos_left + 'px',
                'width': $(window).width() + 'px',
            })
        });
    }
    $(window).scroll(hold_fixed);
    $(window).resize(hold_fixed);
});