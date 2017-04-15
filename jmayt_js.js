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
            $wrapper = $this.closest('.jmayt-item');
            $wrapper.css('min-height', $wrapper.height() + 'px');
            $parent_width = $wrapper.innerWidth();
            $pos = $wrapper.offset();
            $pos_top = $pos.top - $(window).scrollTop() + parseInt($wrapper.css('padding-top'));
            $pos_left = $pos.left + parseInt($wrapper.css('padding-left'));
            if($parent.css('z-index') != 9999){
                $this.html('&#xe097;');
                $this.animate({'font-size': '23px'});
                $parent.css({
                    'position': 'fixed',
                    'z-index':  9999,
                    'top': $pos_top + 'px',
                    'left': $pos_left + 'px',
                    'width': ( $parent_width) + 'px',
                    'height': $parent_width + 'px',
                    'padding-bottom': 'inherit',
                }).animate({
                    'top': 0,
                    'left': 0,
                    'width': $(window).width(),
                    'height': $(window).height(),
                    'padding-left': '10%',
                    'padding-right': '10%',
                    'padding-top': '60px'
                });
            }else{
                $this.html('&#xe140;');
                $this.animate({'font-size': '18px'});
                $parent.animate({
                    'top': $pos_top + 'px',
                    'left': $pos_left + 'px',
                    'width': ($parent_width) + 'px',
                    'height': ($parent_width)/1.7778 + 'px'
                },400, 'swing', function(){
                    $(this).css({
                        'z-index':  0,
                        'position': 'relative',
                        'padding': 'inherit',
                        'top': 'inherit',
                        'left': 'inherit',
                        'height': 'inherit',
                    });
                });
            }
        });
    });
});