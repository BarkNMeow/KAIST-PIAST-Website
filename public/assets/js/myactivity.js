function getConfig(max, data, color, name, unit) {
    return {
        type: 'doughnut',
        data: {
            labels: [0, 0],
            datasets: [{
                data: [data, (max > data ? max - data : 0)],
                backgroundColor: ['hsl(' + color + ', 80%, 60%)', 'hsl(0, 0%, 60%)'],
            }]
        },
        options: {
            maintainAspectRatio: true,
            aspectRatio: 1,
            borderWidth: 0,
            borderSkipped: false,
            indexAxis: 'y',
            barPercentage: 0.9,
            cutout: '80%',
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    enabled: false
                },
            },
        },
        plugins: [{
            id: 'center',
            beforeDraw: function (chart, args, options) {
                const ctx = chart.ctx;
                const h = chart.chartArea.height;
                const w = chart.chartArea.width;

                ctx.save();
                ctx.textAlign = 'center';

                ctx.font = '500 20px Noto Sans KR';
                ctx.textBaseline = 'bottom';
                ctx.fillText(name, w / 2, h / 2);

                ctx.font = '14px Noto Sans KR';
                ctx.textBaseline = 'top';
                ctx.fillText(data + ' / ' + max + unit, w / 2, h / 2 + 6);
                // ctx.restore();
            }
        }]
    }
}

const conf_maxb = (maxb == 0 && b == 0) ? 1 : maxb;
const conf_b = (maxb == 0 && b == 0) ? 1 : b;

const charta = new Chart($('#charta'), getConfig(maxa, a, 0, '활동 점수', '점'))
const chartp = new Chart($('#chartp'), getConfig(maxp, p, 60, '피아노 점수', '점'))
const chartj = new Chart($('#chartj'), getConfig(maxj, j, 100, '정모 출석', '회'))
const chartb = new Chart($('#chartb'), getConfig(conf_maxb, conf_b, 200, '동비 납부', '원'));

$('.bi-clipboard').click(function () {
    const acc_num = $('#account-num').html()

    if (navigator.clipboard) {
        navigator.clipboard.writeText(acc_num);
        alert_float('계좌번호가 복사되었습니다!', true);
    } else {
        alert_float('브라우저에서 복사 API를 지원하지 않습니다 :(', false);
    }
});