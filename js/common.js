
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

function show_hide_more(button, hiddenElements, childs)
{
    require(['jquery'], function($)
    {
        if($(button).text() == $(button).attr('data-show-text'))
        {
            $(button).text($(button).attr('data-hide-text'));
        }
        else 
        {
            $(button).text($(button).attr('data-show-text'));
        }

        $('.'+hiddenElements).toggleClass('ntd-hidden-box');
        $('.'+hiddenElements+childs).addClass('ntd-hidden-box');
    });
}

// Onclick. Show / hide teachers chat cells.
require(['jquery'], function($)
{
    $('.ntd-chat-teacher').click(function() 
    {
        let identifier = '';
        identifier += '.ntd-level-2[data-teacher='+this.dataset.teacher+']';
        identifier += '[data-block-instance='+this.dataset.blockInstance+']';
        identifier += '[data-whose-work='+this.dataset.whoseWork+']';

        $(identifier).toggleClass('ntd-hidden-box');
    });
});

// Onclick. Show / hide teachers course activities cells.
require(['jquery'], function($)
{
    $('.ntd-activity-course-cell').click(function() 
    {
        let identifier = '';
        identifier += '.ntd-level-2[data-course-cell='+this.dataset.courseCell+']';
        identifier += '[data-block-instance='+this.dataset.blockInstance+']';
        identifier += '[data-whose-work='+this.dataset.whoseWork+']';

        $(identifier).toggleClass('ntd-hidden-box').each(function(index) {

            let childId = '';
            childId += '.ntd-level-3[data-teacher-cell='+$(this).attr('data-teacher-cell')+']';
            childId += '[data-block-instance='+$(this).attr('data-block-instance')+']';
            childId += '[data-whose-work='+$(this).attr('data-whose-work')+']';
            
            $(childId).addClass('ntd-hidden-box');
        });

    });
});

// Onclick. Show / hide activities course activities cells.
require(['jquery'], function($)
{
    $('.ntd-activity-teacher-cell').click(function() 
    {
        let identifier = '';
        identifier += '.ntd-level-3[data-course-cell='+this.dataset.courseCell+']';
        identifier += '[data-teacher-cell='+this.dataset.teacherCell+']';
        identifier += '[data-block-instance='+this.dataset.blockInstance+']';
        identifier += '[data-whose-work='+this.dataset.whoseWork+']';

        $(identifier).toggleClass('ntd-hidden-box');
    });
});
