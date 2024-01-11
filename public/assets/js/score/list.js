$('.page-select').on('change', function(){
    $('#page').val(0);
    if($('#issearch').val() == 0) loadBoard();
    else searchBoard();
});

$('#btn-search-score').click(function(){
    $('#page').val(0);
    searchBoard();
});

$('.search-input').on('keyup', function(key){
    if(key.keyCode == 13) searchBoard();
});

$('.search-option').focus(function(){$('.search-window').addClass('focused')});
$('.search-option').blur(function(){$('.search-window').removeClass('focused')});
$('.search-input').focus(function(){$('.search-window').addClass('focused')});
$('.search-input').blur(function(){$('.search-window').removeClass('focused')});



function loadBoard(){
    const page = $('#page').val();
    const perpage = $('.page-select').val();
    window.location.href="list?p=" + page + "&pp=" + perpage;
}

function searchBoard(){
    const search = $('.search-input').val();
    if(search && search.length < 2){
        alert_float('검색어는 두 글자 이상이여야합니다!');
        return;
    }

    const page = $('#page').val();
    const perpage = $('.page-select').val(); 
    const searchop = $('.search-option').val();

    let href = "list?p=" + page + "&pp=" + perpage;

    if(search){
        href += ("&sop=" + searchop + "&s=" + search);
    }

    let scoretype = 0;
    $('input[name=score-type]').each((id, item) => {
        if($(item).is(':checked')){
            scoretype += 1 << parseInt($(item).val());
        }
    });
    if(scoretype) href += ('&type=' + scoretype);

    let genre = 0;
    $('input[name=score-genre]').each((id, item) => {
        if($(item).is(':checked')){
            genre += 1 << parseInt($(item).val());
        }
    });
    if(genre) href += ('&genre=' + genre);

    if($('#score-diff-min').val()) href += ('&mind=' + $('#score-diff-min').val());
    if($('#score-diff-max').val()) href += ('&maxd=' + $('#score-diff-max').val());

    window.location.href = href;
}

function setpage(n){
    $('#page').val(n);
    loadBoard();
}