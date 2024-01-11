let formstate = 0;

function changeFormState(n) {
    $('#form-wrapper').removeClass('state' + formstate).addClass('state' + n);
    $('#form-wrapper > div:nth-child(' + (formstate + 1) + ')').removeClass('fade-in').addClass('fade-out');
    $('#form-wrapper > div:nth-child(' + (n + 1) + ')').removeClass('fade-out').addClass('fade-in');
    formstate = n;
}

$('#login-email, #login-passwd').on('blur input', function () {
    const me = $(this);
    if (me.val()) me.removeClass('invalid');
    else me.addClass('invalid');
})

$('#login-passwd').on('keyup', function (key) {
    if (key.keyCode == 13) tryLogin();
});
$('#login-confirm').click(tryLogin);

function tryLogin() {
    const email = $('#login-email').val();
    const passwd = $('#login-passwd').val();
    const stay = $('#login-stay').is(':checked');

    if (!email) {
        alert_float('이메일을 입력해주세요.');
        $('#login-email').focus();
        return;
    }

    if (!passwd) {
        alert_float('비밀번호를 입력해주세요.');
        $('#login-passwd').focus();
        return;
    }

    console.log(';asdfasdf');

    $.ajax({
        url: 'api/authquery.php',
        type: 'post',
        data: {
            why: 'auth_login',
            email: email,
            passwd: passwd,
            stay: stay,
        },

        dataType: 'json',
    }).done(function (res) {
        if (res.success) {
            const href = $('#login-redirect-href').val();
            window.location.href = (href ? href : 'dashboard');
        } else {
            alert_float(res.message);
        }
    });
}

// signup part starts here
let checklist = {
    'email': false, 'passwd': false, 'gen': false, 'nm': false, 'sex': false,
    'yr': true, 'maj': true, 'bday': true, 'phonenum': true, 'agree': false
};

let checkfunc = {
    'email': val => (val ? true : false),
    'passwd': verifyPassword,
    'gen': val => (!isNaN(val) && val.search(/[\+|-|\.]/) < 0 && parseInt(val) > 0),
    'nm': val => (/^[가-힣]{2,20}$/.test(val)),
    'sex': val => ((val) ? true : false),
    'yr': val => ((val === '') ? true : /^[0-9]{2}$/.test(val)),
    'maj': _ => true,
    'by': verifyDate,
    'bm': verifyDate,
    'bd': verifyDate,
    'phonenum': val => ((val === '') ? true : /^[0-9]{11}$/.test(val))
};

let flag = false;
Object.keys(checkfunc).forEach((key) => {
    $('#signup-' + key).on('input blur', function () {
        console.log(key);
        const verify = checkfunc[key]($(this).val());

        if (['by', 'bm', 'bd'].includes(key)) checklist['bday'] = verify;
        else checklist[key] = verify;

        if (verify) {
            $(this).removeClass('invalid');
            validateSignup();
        } else {
            $(this).addClass('invalid');
            flag = false;
            $('#signup-confirm').attr('disabled', true);
        }
    });
});

function verifyPassword(val) {
    return (val.length >= 8);
}

function verifyDate(_) {
    const y = $('#signup-by').val(), m = $('#signup-bm').val(), d = $('#signup-bd').val();
    let result = true;

    if (y === '' && m === '' && d === '') result = true;
    else if (!y || !m || !d) result = false;
    else {
        let date = y + '-' + m + '-' + d;
        const dateObj = new Date(date);

        if ((dateObj === "Invalid Date") || isNaN(dateObj)) result = false;
        else {
            const oy = dateObj.getFullYear(), om = dateObj.getMonth() + 1, od = dateObj.getDate();
            if (oy != y || om != m || od != d) result = false;
        }
    }

    if (result) $('#signup-by, #signup-bm, #signup-bd').removeClass('invalid');
    else $('#signup-by, #signup-bm, #signup-bd').addClass('invalid');

    return result;
}

$('#signup-viewcontract').click(() => {
    $('#overlay').show();
    $('.overlay-wrapper').addClass('overlay-fade-in');
});

$('#contract-agree-yes, #contract-agree-no').on('click', function () {
    checklist['agree'] = ($(this).hasClass('btn-black') ? true : false);

    if (checklist['agree']) {
        $('#signup-viewcontract').html('동의함');
        validateSignup();
    } else {
        $('#signup-viewcontract').html('보기*');
        flag = false;
        $('#signup-confirm').attr('disabled', true);
    }

    $('.overlay-wrapper').removeClass('overlay-fade-in');
    $('#overlay').hide();
});

function validateSignup() {
    flag = true;
    for (key in checklist) {
        flag = flag && checklist[key];
    }

    $('#signup-confirm').attr('disabled', !flag);
}

$('#signup-confirm').click(function () {
    let data = {};
    let fields = Object.keys(checklist);

    data['why'] = 'user_insert';
    fields.forEach(tag => {
        if (tag == 'bday') {
            const y = $('#signup-by').val(), m = $('#signup-bm').val(), d = $('#signup-bd').val();
            if (!y || !m || !d) data[tag] = '';
            else data[tag] = y + '-' + m + '-' + d;
        }
        else if (tag == 'agree') data[tag] = (checklist['agree'] ? 1 : 0);
        else data[tag] = $('#signup-' + tag).val();
    });

    $.ajax({
        url: '../api/authquery.php',
        type: 'post',
        data: data,
        dataType: 'json',
    }).done(function (res) {
        if (!res.success) {
            alert_float(res.message);
        } else {
            changeFormState(3);
        }
    });
});

$('#passwdlost-input').on('input', function () {
    const valid = ($(this).val() !== '');

    if (valid) $(this).removeClass('invalid');
    else $(this).addClass('invalid');

    $('#passwdlost-confirm').attr('disabled', !valid);
});

$('#passwdlost-confirm').click(function () {
    const email = $('#passwdlost-input').val();

    $.ajax({
        url: '../api/authquery.php',
        type: 'post',
        data: {
            why: 'reset_passwd',
            email: email,
        },
        dataType: 'json',
    }).done(function (res) {
        if (!res.success) {
            alert_float(res.message);
        } else {
            changeFormState(4);
        }
    });
});