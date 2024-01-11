let notcnt = 0;
let page = 0;
let endtime = Date.now() / 1000 + 10;

const target = $('#notify-content');

setTimeout(continueNotify, 500);

$('#btn-notify-add').click(continueNotify);

$('#btn-notify-readall').click(function(){
    $.ajax({
        url: '../api/notification.php',
        type: 'post',
        data: {
            why: 'readall',
        },
        dataType: 'json',
    }).done(function(res){
        if(!res.success){
            alert_float(res.message);
        } else {
            target.children('div:not(.nothing)').addClass('old');
            notcnt = 0;
            $('#notify-cnt').html(notcnt);
        }
    });
});

function continueNotify(){
    $.ajax({
        url: '../api/notification.php',
        type: 'post',
        data: {
            why: 'load_before',
            page: page,
            endtime: endtime,
        },
        dataType: 'json',
    }).done(function(res){
        $('#notify-arrow-repeat').remove();
        if(!res.success){
            alert_float(res.message);
        } else {
            const newnot = $(res.content);
            if(newnot.hasClass('text-grey')){
                target.append('<div class="text-grey border-bottom">알림이 없습니다! 정말 평화롭죠?</div>');
            } else {
                newnot.appendTo(target);
                $('#btn-notify-add').show();
                endtime = res.endtime;
                page += 1;

                target.children('div').click(function(){
                    const id = parseInt($(this).attr('data-id'));
                    const old = $(this).hasClass('old');
                    
                    if(!id) return;
                    $.ajax({
                        url: '../api/notification.php',
                        type: 'post',
                        data: {
                            why: 'read',
                            id: id,
                        },
                        dataType: 'json',
                    }).done(function(res){
                        if(res.success){
                            if(!old) notcnt -= 1;
                            $('#notify-cnt').html(notcnt);
                            window.location.href = res.href;
                        } else {
                            alert_float(res.message);
                        }
                    });
                });
            }

            if(res.next){
                $('#btn-notify-add').show();
            } else {
                $('#btn-notify-add').hide();
            }
        }
    });
}

function setNotcnt(n){
    notcnt = n;
}