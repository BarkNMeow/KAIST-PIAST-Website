$('.bi-chevron-compact-right').click(function(){
    const target = $(this).parents().eq(1).next();
    if(!$(this).hasClass('arrow-down') && !$(this).hasClass('arrow-right'))
        $(this).addClass('arrow-down');
    else
        $(this).toggleClass('arrow-down arrow-right');

    if($(this).hasClass('arrow-down')){
        target.css('height', '30em');
    }
    else{
        target.css('height', '0');
    }
});