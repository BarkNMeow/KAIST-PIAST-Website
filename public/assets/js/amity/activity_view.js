let menuvisible = false;

$('#post-dropdown-btn').click((e) => {
    e.stopPropagation();
    dropdownVisibility(!menuvisible);
});
$(document).click(function(){
    dropdownVisibility(false);
});

$('.image-ui .left-btn, .image-ui .right-btn').click(function(){
    const target = $('#activity-image .image-viewer:nth-child(' + (viewidx + 2) + ')');
    const url = target.css('background-image').slice(5, -2);

    $('#img-download-btn').attr('href', url);
})

function dropdownVisibility(visible){
    if(visible){
        $(this).addClass('selected');
        $('#post-dropdown').show();
    } 
    else {
        $(this).removeClass('selected');
        $('#post-dropdown').hide();
    }

    menuvisible = visible;
}

function verifyInput(){
    let flag = true;
    $('#score-table > div input').each(function(){
        if(!$(this).is(':disabled') && $(this).val() == ''){
            flag = false;
        }
    })
    if($('#activity-score-total').html() === '0') flag = false;

    $('#accept-score-btn').attr('disabled', !flag);
}

$('#activity-delete-btn').click(function(){
    if(confirm('정말 삭제하시겠습니까?')){
        const id = $('#id').val();
        $.ajax({
            url: '../api/amityquery.php',
            type: 'post',
            data: {
                why: 'activity_delete',
                id: id,
            },
            dataType: 'json',
        }).done(function(res){
            alert(res.message);
            if(res.success) window.location.href = $('.btn-list-wrapper a').attr('href');
        });
    }
})

$('#accept-score-btn').click(function(){
    const id = $('#id').val();

    let scoreboard = [];
    $('#score-table > div').each(function(){
        const event = parseInt($(this).find('select').val());
        const n = parseInt($(this).find('input[name=n]').val());
        const k = parseInt($(this).find('input[name=k]').val());
        if(event != -1) scoreboard.push({event: event, n: n, k: k});
    });

    scoreboard = JSON.stringify(scoreboard);

    $.ajax({
        url: '../api/amityquery.php',
        type: 'post',
        data: {
            why: 'activity_accept',
            id: id,
            scoreboard: scoreboard,
        },
        dataType: 'json',
    }).done(function(res){
        alert_float(res.message, res.success);
        if(res.success){
            $('#score-total').removeClass('text-grey');
            $('#accept-score-btn').html('승인됨 <i class="bi bi-check-lg"></i>');
        }
    });
});