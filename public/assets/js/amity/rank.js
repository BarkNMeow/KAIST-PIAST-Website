let calendarvisible = false;

function showGraphTab(n){
    $('#graph-wrapper > div').hide();
    $('#btn-wrapper > button').removeClass('selected');
    $('#graph-wrapper > div:nth-child(' + n + ')').show();
    $('#btn-wrapper > button:nth-child(' + n + ')').addClass('selected');

    calendarvisible = (n == 2);
}

// Draw Bar Graph
let totalscore = 0;
let labelcnt = label.length;

let bgcolor = [];
let bordercolor = [];
let hue = 0;
const huestep = 360 / (labelcnt - 1)

const chartgap = 9;

for(let i = 0; i < labelcnt; i++){
    if(i == labelcnt - 1) break;

    bgcolor.push('hsla(' + hue + ', 100%, 70%, 0.7)');
    bordercolor.push('hsl(' + hue + ', 70%, 50%)');
    hue += huestep;
}

bgcolor.push('hsla(0, 0%, 60%, 0.5)');
bordercolor.push('hsl(0, 0%, 60%)');

Chart.defaults.font = {
    family: 'Noto Sans KR',
    size: 14,
}

let datasets = []
let grouplabel = []
let groupscore = []

for(i in grouplist){
    grouplabel.push(grouplist[i].nm)
    groupscore.push(0)
}

for(l in label){
    let set = {};
    set.data = [];

    for(i in grouplist){
        set.data.push(bardata[grouplist[i].id][l])
        groupscore[i] += bardata[grouplist[i].id][l]
    }

    set.label = label[l];
    set.backgroundColor = bgcolor[l];
    set.borderColor = bordercolor[l];
    datasets.push(set);
}

function drawDataLabel(chart, factor){
    let ctx = chart.ctx;

    ctx.textBaseline = 'middle';
    ctx.font = 'medium 19px Noto Sans KR';

    let barlist = chart.getDatasetMeta(1).data
    const chartwidth = chart.chartArea.width

    // console.log(chartwidth)

    for(i in barlist){
        const bar = barlist[i];
        const barwidth = chartwidth * (groupscore[i] / groupscore[0]);

        const nmMetric = ctx.measureText(grouplist[i].nm)
        const scoreMetric = ctx.measureText(groupscore[i] + '점')
        const labelMetric = Math.max(nmMetric.width, scoreMetric.width)
        let xpos;

        // console.log(grouplist[i].nm, barwidth)
        
        if(barwidth + chartgap + labelMetric > chartwidth){
            ctx.textAlign = 'right';
            // ctx.fillStyle = 'white';
            xpos = barwidth * factor - chartgap + 8;
        } else {
            ctx.textAlign = 'left';
            // ctx.fillStyle = 'black';
            xpos = (barwidth + chartgap) * factor + 8;
        }

        ctx.fillText(grouplist[i].nm, xpos, bar.y + 0.5 - 11)
        ctx.fillText(groupscore[i] + '점', xpos, bar.y + 0.5 + 11)
    }
}

let barconfig = {
    type: 'bar',
    data: {
        labels: grouplabel,
        datasets: datasets
    },
    options: {
        borderWidth: 2,
        borderSkipped: false,
        barPercentage: 0.9,
        indexAxis: 'y',
        maintainAspectRatio: false,
        scales: {y: {
                    stacked: true,
                    display: false,
                    ticks: {
                        font: {
                            size: 16,
                            weight: 500,
                        }
                    },
                    }, 
                x: {stacked: true,
                    max: groupscore[0],
                }
        },
        plugins: {
            id: '1',
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: context => ' ' + context.raw + '점',
                    title: context => context[0].label + ' (' + context[0].dataset.label + ')',
                }
            },
        },
        animationdone: false,
        animation: {
            onProgress: function(animation) {
                if(animation.chart.options.animationdone) return;

                let t = (animation.currentStep / animation.numSteps) - 1;
                const factor = (animation.initial ? - (t*t*t*t - 1) : 1);
                drawDataLabel(animation.chart, factor)

                if(!animation.initial) animation.chart.options.animationdone = true;
            }
        }
    },
    plugins: [{
        beforeTooltipDraw: (chart, args, options) => {
            if(!chart.options.animationdone) return;
            drawDataLabel(chart, 1)
        }
    }]
}

if(grouplist.length){
    let barchart = new Chart($('#barchart'), barconfig);
} 


// Nalgang Calendar
let time = new Date();
let year = time.getFullYear();
let month = time.getMonth() + 1;
let prevyear = year, prevmonth = month;

let previd = 0;

loadCalendar();
$('#calendar-month-inc').click(() => changeMonth(1));
$('#calendar-month-dec').click(() => changeMonth(-1));

$(document).on('mouseover', function(e){
    if(!calendarvisible) return;
    let target = $(e.target);

    console.log('trigerrrred');

    if(previd){
        $('.calendar-legend[name=' + previd + ']').removeClass('selected group' + previd + ' op');
        $('.calendar-group-container.group' + previd).addClass('op');
    }

    let flag = (target.hasClass('calendar-group-container') || target.hasClass('calendar-legend'));
    if(target.parent().hasClass('calendar-legend')){
        target = target.parent();
        flag = true;
    }

    if(flag){
        const id = target.attr('data-id');
        previd = id;

        $('.calendar-legend[name=' + id + ']').addClass('selected group' + id + ' op');
        $('.calendar-group-container.group' + id).removeClass('op');

    } else {
        previd = 0;
    }
})

function loadCalendar(){
    $.ajax({
        url: '../api/amityquery.php',
        type: 'post',
        data: {
            why: 'calendar_get',
            year: year,
            month: month,
        },
        dataType: 'json',
    }).done(function(res){
        if(res.success){
            $('#calendar').html(res.content);
            $('#calendar-year').html(year + '년');
            $('#calendar-month-dec').html('<i class="bi-chevron-compact-left bi"></i>' + (((month - 2 + 12) % 12) + 1) + '월');
            $('#calendar-month').html(month + '월');
            $('#calendar-month-inc').html((((month + 12) % 12) + 1) + '월' + '<i class="bi-chevron-compact-right bi"></i>');

            prevyear = year;
            prevmonth = month;
        } else {
            year = prevyear;
            month = prevmonth;
            alert_float(res.message)
        }
    });
}

function changeMonth(n){
    month += n;
    if(month == 0){
        year--;
        month = 12;
    } else if(month == 13){
        year++;
        month = 1;
    }

    loadCalendar();
}