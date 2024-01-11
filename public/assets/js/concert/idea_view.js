let menuvisible = false;
const visible = $('#song-input').is(':visible');

$('#post-dropdown-btn').click((e) => {
    e.stopPropagation();
    dropdownVisibility(!menuvisible);
});
$(document).click(function(){
    dropdownVisibility(false);
})

$('#post-like-btn').on('click', function(){
    const id = $('#id').val();
    const target = $(this);

    $.ajax({
        url: '../api/concertquery.php',
        type: 'post',
        data: {
            why: 'update_like',
            id: id,
        },
        dataType: 'json',
    }).done(function(res){
        if(!res.success){
            alert_float(res.message);
        } else {
            if(res.active){
                target.addClass('liked');
                target.html('<i class="bi bi-heart-fill"></i> ' + res.likes);
            } else {
                target.removeClass('liked');
                target.html('<i class="bi bi-heart"></i> ' + res.likes);
            }
        }
    });
});

$('#post-delete-btn').on('click', function(){
    if(confirm('정말 삭제하시겠습니까?')){
        const id = $('#id').val();
        $.ajax({
            url: '../api/concertquery.php',
            type: 'post',
            data: {
                why: 'delete_post',
                id: id,
            },
            dataType: 'json',
        }).done(function(res){
            if(!res.success){
                alert_float(res.message);
            } else {
                alert('성공적으로 삭제되었습니다.');
                window.location.href = 'idea';
            }
        });
    }
});

$('#idea-elect-btn').on('click', function(){
    if(confirm('정말 이 테마를 선정하시겠습니까? 기존 신청 내역이 없어집니다!')){
        const id = $('#id').val();

        $.ajax({
            url: '../api/concertquery.php',
            type: 'post',
            data: {
                why: 'idea_elect',
                id: id,
            },
            dataType: 'json',
        }).done(function(res){
            alert_float(res.message, res.success);
        });
    }
});

$('#song-title, #song-m, #song-s, #song-perf').on('input', verifySong);

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

function verifySong(){
    let flag = true;
    if(!$('#song-title').val()) flag = false;

    const m = parseInt($('#song-m').val());
    const s = parseInt($('#song-s').val());

    if(isNaN(m) || m < 0 || m > 59) flag = false;
    if(isNaN(s) || s < 0 || s > 59) flag = false;

    const perfcnt = parseInt($('#song-perf').val());
    if(isNaN(perfcnt) || perfcnt < 1) flag = false;


    $('#song-add-btn').attr('disabled', !flag);
}

$('#song-add-btn').click(function(){
    const ideaid = $('#id').val();
    const title = $('#song-title').val();
    const link = $('#song-link').val();
    const time = parseInt($('#song-m').val()) * 60 + parseInt($('#song-s').val());
    const perf = $('#song-perf').val();
    const songid = $('#songid').val();

    $.ajax({
        url: '../api/concertquery.php',
        type: 'post',
        data: {
            why: 'add_song',
            ideaid: ideaid,
            title: title,
            link: link,
            time: time,
            perf: perf,
            songid: songid,
        },
        dataType: 'json',
    }).done(function(res){
        if(!res.success){
            alert_float(res.message);
        } else {
            loadSong();
            initializeInput();
        }
    });
});

$('#song-cancel-btn').click(initializeInput);

function showHidden(){
    const hidden = $('#hiddenpos').val();
    if(hidden != 0){
        $('.songlist:visible > div:nth-child(' + hidden + ')').show();
        $('#hiddenpos').val(0);
    }
}

function initializeInput(){
    showHidden();
    $('.songlist:visible').append($('#song-input-container'));

    $('#song-title, #song-link, #song-m, #song-s, #song-perf').val('');
    $('#song-add-btn').html('참가');

    $('#songid').val(0);
    $('#inputpos').val(999999);
}

function moveInput(childno, songid){
    showHidden();

    let targetPos;
    if($('#inputpos').val() <= childno) targetPos = childno + 1;
    else targetPos = childno;

    const target = $('.songlist:visible > div:nth-child(' + targetPos + ')');
    $('#song-input-container').insertAfter(target);
    target.hide();

    $('#song-title').val(target.find('a').html());
    $('#song-link').val(target.find('a').attr('href'));

    const time = target.attr('data-time');
    $('#song-m').val(Math.floor(time / 60));
    $('#song-s').val(time % 60);

    $('#song-perf').val(target.attr('data-perf'));

    $('#song-add-btn').html('수정');

    $('#songid').val(songid);
    $('#inputpos').val(targetPos);
    $('#hiddenpos').val(childno);
}

function loadSong(){
    const ideaid = $('#id').val();
    $.ajax({
        url: '../api/concertquery.php',
        type: 'post',
        data: {
            why: 'load_song',
            ideaid: ideaid,
        },
        dataType: 'json',
    }).done(function(res){
        if(!res.success){
            alert_float(res.message);
        } else {
            $('.songlist:visible > div:not(#song-input-container)').remove();
            $('.songlist:visible').prepend(res.content);
        }
    });
}

function deleteSong(id){
    if(confirm('정말로 삭제하시겠습니까?')){
        $.ajax({
            url: '../api/concertquery.php',
            type: 'post',
            data: {
                why: 'delete_song',
                songid: id,
            },
            dataType: 'json',
        }).done(function(res){
            alert_float(res.message, res.success);
            if(res.success){
                loadSong();
            }
        });
    }
}