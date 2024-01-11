// fix info
let infochecklist = {'yr': true, 'maj': true, 'bday': true, 'phonenum': true};

let infocheckfunc = { 'yr':  val => ((val === '') ? true : /^[0-9]{2}$/.test(val)),
                        'maj': _ => true,
                        'by': verifyDate,
                        'bm': verifyDate,
                        'bd': verifyDate,
                        'phonenum': val => ((val === '') ? true : /^[0-9]{11}$/.test(val))
                };

let infoflag = false;
Object.keys(infocheckfunc).forEach((key) => {
    $('#info-' + key).on('input blur', function(){
        const verify = infocheckfunc[key]($(this).val());

        if(['by', 'bm', 'bd'].includes(key)) infochecklist['bday'] = verify;
        else infochecklist[key] = verify;

        if(verify){
            $(this).removeClass('invalid');
            validateInfo();
        } else {
            $(this).addClass('invalid');
            flag = false;
            $('#info-update-btn').attr('disabled', true);
        }
    });
});

function verifyDate(_){
    const y = $('#info-by').val(), m = $('#info-bm').val(), d = $('#info-bd').val();
    let result = true;

    if(y === '' && m === '' && d === '') result = true;
    else if (!y || !m || !d) result = false;
    else {
        let date = y + '-' + m + '-' + d;
        const dateObj = new Date(date);

        if((dateObj === "Invalid Date") || isNaN(dateObj)) result = false;
        else {
            const oy = dateObj.getFullYear(), om = dateObj.getMonth() + 1, od = dateObj.getDate();
            if(oy != y || om != m || od != d) result = false;
        }
    }

    if(result) $('#info-by, #info-bm, #info-bd').removeClass('invalid');
    else $('#info-by, #info-bm, #info-bd').addClass('invalid');
    
    return result; 
}

function validateInfo(){
    flag = true;
    for(key in infochecklist){
        flag = flag && infochecklist[key];
    }

    $('#info-update-btn').attr('disabled', !flag);
}

$('#info-update-btn').click(function(){
    let data = {};
    let fields = Object.keys(infochecklist);

    data['why'] = 'update_info';
    fields.forEach(tag => {
        if(tag == 'bday'){
            const y = $('#info-by').val(), m = $('#info-bm').val(), d = $('#info-bd').val();
            if(!y || !m || !d) data[tag] = '';
            else data[tag] =  y + '-' + m + '-' + d;
        }
        else data[tag] = $('#info-' + tag).val();
    });

    $.ajax({
        url: '../api/authquery.php',
        type: 'post',
        data: data,
        dataType: 'json',
    }).done(function(res){
        alert_float(res.message, res.success);
    });
});

// passwd update
$('#passwd-old, #passwd-new').on('input', function(){
    if($(this).val() === ''){
        $(this).addClass('invalid');
        $('#passwd-update-btn').attr('disabled', true);
    } else {
        $(this).removeClass('invalid');
        $('#passwd-update-btn').attr('disabled', ($('#passwd-old').val() === '' || $('#passwd-new').val() === ''));
    }    
});

$('#passwd-update-btn').click(function(){
    const oldpass = $('#passwd-old').val();
    const newpass = $('#passwd-new').val();

    $.ajax({
        url: '../api/authquery.php',
        type: 'post',
        data: {
            why: 'update_passwd',
            old: oldpass,
            new: newpass,
        },
        dataType: 'json',
    }).done(function(res){
        alert_float(res.message, res.success);
    });
});