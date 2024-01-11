function concertChangeTab(n, dom){
    showHidden();
    $('.tab-container').hide();
    $('.tab-container').eq(n).show();
    $('#id').val(n - 1);

    $('.tab-btn').removeClass('selected');
    $(dom).addClass('selected');

    $('#tab-btn-border-bottom').css('transform', 'translateX(' + (7.75 * n) + 'rem)');
    initializeInput();
}

$('button[name=concert-activate-btn]').click(function(){
    if(confirm('정말 ' + $(this).html() + '하시겠습니까?')){
        $.ajax({
            url: '../api/concertquery.php',
            type: 'post',
            data: {
                why: 'apply_toggle',
            },
            dataType: 'json',
        }).done(function(res){
            const resstr = res.result ? '활성화' : '비활성화';
            alert_float('성공적으로 ' + resstr + '되었습니다.', true);
            window.location.reload();
        });
    }
});