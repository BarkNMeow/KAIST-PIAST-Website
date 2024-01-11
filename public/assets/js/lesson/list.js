updateTimer();
setTimeout(() => { updateTimer(); setInterval(updateTimer, 1000);}, 1000 - (Date.now() % 1000));
updateInput(parseInt($('#active').val()));

function deleteLesson(id){
    if(confirm('정말 삭제하시겠습니까?')){
        $.ajax({
            url: '../api/lessonquery.php',
            type: 'post',
            data: {
                why: 'lesson_delete',
                id: id,
            },
            dataType: 'json',
        }).done(function(res){
            if(!res.success) alert_float(res.message);
            else {
                alert(res.message);
                window.location.reload();
            }
        });
    }
}

function applyLesson(id){
    $.ajax({
        url: '../api/lessonquery.php',
        type: 'post',
        data: {
            why: 'lesson_apply',
            id: id,
        },
        dataType: 'json',
    }).done(function(res){
        if(!res.success) alert_float(res.message);
        else {
            alert(res.message);
            window.location.reload();
        }
    });
}

function cancelLesson(id){
    if(confirm('정말 레슨 신청을 취소하시겠습니까?')){
        $.ajax({
            url: '../api/lessonquery.php',
            type: 'post',
            data: {
                why: 'lesson_cancel',
                id: id,
            },
            dataType: 'json',
        }).done(function(res){
            if(!res.success) alert_float(res.message);
            else {
                alert(res.message);
                window.location.reload();
            }
        });
    }
}

$('#lesson-apply-setting').click(function(){
    updateInput();
    $('#overlay').show();
});

$('#start-init').click(function(){
    $('#start-date').val('');
    $('#start-time').val('');
})

$('#end-init').click(function(){
    $('#end-date').val('');
    $('#end-time').val('');
})

$('#btn-confirm').click(function(){
    let starttime, endtime;

    const sd = $('#start-date').val();
    const st = $('#start-time').val();
    if(sd == '' || st == '') starttime = -1;
    else starttime = new Date(sd + ' ' + st).getTime() / 1000;

    const ed = $('#end-date').val();
    const et= $('#end-time').val();
    if(ed == '' || et == '') endtime = -1;
    else endtime = new Date(ed + ' ' + et).getTime() / 1000;

    $.ajax({
        url: '../api/lessonquery.php',
        type: 'post',
        data: {
            why: 'apply_toggle',
            starttime: starttime,
            endtime: endtime,
        },
        dataType: 'json',
    }).done(function(res){
        if(!res.success) alert_float(res.message);
        else {
            alert(res.message);
            window.location.reload();
        }
    });
});

function updateTimer(){
    let timer = '';
    const deadline = parseInt($('#active').val()) ? $('#end').val() : $('#start').val();
    const mode = parseInt($('#active').val()) ? '종료' : '시작';
    const timenow = parseInt(new Date(Date.now()).getTime() / 1000);
    const timediff = deadline - timenow;
    const target = $('#lesson-apply-timer');

    if(timediff <= 0 && deadline != -1)
        window.location.reload();

    if(deadline == -1){
        timer = '<i class="bi bi-clock"></i> 레슨 신청 ' + mode + ' 계획이 없습니다.';
    } else {
        timer = '<i class="bi bi-clock"></i> ' + parseInt(timediff / 86400) + 'd ' + (parseInt(timediff / 3600) % 24) + 'h ' + (parseInt(timediff / 60) % 60)+ 'm ' + (timediff % 60) + 's 후 ' + mode;
    }
    target.html(timer);
}

// 시간 보정 출처: https://bloodguy.tistory.com/entry/JavaScript-DatetoISOString-timezone-offset-%EB%B0%98%EC%98%81
function num2date(timestamp){
    const offset = new Date().getTimezoneOffset() * 60000;
    return new Date(timestamp - offset);
}

function updateInput(){
    let starttime, endtime;
    const st = $('#start').val();
    const et = $('#end').val();

    if(st == -1){
        starttime = Date.now() + 60 * 1000;
    } else {
        starttime = $('#start').val() * 1000;
    }

    $('#start-date').val(num2date(starttime).toISOString().substring(0, 10));
    $('#start-time').val(num2date(starttime).toISOString().substring(11, 16));

    if(et == -1 && st == -1){
        $('#end-date').val('');
        $('#end-time').val('');
    } else {
        if(et == -1){
            endtime = Date.now() + 60 * 1000;
        } else {
            endtime = $('#end').val() * 1000;
        }

        $('#end-date').val(num2date(endtime).toISOString().substring(0, 10));
        $('#end-time').val(num2date(endtime).toISOString().substring(11, 16));
    }
}