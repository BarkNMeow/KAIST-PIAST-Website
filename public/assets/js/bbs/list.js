$('#page-select').on('change', function(){
    $('#page').val(0);
    if($('#issearch').val() == 0) loadBoard();
    else searchBoard();
});

$('#btn-bbs-search').click(function(){
    $('#page').val(0);
    searchBoard();
});

$('#input-bbs-search').on('keyup', function(key){
    if(key.keyCode == 13) searchBoard();
});

$('.search-option').focus(function(){$('.search-window').addClass('focused')});
$('.search-option').blur(function(){$('.search-window').removeClass('focused')});
$('#input-bbs-search').focus(function(){$('.search-window').addClass('focused')});
$('#input-bbs-search').blur(function(){$('.search-window').removeClass('focused')});



function loadBoard(){
    const bbstype = $('#bbstype').val();
    const page = $('#page').val();
    const perpage = $('#page-select').val();

    window.location.href="list?b=" + bbstype + "&p=" + page + "&pp=" + perpage;
}

function searchBoard(){
    const search = $('#input-bbs-search').val();
    if(search.length < 2){
        alert_float('검색어는 두 글자 이상이여야합니다!');
        return;
    }

    const bbstype = $('#bbstype').val();
    const page = $('#page').val();
    const perpage = $('#page-select').val(); 
    const searchop = $('.search-option').val(); 

    window.location.href="list?b=" + bbstype + "&p=" + page + "&pp=" + perpage + "&sop=" + searchop + "&s=" + search;
}

function setpage(n){
    $('#page').val(n);
    loadBoard();
}