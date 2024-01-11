const x = '<i class="bi bi-x-lg"></i>';
const o = '<i class="bi bi-circle"></i>';
const tri = '<i class="bi bi-triangle"></i>';
const symbol = [x, tri, o];

let memory = {};
let timer = setInterval(updateJungmo, 60000);
let delta = 0;
let unsetlist = [];

let loading = false;
setTimeout(() => {getTable();}, 300);

let dropdownsize = parseInt($('#jungmo-table').css('height')) - 44;

$(window).bind('beforeunload', function(){
    return '정말로 나가시겠습니까?';
});

$(document).click(() => { $('.header-dropdown').hide(); $('.header-dropdown-btn').removeClass('selected'); });

$('#jungmo-update-btn').click(updateJungmo);
$('#jungmo-init-btn').click(getTable);

$('#jungmo-autosave').click(function(){
    const target = $(this);
    if(target.hasClass('auto-off')){
        target.removeClass('auto-off');
        target.html('자동 저장: 켜짐');
        timer = setInterval(updateJungmo, 60000);
    } else {
        target.addClass('auto-off');
        target.html('자동 저장: 꺼짐');
        clearInterval(timer);
    }
});

function toggleDropdown(dom, event){
    event.stopPropagation();

    const target = $(dom).next();
    if($(dom).hasClass('selected')){
        target.hide();
    } else {
        $('.header-dropdown').hide();
        $('.header-dropdown-btn').removeClass('selected');
        target.show();
    }

    $(dom).toggleClass('selected');
}

function getTable(){
    const target = $('#jungmo-table');

    loading = true;
    setTimeout(() => { if(loading) $('#jungmo-table-loading').show(); }, 300);

    $.ajax({
        url: '../api/jungmoquery.php',
        type: 'post',
        data: {
            why: 'jungmo_table',
        },
        dataType: 'json',
    }).done(function(res){
        loading = false;
        if(!res.success)
            alert_float(res.message, res.success);

        target.children('div:nth-child(n+4)').remove();
        target.append(res.content);
        $('#jungmo-table-header').html(res.header);
        
        $('#jungmo-table-loading').hide();
        $('#jungmo-table-cnt').html(res.cnt);
        $('#jungmo-update-btn').attr('disabled', true);

        memory = {};
        delta = 0;
        unsetlist = [];
    });
}

function addClick(dom, id, inc = 1){
    const target = $(dom);
    const value = parseInt(target.attr('data-value'));
    const email = target.parent().attr('data-email');
    const newvalue = (value & 4) + ((value & 3) + inc) % 3;

    if(value < 4){
        target.removeClass();
        switch(newvalue){
            case 0:
                target.addClass('cell-none');
                break;
            case 1:
                target.addClass('cell-half');            
                break;
            case 2:
                target.addClass('cell-full');
                break;
        }
    }

    $(dom).html(symbol[newvalue & 3])
    target.attr('data-value', newvalue);

    if(!memory[email]) memory[email] = {};

    if(!memory[email][id]){
        memory[email][id] = 0;
        delta += 1;

        if(target.attr('data-unset')){
            unsetlist.push(target);
        }
    }

    memory[email][id] = (memory[email][id] + inc) % 3;

    if(memory[email][id] == 0){
        if(!target.attr('data-unset')){
            delete memory[email][id];
            delta -= 1;
        }
    }

    $('#jungmo-update-btn').attr('disabled', !delta);
}

function setColumn(id, colnum, value){
    if(confirm('정말 모두 ' + (value ? 'O' : 'X')+ '로 설정하시겠습니까?')){
        const btnlist = $('#jungmo-table .table-row > div:nth-child(' + colnum + ')');
        btnlist.each(function(){
            const valuenow = parseInt($(this).attr('data-value'));
            const needinc = ((value - (valuenow & 3)) + 6) % 3;

            addClick(this, id, needinc);
        });
    }
}

function updateJungmo(){
    if(delta == 0) return;
    const data = JSON.stringify(memory);
    $.ajax({
        url: '../api/jungmoquery.php',
        type: 'post',
        data: {
            why: 'update_jungmo_chk',
            data: data,
        },
        dataType: 'json',
    }).done(function(res){
        alert_float(res.message, res.success);
        if(res.success){
            $('#jungmo-update-btn').attr('disabled', true);

            unsetlist.forEach(element => {
                element.removeAttr('data-unset');
            });

            memory = {};
            delta = 0;
            unsetlist = [];
        }
    });
}