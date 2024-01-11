function loadPage(page) {
    $.ajax({
        url: '../api/phonebookquery.php',
        type: 'post',
        data: {
            why: 'get_page',
            val: page,
            pp: 10,
            mode: 1,
        },
        dataType: 'json',
    }).done(function (res) {
        if (res.success) {
            $('#page-left').html(res.left);
            $('#page-right').html(res.right);
            $('#page-left-idx').html(res.leftp);
            $('#page-right-idx').html(res.rightp);

            if (page % 2 == 0) showPage(0);
            else showPage(1);

            $('#page-left').scrollTop(0);
            $('#page-right').scrollTop(0);
        } else {
            alert_float(res.message, res.success);
        }
    });
}

function showPage(right) {
    if (right) {
        $('#page-left').removeClass('shown');
        $('#page-left-idx').removeClass('shown');
        $('#page-right').addClass('shown');
        $('#page-right-idx').addClass('shown');
        $('#page-right').scrollTop(0);
    } else {
        $('#page-right').removeClass('shown');
        $('#page-right-idx').removeClass('shown');
        $('#page-left').addClass('shown');
        $('#page-left-idx').addClass('shown');
        $('#page-left').scrollTop(0);
    }
}

function showtmiinput(dom) {
    const me = $(dom);
    const parent = me.parent();
    const input = parent.find('input');
    const tmi = parent.find('div:first-child');

    if (parent.hasClass('notmi')) input.val();
    else input.val(tmi.html());

    me.hide();
    tmi.hide();
    input.show();
    input.focus();
}

function checkblur(e, dom) {
    if (e.keyCode == 13) {
        $(dom).blur();
    }
}

function fixtmi(dom) {
    const me = $(dom);
    const newtmi = me.val();
    const email = me.attr('data-email');

    $.ajax({
        url: '../api/phonebookquery.php',
        type: 'post',
        data: {
            why: 'modify_tmi',
            email: email,
            tmi: newtmi,
        },
        dataType: 'json',
    }).done(function (res) {
        alert_float(res.message, res.success);

        const parent = me.parent();
        const btn = parent.find('i');
        const tmi = parent.find('div:first-child');

        tmi.html(newtmi);
        tmi.show();
        btn.show();
        me.hide();

        if (newtmi === '') {
            tmi.html('비고 항목이 없습니다.');
            parent.addClass('notmi');
        } else {
            parent.removeClass('notmi');
        }
    });
};