// Score Chart
let totalscore = 0;
let labelcnt = labels.length;
let isbar = (window.innerWidth < 768);

let bgcolor = [];
let bordercolor = [];
let hue = 0;
const huestep = 360 / (labelcnt - 1)

for(let i = 0; i < labelcnt; i++){
    totalscore += data[i];
    if(i == labelcnt - 1) break;

    bgcolor.push('hsla(' + hue + ', 100%, 70%, 0.5)');
    bordercolor.push('hsl(' + hue + ', 100%, 70%)');
    hue += huestep;
}

bgcolor.push('hsla(0, 0%, 40%, 0.5)');
bordercolor.push('hsl(0, 0%, 40%)');

let config = {
    type: (window.innerWidth > 768 ? 'bar' : 'doughnut'),
    data: {
        datasets: [{
            data: [1]
        }]
    },
    options: {
        maintainAspectRatio: false,
    }
}

function barConfig(chart){
    if(isbar) return;

    let chartdata = [];
    for(i in labels){
        chartdata.push({label: labels[i], data: [data[i]], backgroundColor: bgcolor[i], borderColor: bordercolor[i]})
    }

    chart.config.type = 'bar';
    chart.data = { labels: [1], datasets: chartdata };

    chart.options = {
        maintainAspectRatio: false,
        borderWidth: 2,
        borderSkipped: false,
        indexAxis: 'y',
        barPercentage: 0.9,
        scales: {y: {stacked: true,
                    display: false, 
                }, 
                x: {stacked: true,
                    display: true,
                    max: totalscore,
                    ticks: {
                        stepSize: (totalscore < 10 ? 1 : (totalscore < 100 ? 5 : 10)),
                        callback: (val, index, ticks) => (val == totalscore ? '' : val),
                    },
                },
            },
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: context => ' ' + context.raw + '점',
                    title: context => context[0].dataset.label,
                }
            }
        },
    }

    chart.update('none');
}

function doughnutConfig(chart){
    if(!isbar) return;

    chart.config.type = 'doughnut';
    chart.data = {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: bordercolor,
                    }]
                };

    chart.options = {
        maintainAspectRatio: false,
        borderWidth: 2,
        borderSkipped: false,
        indexAxis: 'y',
        barPercentage: 0.9,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: context => ' ' + context.raw + '점',
                    title: context => context[0].label,
                }
            }
        },
    }

    chart.update('none');
}

Chart.defaults.font = {
    family: 'Noto Sans KR',
    size: 14,
}

if($('#chart').length){
    const chart = new Chart($('#chart'), config);
    window.onresize = () => {
        if(window.innerWidth > 768) barConfig(chart);
        else doughnutConfig(chart);
    
        isbar = (window.innerWidth > 768);
    }
    
    window.onresize();
}


// name fix
$('#name-fix-btn').click(function(){
    $('#name-span').hide();
    $('#name-fix-btn').hide();
    $('#name-fix-input').show();
    $('#name-fix-input').val($('#name-span').html());
    $('#name-fix-input').focus();
})

$('#name-fix-input').on('keyup', function(key){
    if(key.keyCode == 13) $(this).blur();
});

$('#name-fix-input').on('blur input', function(){
    if($(this).val() === ''){
        $(this).addClass('invalid');
    } else {
        $(this).removeClass('invalid');
    }
});

$('#name-fix-input').on('blur', function(){
    const nm = $(this).val();
    if(nm === '') return;


    $.ajax({
        url: '../api/amityquery.php',
        type: 'post',
        data: {
            why: 'groupnm_change',
            nm: nm,
        },
        dataType: 'json',
    }).done(function(res){
        alert_float(res.message, res.success)
        if(res.success){
            $('#name-span').html(nm);
            $('#name-span').show();
            $('#name-fix-btn').show();
            $('#name-fix-input').hide();
        }
    });
});