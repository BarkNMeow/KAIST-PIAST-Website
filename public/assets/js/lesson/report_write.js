const Delta = Quill.import('delta');

let options = {
    debug: 'warn',
    modules: {
        toolbar: [
            [{ 'size': ['small', false, 'large', 'huge'] }],
            ['bold', 'italic', 'underline', 'strike'],
            [{ 'background': [] }, { 'color': [] }],
        ]
    },
    placeholder: '레슨에서 무엇을 배웠나요?',
    readOnly: false,
    theme: 'bubble'
};

let editor = new Quill('#quill', options);
editor.on('text-change', function (delta, oldDelta, source) {
    if (delta.ops.filter(i => i.insert && i.insert.image).length > 0) {
        const retain_del = delta.ops.filter(i => i.retain);
        const retain_len = (retain_del.length ? retain_del[0].retain : 0);
        editor.updateContents(new Delta().retain(retain_len).delete(1));
    }
});

let menuon = false;

$('#report-showlist').click(function (e) {
    e.stopPropagation();
    foldLessonlist(!menuon);
});

$('#report-lessonid').on('change', loadReportlist);

$('#new-report').click(function () {
    $('#report-list button').removeClass('selected');
    $(this).addClass('selected');

    $('#id').val('');
    initInput();
    // foldLessonlist(true);
});

$(document).click(() => foldLessonlist(false));

function foldLessonlist(m) {
    if (m) $('#report-container').addClass('move');
    else $('#report-container').removeClass('move');
    menuon = m;
}

function loadReportlist() {
    const lessonid = $('#report-lessonid').val();
    $.ajax({
        url: '../api/lessonquery.php',
        type: 'post',
        data: {
            why: 'reportlist_get',
            lessonid: lessonid,
            email: null,
        },
        dataType: 'json',
    }).done(function (res) {
        if (!res.success) {
            alert_float(res.message)
        } else {
            const target = $('#report-list');
            target.children('button:not(#new-report)').remove();

            for (i in res.content) {
                const row = res.content[i];
                target.append('<button onclick="loadReport(' + row.id + ', this)" class="border-bottom ' + (row.accept >= 2 ? 'text-accept' : 'text-reject') + '">' + row.date + '</button>');
            }

            $('#new-report').click();
        }
    });
}

function loadReport(id, dom = null) {
    $.ajax({
        url: '../api/lessonquery.php',
        type: 'post',
        data: {
            why: 'report_get',
            id: id,
        },
        dataType: 'json',
    }).done(function (res) {
        if (!res.success) {
            alert_float(res.message)
        } else {
            if (dom) {
                $('#report-list button').removeClass('selected');
                $(dom).addClass('selected');
            }

            $('#report-date').val(res.date);

            const statusnm = ['거절됨', '확인중', '확인중', '승인됨'];
            let acceptClass;
            if (res.accept >= 2) acceptClass = 'text-accept';
            else acceptClass = 'text-reject';

            $('#report-status').removeClass('text-accept text-reject text-grey').addClass(acceptClass);
            $('#report-status').html(statusnm[res.accept]);

            $('#image-inner').hide();
            $('#image-viewer').show();
            $('#image-viewer').css('background-image', 'url(../image/lesson/' + id + ')');

            $('.ql-editor').html(res.main);
            $('#id').val(id);

            // foldLessonlist(true);
        }
    });
}

function initInput() {
    const iddom = $('#id');

    if (iddom.val()) {
        loadReport(iddom.val());
    } else {
        $('#report-date').val('');

        $('#report-status').removeClass('text-accept text-reject').addClass('text-grey');
        $('#report-status').html('작성중');
        $('#file').val('');

        $('#image-inner').show();
        $('#image-viewer').hide();

        editor.setText('');
        iddom.val('');
    }
}

$('#file').change(function () {
    console.log($(this)[0].files)
    if ($(this)[0].files && $(this)[0].files[0]) {
        const reader = new FileReader();

        reader.onload = e => {
            $('#image-inner').hide();
            $('#image-viewer').show();
            $('#image-viewer').css('background-image', 'url(' + e.target.result + ')');
        }

        reader.readAsDataURL($(this)[0].files[0]);
    }
});

$('#image-inner, #image-viewer').click(function () {
    $('#file').trigger('click');
});

editor.on('text-change', function (delta, oldDelta, source) {
    const len = $('.ql-editor').html().length;
    const target = $('#text-count');

    target.html(len + ' / 1000');
    if (len > 1000) target.css('color', 'red');
    else target.css('color', 'var(--black)');
});


$('#btn-init').click(function () {
    if (confirm('정말 초기화하시겠습니까?')) initInput();
});

$('#btn-submit').click(function () {
    let data = new FormData();
    const files = $('#file')[0].files;
    const lessonid = $('#report-lessonid').val();
    const date = $('#report-date').val();
    const main = $('.ql-editor').html();
    const id = $('#id').val();

    data.append('why', 'report_post');
    data.append('image', files[0]);
    data.append('lessonid', lessonid);
    data.append('date', date);
    data.append('main', main);
    data.append('id', id);

    $.ajax({
        url: '../api/lessonquery.php',
        type: 'post',
        processData: false,
        contentType: false,
        data: data,
        dataType: 'json',
    }).done(function (res) {
        alert_float(res.message, res.success);
        if (res.success) loadReportlist();
    });
});

$('#btn-delete').click(function () {
    if (confirm('정말 삭제하시겠습니까?')) {
        const id = $('#id').val();
        if (id) {
            $.ajax({
                url: '../api/lessonquery.php',
                type: 'post',
                data: {
                    why: 'report_delete',
                    id: id,
                },
                dataType: 'json',
            }).done(function (res) {
                alert_float(res.message, res.success);
                if (res.success) {
                    loadReportlist();
                }
            });
        } else {
            initInput();
        }
    }
});

