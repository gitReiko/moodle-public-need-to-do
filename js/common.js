
require(['jquery'], function($)
{
    // Onclick. Show / hide teacher unreaded messages.
    $('.ntd-messanger-headline').click(function() 
    {
        $('.ntd-level-2[data-teacher='+this.dataset.teacher+']').toggleClass('ntd-hidden-box');
    });
});

