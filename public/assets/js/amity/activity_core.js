let scoreBoard = null;
let scoreCalc = [];
let viewidx = 0;
let imagenum = $('.image-viewer').length;

$('.image-ui .left-btn, .image-ui .right-btn').click(function () {
    const isLeft = $(this).hasClass('left-btn');
    if (isLeft) viewidx = (viewidx - 1 + imagenum) % imagenum;
    else viewidx = (viewidx + 1) % imagenum;

    updateImageview();
});

function updateImageview() {
    $('#activity-image .image-viewer').hide();
    $('#activity-image .image-viewer').eq(viewidx).show();
    $('.menu span').html((viewidx + 1) + ' / ' + imagenum);
}

$('select[name=select-event]').change(function () {
    const row = $(this).parent();
    const id = $(this).val();

    if (id == -1) {
        row.find('input').attr('disabled', true);
        row.find('input').val('');
        row.children('div:last-child').html('?');

        if ($('#score-table > div').last().find('select').val() == -1) {
            row.remove();
        }

    } else {
        if ($('#score-table > div').last().find('select').val() != -1) {
            row.after(row.clone(true));
        }

        const scoreinfo = scoreBoard[id];
        row.find('input[name=n]').attr('disabled', (scoreinfo['coeff'] == 0));
        row.find('input[name=k]').attr('disabled', !scoreinfo['allowk']);
        row.find('input').val('');

        if (scoreinfo['coeff'] == 0 && !scoreinfo['allowk']) {
            row.children('div:last-child').html(scoreinfo['bias']);
        } else {
            row.children('div:last-child').html('?');
        }
    }

    updateScore();
});

$('input[name=n], input[name=k]').on('keyup change', function () {
    const row = $(this).parent();
    const id = row.find('select[name=select-event]').val();
    const target = row.children('div:last-child');

    const n = parseInt(row.find('input[name=n]').val());
    const k = parseInt(row.find('input[name=k]').val());
    let score = scoreCalc[id](n, k);

    if (isNaN(parseInt(score))) target.html('');
    else target.html(score);
    updateScore();
});

function updateScore() {
    let score = 0;
    $('#score-table > div').each(function () {
        const inner = $(this).children('div:last-child').html();
        if (!isNaN(parseInt(inner))) score += parseInt(inner);
    });

    $('#activity-score-total').html(score);
    verifyInput();
}

function getScoreboard(json) {
    scoreBoard = json;

    for (i = 0; i < scoreBoard.length; i++) {
        const scoreinfo = scoreBoard[i];
        const lambda1 = (n, k) => scoreinfo['bias'];

        let lambda2 = (n, k) => 0;
        if (scoreinfo['coeff']) {
            lambda2 = (n, k) => scoreinfo['coeff'] * n + lambda1(n, k);
        } else {
            lambda2 = lambda1;
        }

        let lambda3 = (n, k) => 0;
        if (scoreinfo['allowk']) {
            lambda3 = (n, k) => k * lambda2(n, k);
        } else {
            lambda3 = lambda2;
        }

        console.log(scoreinfo.id)
        scoreCalc[scoreinfo.id] = lambda3;
    }
}