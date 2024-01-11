// 시간 보정 출처: https://bloodguy.tistory.com/entry/JavaScript-DatetoISOString-timezone-offset-%EB%B0%98%EC%98%81
const offset = new Date().getTimezoneOffset() * 60000;
const timezoneDate = new Date(Date.now() - offset);

$('#date').val(timezoneDate.toISOString().substring(0, 10));
$('#date').attr('min', timezoneDate.toISOString().substring(0, 10));

$('#isbsg').change(() => {
    if ($('#isbsg').is(':checked')) $('#isconcert').prop('checked', false);
});

$('#isconcert').change(() => {
    if ($('#isconcert').is(':checked')) $('#isbsg').prop('checked', false);
});

$('.add-jungmo-btn').click(function () {
    if (confirm('정모를 생성하시겠습니까?')) {
        const date = $('#date').val();
        const isbsg = $('#isbsg:checked').val() ? 1 : 0;
        const isconcert = $('#isconcert:checked').val() ? 1 : 0;

        $.ajax({
            url: '../api/jungmoquery.php',
            type: 'post',
            data: {
                why: 'add_jungmo',
                date: date,
                isbsg: isbsg,
                isconcert: isconcert,
            },
            dataType: 'json',
        }).done(function (res) {
            alert_float(res.message, res.success);
            if (res.success) loadPostAll();
        });
    }
});

$('.jungmo-header button[name=jungmo-apply]').click(moveInputApply);
$('#jungmo-cancel-btn').click(initializeInput);

$('#jungmo-post-btn').click(function () {
    const id = $('#id').val();
    const postid = $('#postid').val();
    const title = $('#title').val();
    const m = $('#m').val();
    const s = $('#s').val();
    const main = $('#quill .ql-editor').html();
    const target = $(this).parents().eq(2);

    $.ajax({
        url: '../api/jungmoquery.php',
        type: 'post',
        data: {
            why: 'apply_jungmo',
            id: id,
            postid: postid,
            title: title,
            m: m,
            s: s,
            main: main,
        },
        dataType: 'json',
    }).done(function (res) {
        if (!res.success) {
            alert_float(res.message);
        } else {
            initializeInput();
            loadPost(target, res.message);
        }
    });
});

Quill.register("modules/imageCompressor", imageCompressor);

var options = {
    debug: 'warn',
    modules: {
        imageCompressor: {
            quality: 1,
            maxWidth: 1,
            maxHeight: 1,
            imageType: 'image/jpeg',
        },
        toolbar: [
            [{ 'size': ['small', false, 'large', 'huge'] }],
            ['bold', 'italic', 'underline', 'strike'],
            [{ 'background': [] }, { 'color': [] }],
        ]
    },
    placeholder: '곡에 대해 간략히 적어주세요!',
    readOnly: false,
    theme: 'snow'
};

var editor = new Quill('#quill', options);
$('.ql-toolbar.ql-snow').css('padding', '2px 2px 2px .3em');
$('#quill .ql-editor').css('padding', '12px');

editor.on('text-change', function (delta, oldDelta, source) {
    if (!$('#post-input-container').is(':visible')) return;
    const len = $('#quill .ql-editor').html().length;
    const target = $('#post-char-cnt');

    target.html(len + ' / 2000');
    if (len > 2000) target.css('color', 'red');
    else target.css('color', 'black');
});

function initializeInput() {
    $('.jungmo-post-wrapper').show();

    const target = $('#post-input-container');
    target.hide();
    $('main').append(target);
}

function moveInputApply() {
    $('.jungmo-post-wrapper').show();

    editor.deleteText(0, editor.getLength());
    $('#title').val('');
    $('#m').val('');
    $('#s').val('');

    $('#id').val($(this).val());
    $('#postid').val(0);

    const target = $('#post-input-container');
    $(this).parents().eq(1).append(target);
    target.removeClass('border-top');
    target.show();
}

function moveInputFix(dom, id) {
    $('.jungmo-post-wrapper').show();

    const target = $(dom).parents().eq(1);
    target.hide();
    target.after($('#post-input-container'));
    $('#post-input-container').addClass('border-top');
    $('#post-input-container').show();

    let arr = target.find('.jungmo-time').html().split(' ');
    const m = arr[0].replace(/\D/g, '');
    const s = arr[1].replace(/\D/g, '');
    $('#title').val(target.find('.jungmo-title').html());
    $('#m').val(m);
    $('#s').val(s);

    $('#quill .ql-editor').html(target.find('.jungmo-post').html());
    $('#id').val('');
    $('#postid').val(id);
}

function loadPost(target, id) {
    console.log(target);
    $.ajax({
        url: '../api/jungmoquery.php',
        type: 'post',
        data: {
            why: 'get_post',
            id: id,
        },
        dataType: 'json',
    }).done(function (res) {
        if (!res.success) {
            alert_float(res.message);
        } else {
            target.children().remove(':nth-child(n+2):not(#post-input-container)');
            target.append(res.content);
        }
    });
}

function loadPostAll() {
    $.ajax({
        url: '../api/jungmoquery.php',
        type: 'post',
        data: {
            why: 'get_post_all',
        },
        dataType: 'json',
    }).done(function (res) {
        if (!res.success) {
            alert_float(res.message);
        } else {
            $('.jungmo-wrapper').remove();
            $('main').append(res.content);
            $('.jungmo-header button').click(moveInputApply);
        }
    });
}

function deletePost(dom, id) {
    if (confirm('정말로 정모 신청을 삭제하시겠습니까?')) {
        let target = $(dom).parents().eq(2);
        $.ajax({
            url: '../api/jungmoquery.php',
            type: 'post',
            data: {
                why: 'delete_post',
                id: id,
            },
            dataType: 'json',
        }).done(function (res) {
            alert_float(res.message, res.success);
            if (res.success) loadPost(target, res.id);
        });
    }
}

function deleteJungmo(id) {
    if (confirm('정말로 정모를 삭제하시겠습니까?')) {
        const target = $(this).parent().parent();

        $.ajax({
            url: '../api/jungmoquery.php',
            type: 'post',
            data: {
                why: 'delete_jungmo',
                id: id,
            },
            dataType: 'json',
        }).done(function (res) {
            alert_float(res.message, res.success);
            if (res.success)
                initializeInput();
            loadPostAll();
        });
    }

}

$('.bi-clipboard').click(function () {
    const target = $(this).parents().eq(1);
    const title = target.find('.jungmo-title').html();
    const time = target.find('.jungmo-time').html();
    let info = target.find('.jungmo-post').html();

    info = info.split('</p><p>').join('\r\n');
    info = info.split(/<(?:\/)?(?:p|em|strong|u|s)>/).join('');

    if (navigator.clipboard) {
        navigator.clipboard.writeText(title + time + '\r\n' + info);
        alert_float('내용이 복사되었습니다!', true);
    } else {
        alert_float('브라우저에서 복사 API를 지원하지 않습니다 :(', false);
    }
});