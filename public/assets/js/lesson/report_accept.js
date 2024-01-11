let menuon = false;
let reportid = 0;

$('#report-showlist').click(function (e) {
    e.stopPropagation();
    foldLessonlist(!menuon);
});

$(document).click(() => foldLessonlist(false));

function foldLessonlist(m) {
    if (m) $('#report-container').addClass('move');
    else $('#report-container').removeClass('move');
    menuon = m;
}

$('#report-lessonid').change(function () {
    loadStudent().then(() => { loadReportList().then(loadReport) });
});

$('#report-name').change(function () {
    loadReportList().then(loadReport);
});

$('#report-date').change(function () {
    loadReport();
});

$('#btn-accept').click(() => acceptReport(true));
$('#btn-reject').click(() => acceptReport(false));

function loadReportByInfo(lessonid, email, reportid) {
    $('#report-lessonid').val(lessonid);
    loadStudent().then(() => {
        $('#report-name').val(email);
        loadReportList().then(() => {
            $('#report-date').val(reportid);
            loadReport();
            changeTab(1, $('#tab-btn-report'));
        });
    });
}

function loadStudent() {
    return new Promise(resolve => {
        const lessonid = $('#report-lessonid').val();
        if (!lessonid) {
            resolve();
            return;
        }

        $.ajax({
            url: '../api/lessonquery.php',
            type: 'post',
            data: {
                why: 'reportstudent_get',
                lessonid: lessonid,
            },
            dataType: 'json',
        }).done(function (res) {
            if (!res.success) {
                alert_float(res.message);
            } else {
                const target = $('#report-name');
                target.html('');

                const nmlist = res.content;
                if (nmlist.length > 0) {
                    target.attr('disabled', false);
                    for (id in nmlist) {
                        target.append('<option class="text-grey" value="' + nmlist[id].email + '">' + nmlist[id].gennm + '</option>')
                    }

                } else {
                    target.html('<option value="">부원 없음</option>');
                    target.attr('disabled', true);
                }

            }

            resolve();
        });
    });
}

function loadReportList() {
    return new Promise(resolve => {
        const lessonid = $('#report-lessonid').val();
        const email = $('#report-name').val();

        if (!lessonid || !email) {
            $('#report-date').html('<option value="">레슨일지 없음</option>');
            $('#report-date').attr('disabled', true);
            $('#report-list').html('<button class="border-bottom text-grey" disabled>레슨일지 없음</button>');
            resolve();
            return;
        }

        $.ajax({
            url: '../api/lessonquery.php',
            type: 'post',
            data: {
                why: 'reportlist_get',
                email: email,
                lessonid: lessonid,
            },
            dataType: 'json',
        }).done(function (res) {
            if (!res.success) {
                alert_float(res.message);
            } else {
                const reportlist = $('#report-list');
                const datelist = $('#report-date');

                reportlist.html('');
                datelist.html('');

                if (res.content.length) {
                    datelist.attr('disabled', false);

                    for (i in res.content) {
                        const row = res.content[i];
                        datelist.append('<option value="' + row.id + '">' + row.date + '</option>');

                        if (row.accept == 1 || row.accept == 2) row.date += '<i class="bi bi-exclamation-lg"></i>';
                        reportlist.append('<button name="btn' + row.id + '" onclick="setReportid(' + row.id + ', this)" class="border-bottom ' + (row.accept >= 2 ? 'text-accept' : 'text-reject') + '">' + row.date + '</button>');
                    }

                } else {
                    datelist.html('<option value="">레슨일지 없음</option>');
                    datelist.attr('disabled', true);
                    reportlist.html('<button class="border-bottom text-grey" disabled>레슨일지 없음</button>');
                }
            }

            resolve();
        });
    });
}

function setReportid(id, dom) {
    $('#report-date').val(id);
    loadReport(dom);
}

function loadReport(dom = null) {
    const id = $('#report-date').val();

    if (!id) {
        $('#image-viewer').hide();
        $('#image-inner').show();
        $('.ql-editor').html('');
        $('#report-status').html('');
        $('#btn-accept, #btn-reject').attr('disabled', true);
        reportid = 0;
        return;
    }

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
            alert_float(res.message);
        } else {
            $('#report-list button').removeClass('selected');
            if (dom) {
                $(dom).addClass('selected');
            } else {
                $('#report-list button[name="btn' + id + '"]').addClass('selected');
            }

            $('#report-date').val(id);

            $('#image-inner').hide();
            $('#image-viewer').show();
            $('#image-viewer').css('background-image', 'url(../image/lesson/' + id + ')');

            $('.ql-editor').html(res.main);
            reportid = id;

            updateStatus(res.accept);
            $('#btn-accept, #btn-reject').attr('disabled', false);
        }
    });
}

function acceptReport(accept) {
    if (!reportid) return;

    $.ajax({
        url: '../api/lessonquery.php',
        type: 'post',
        data: {
            why: 'report_accept',
            id: reportid,
            accept: accept,
        },
        dataType: 'json',
    }).done(function (res) {
        alert_float(res.message, res.success);
        if (res.success) {
            updateStatus(res.accept, true);
        }
    });
}

function updateStatus(status, updatebtn = false) {
    const statusnm = ['거절됨', '확인중', '확인중', '승인됨'];
    let acceptClass;
    if (status >= 2) acceptClass = 'text-accept';
    else acceptClass = 'text-reject';

    $('#report-status').removeClass('text-accept text-reject text-grey').addClass(acceptClass);
    $('#report-status').html(statusnm[status]);

    if (updatebtn) {
        const lessonid = $('#report-lessonid').val();
        const btnstr = 'button[name=btn' + reportid + ']';
        const btntarget = $('#report-list ' + btnstr + ', #lesson' + lessonid + ' ' + btnstr);

        if (status >= 2) btntarget.removeClass('btn-report-rejected').addClass('btn-report-accepted');
        else btntarget.addClass('btn-report-rejected').removeClass('btn-report-accepted');

        btntarget.children('i').remove();
        if (status == 1 || status == 2) btntarget.append('<i class="bi bi-exclamation-lg"></i>');
    }
}