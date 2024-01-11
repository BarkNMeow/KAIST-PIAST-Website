function toggleList(dom){
    const target = $(dom).parent().parent().find('.video-list-wrapper');

    if($(dom).hasClass('seemore-btn')){
        $('.video-list-wrapper').css('height', '12rem');
        $('.video-list-wrapper').off();
        $('.fold-btn').html('더 보기');
        $('.fold-btn').toggleClass('seemore-btn fold-btn');

        target.css('height', target.find('.video-list').css('height'));
        window.onresize = function(){
            target.css('height', target.find('.video-list').css('height'));
        };

        $(dom).html('접기');
    } else {
        window.onresize = function(){};
        target.css('height', '12rem');
        target.off();
        $(dom).html('더 보기');
    }

    $(dom).toggleClass('seemore-btn fold-btn');
}

function activateVideo(id, dom){
    if($('#np').val() == id) return;
    
    $('#np').val(id);

    var inner = '<iframe src="https://www.youtube.com/embed/' + id + '?autoplay=1" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen style="width: 100%; height: 100%;"></iframe>';
    $('#player-wrapper').html(inner);
    $('#player-wrapper').css('height', '405px');
    $('#player-wrapper').css('margin-bottom', '2rem');

    $('.play-overlap-hold').removeClass('play-overlap-hold');
    $(dom).addClass('play-overlap-hold');

    $('#player-wrapper')[0].scrollIntoView(false);
}