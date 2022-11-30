// Onclick. Show / hide teacher unreaded messages. start
require(['jquery'], function($)
{
    
    $('.ntd-messanger-headline').click(function() 
    {
        $('.ntd-level-2[data-teacher='+this.dataset.teacher+']').toggleClass('ntd-hidden-box');
    });
});
// Onclick. Show / hide teacher unreaded messages. end

// Teachers tooltips start 
require(['jquery'], function($)
{
    $(document).ready(function() {

        $('div.ntd-tooltip').each(function(i){
            $("body").append("<div class='ntd-tooltip-box' id='ntd-tooltip-box"+i+"'><p>"+$(this).attr('title')+"</p></div>");
            var my_tooltip = $("#ntd-tooltip-box"+i);
            
            $(this).removeAttr("title").mouseover(function(){
                    my_tooltip.css({display:"none"}).fadeIn(100);
            }).mousemove(function(kmouse){
                    my_tooltip.css({left:kmouse.pageX+15, top:kmouse.pageY+15});
            }).mouseout(function(){
                    my_tooltip.fadeOut(100);                  
            });
        });
    });    
});
// Teachers tooltips end