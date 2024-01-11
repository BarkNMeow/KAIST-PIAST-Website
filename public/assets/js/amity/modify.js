let memory = {};
let sex = true;

$('#sex-reveal').click(function () {
    if ($(this).is(':checked')) $('body').addClass('reveal');
    else $('body').removeClass('reveal');
})

function amityChangeTab(n, dom) {
    $('.tab-container').hide();
    $('.tab-container').eq(n).show();
    $('#id').val(n - 1);

    $('.tab-btn').removeClass('selected');
    $(dom).addClass('selected');

    $('#tab-btn-border-bottom').css('transform', 'translateX(' + (7.75 * n) + 'rem)');
}

// 친목조장 관리
function showLeaderlist() {
    $('#leaderlist-search-input').val('#all');
    getLeader();
    $('#leaderlist-overlay').show();
}

$('#leaderlist-all-btn').click(function () {
    $('#leaderlist-search-input').val('#all');
    getLeader();
});

$('#leaderlist-search-btn').click(getLeader);

$('#leaderlist-search-input').on('keyup', function (key) {
    if (key.keyCode == 13) {
        $(this).blur();
        getLeader();
    }
});

function getLeader() {
    const nm = $('#leaderlist-search-input').val();

    $.ajax({
        url: '../api/amityquery.php',
        type: 'post',
        data: {
            why: 'leader_get',
            nm: nm,
        },
        dataType: 'json',
    }).done(function (res) {
        if (!res.success) alert_float(res.message);
        $('#leaderlist').html(res.content);
    });
}

function modifyLeader(email) {
    $.ajax({
        url: '../api/amityquery.php',
        type: 'post',
        data: {
            why: 'leader_modify',
            email: email,
        },
        dataType: 'json',
    }).done(function (res) {
        alert_float(res.message, res.success);
        if (res.success) {
            getLeader();
            getGroupList();
        }
    });
}

function getGroupList() {
    $.ajax({
        url: '../api/amityquery.php',
        type: 'post',
        data: {
            why: 'group_list_get',
        },
        dataType: 'json',
    }).done(function (res) {
        if (res.success) {
            $('#group-wrapper').html(res.content);
        } else {
            alert_float(res.message);
        }
    });
}

function removeGroup(email) {
    if (confirm('정말로 이 친목조를 삭제하시겠습니까?')) modifyLeader(email);
}

// 친목조원 관리
function showMemberlist(id) {
    memory = {};

    $('#memberlist-id').val(id);
    $('#memberlist-search-input').val('#all');
    $('#memberlist-controlall').prop('checked', false);
    getMember();
    $('#memberlist-overlay').show();
}

$('#memberlist-all-btn').click(function () {
    $('#memberlist-search-input').val('#all');
    getMember();
});

$('#memberlist-sex-btn').click(function () {
    if (sex) {
        $('#memberlist-search-input').val('#male');
        $(this).html('<i class="bi bi-gender-female"></i>');
    } else {
        $('#memberlist-search-input').val('#female');
        $(this).html('<i class="bi bi-gender-male"></i>');
    }

    sex = !sex;
    getMember();
})

$('#memberlist-search-btn').click(getMember);

$('#memberlist-search-input').on('keyup', function (key) {
    if (key.keyCode == 13) {
        $(this).blur();
        getMember();
    }
});

$('#memberlist-controlall').change(function () {
    const checked = $(this).is(':checked');

    $('#memberlist > div').each(function () {
        const checkbox = $(this).find('input');
        const email = checkbox.attr('data-email');

        if (memory[email]) {
            if (memory[email]['checked'] == checked) return;
            else delete memory[email];
        } else {
            const thischecked = checkbox.is(':checked');
            if (thischecked == checked) return;
            else {
                const gennm = $(this).children('div:first-child').html();
                const sex = $(this).children('div:first-child').attr('class');
                memory[email] = { 'gennm': gennm, 'checked': checked, 'sex': sex };
            }
        }

        checkbox.prop('checked', checked);
    });
});

function getMember() {
    const id = $('#memberlist-id').val();
    const nm = $('#memberlist-search-input').val();

    $.ajax({
        url: '../api/amityquery.php',
        type: 'post',
        data: {
            why: 'member_get',
            nm: nm,
            id: id,
        },
        dataType: 'json',
    }).done(function (res) {
        if (!res.success)
            alert_float(res.message);
        else {
            const data = res.data;
            for (email in memory) {
                if (data[email]) {
                    data[email]['checked'] = memory[email]['checked'];
                } else if (nm == '#all') {
                    data[email] = {};
                    data[email]['gennm'] = memory[email]['gennm'];
                    data[email]['sex'] = memory[email]['sex'];
                    data[email]['checked'] = memory[email]['checked'];
                }
            }

            const target = $('#memberlist');
            $('#memberlist').empty();

            if (Object.keys(data).length == 0) {
                target.append('<div class="overlay-list-row text-grey"><div>검색 결과가 없습니다.</div><div></div></div>');
                return;
            }

            for (email in data) {
                const checked = data[email]['checked'] ? ' checked' : '';
                target.append('<div class="overlay-list-row"><div class="' + data[email]['sex'] + '">' + data[email]['gennm'] + '</div><div><input type="checkbox" data-email="' + email + '" ' + checked + '></div></div>');
            }

            $('#memberlist input').click(function () {
                const email = $(this).attr('data-email');
                if (!memory[email]) {
                    memory[email] = {};

                    memory[email]['gennm'] = $(this).parent().parent().children('div:first-child').html();
                    memory[email]['sex'] = $(this).parent().parent().children('div:first-child').attr('class');
                    memory[email]['checked'] = $(this).is(':checked');
                } else {
                    delete memory[email]; // 두 번 클릭 => 변동 X
                }
            });
        }
    });
}

$('#memberlist-confirm').click(function () {
    if (Object.keys(memory).length == 0) {
        $('#memberlist-overlay').hide();
        return;
    }

    const id = $('#memberlist-id').val();
    const data = JSON.stringify(memory);

    $.ajax({
        url: '../api/amityquery.php',
        type: 'post',
        data: {
            why: 'member_modify',
            data: data,
            id: id,
        },
        dataType: 'json',
    }).done(function (res) {
        alert_float(res.message, res.success);
        if (res.success) {
            $('#memberlist-overlay').hide();
            getGroupList();
        }
    });
});


// 친목점수 항목