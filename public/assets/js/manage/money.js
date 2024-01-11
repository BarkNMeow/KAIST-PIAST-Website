let loading = false;
let viewonly = 1;

setTimeout(() => {getMoneyTable();}, 300);
getMoneyList();

$('#money-search-filter').change(getMoneyTable);

$('#money-table-mode-btn').click(function(){
    if(viewonly){
        $(this).html('<i class="bi bi-pencil-fill"></i> 수정 모드');
        viewonly = 0;
    } else {
        $(this).html('<i class="bi bi-eye-fill"></i> 보기 모드');
        viewonly = 1;
    }

    getMoneyTable(true);
})

function chkblur(event){
    if(event.key == 'Enter'){
        $(event.target).blur();
    }
}

function getMoneyTable(keeppos = false){
    const target = $('#money-table');
    const option = parseInt($('#money-search-filter').val());
    const scrollX = target.scrollLeft();

    loading = true;
    setTimeout(() => { if(loading) $('#money-table-loading').show(); }, 300);

    $.ajax({
        url: '../api/moneyquery.php',
        type: 'post',
        data: {
            why: 'money_table_get',
            option: option,
            viewonly: viewonly,
        },
        dataType: 'json',
    }).done(function(res){
        loading = false;
        if(!res.success) alert_float(res.message, res.success);

        target.children('div:nth-child(n + 4)').remove();
        target.append(res.content);
        $('#money-table-cnt').html(res.count);

        if(!keeppos){
            target.scrollLeft(scrollX);
            target.scrollTop(0);
        }

        $('#money-table-loading').hide();
    });
}

function setVisible(email, val){
    $.ajax({
        url: '../api/moneyquery.php',
        type: 'post',
        data: {
            why: 'set_visible',
            email: email,
            val: val,
        },
        dataType: 'json',
    }).done(function(res){
        alert_float(res.message, res.success);
        getMoneyTable(true);
    });
}

function setPaid(email, val, typeidx){
    const type = ['due', 'finebill'];
    const parsed = parseInt(val);
    let value;

    if(isNaN(parsed)){
        const me = $(val);

        if(me.val() === '') value = 0;
        else value = parseInt(me.val());

        if(value == parseInt(me.attr('data-original'))) return;

    } else {
        value = parsed;
    }

    $.ajax({
        url: '../api/moneyquery.php',
        type: 'post',
        data: {
            why: 'set_' + type[typeidx] + 'paid',
            email: email,
            val: value,
        },
        dataType: 'json',
    }).done(function(res){
        alert_float(res.message, res.success);
        getMoneyTable(true);
    });
}

function setDuepaidDate(email, dom){
    const val = $(dom).val();

    if(val == $(dom).attr('data-original')) return;

    $.ajax({
        url: '../api/moneyquery.php',
        type: 'post',
        data: {
            why: 'set_duepaiddate',
            email: email,
            val: val,
        },
        dataType: 'json',
    }).done(function(res){
        alert_float(res.message, res.success);
        getMoneyTable(true);
    });
}

// Table Setting starts here
$('#setting-confirm-btn').click(function(){
    const due = $('#setting-due').val();
    const accountnum = $('#setting-accountnum').val();

    $.ajax({
        url: '../api/moneyquery.php',
        type: 'post',
        data: {
            why: 'money_setting',
            due: due,
            accountnum: accountnum,
        },
        dataType: 'json',
    }).done(function(res){
        if(res.success) {
            alert_float(res.message, res.success);
            $('#setting-overlay').hide();
            getMoneyTable(true);
        } else {
            alert_float(res.message);
        }
    });
})

$('#setting-init-btn').click(function(){
    if(confirm('정말 초기화하시겠습니까?')){
        $.ajax({
            url: '../api/moneyquery.php',
            type: 'post',
            data: {
                why: 'money_init',
            },
            dataType: 'json',
        }).done(function(res){
            alert_float(res.message, res.success);
            if(res.success) {
                getMoneyTable(true);
                $('#setting-due').val(0);
                $('#setting-overlay').hide();
            }
        });
    }
})

// Addlist starts here
let memory = {};
let searchcnt = 0;
let checkcnt = 0;

let checklist = {'date': false, 'tmi': false, 'peoplecnt': false, 'totalmoney': false, 'money': false};
let checkfunc = {'date': val => (val !== ''),
                 'tmi': val => (val !== ''),
                 'peoplecnt': val => (!isNaN(val) && val.search(/[\+|-|\.]/) < 0 && parseInt(val) > 0),
                 'totalmoney': val => (!isNaN(val) && val.search(/[\+|-|\.]/) < 0 && parseInt(val) > 0),
                 'money': val => (!isNaN(val) && val.search(/[\+|-|\.]/) < 0 && parseInt(val) > 0),
                };

let flag = false;

function getMoneyList(){
    $.ajax({
        url: '../api/moneyquery.php',
        type: 'post',
        data: {
            why: 'moneylist_get',
        },
        dataType: 'json',
    }).done(function(res){
        if(!res.success) alert_float(res.message);
        $('#moneylist').html(res.content);
        $('#moneylist-total').html(res.total);
    });
}

$('#moneylist-add').click(function(){
    memory = {};
    searchcnt = checkcnt = 0;
    for(key in checklist) checklist[key] = false;

    $('#addlist-overlay-window > div').css('transform', 'translateX(0)');
    $('#addlist-title').html('정산 대상');

    $('#addlist-controlall').prop('checked', false);

    $('#addlist-id').val('');
    $('#addlist-search-input').val('');
    $('#addlist').html('<div class="overlay-list-row text-grey"><div>검색 결과가 없습니다.</div><div></div></div>');
    
    $('#addinfo-date').val('');
    $('#addinfo-tmi').val('');
    $('#addinfo-peoplecnt').val(0);
    $('#addinfo-totalmoney').val(0);
    $('#addinfo-money').val(0);

    $('#addlist-overlay-confirm').attr('disabled', true);
    $('#addlist-overlay').show();
});

$('#addlist-overlay-next').click(function(){
    $('#addlist-overlay-window > div').css('transform', 'translateX(calc(-100% - 2rem))');
    $('#addlist-title').html('정산 금액');
});

$('#addlist-overlay-prev').click(function(){
    $('#addlist-overlay-window > div').css('transform', 'translateX(0)');
    $('#addlist-title').html('정산 대상');
});

$('#addlist-search-btn').click(getAddlist);

$('#addlist-all-btn').click(function(){
    $('#addlist-search-input').val('#all');
    getAddlist();
});

$('#addlist-full-btn').click(function(){
    $('#addlist-search-input').val('#full');
    getAddlist();
});

$('#addlist-search-input').on('keyup', function(key){
    if(key.keyCode == 13) {
        getAddlist();
        $(this).blur();
    }
});

$('#addlist-controlall').change(function(){
    if(searchcnt == 0) return;
    const checked = $(this).is(':checked');

    $('#addlist > div').each(function(){
        const checkbox = $(this).find('input');
        const email = checkbox.attr('data-email');

        if(memory[email]){
            if(memory[email]['checked'] == checked) return;
            else delete memory[email];
        } else {
            const thischecked = checkbox.is(':checked');
            if(thischecked == checked) return;
            else {
                const gennm = $(this).children('div:first-child').html(); 
                memory[email] = {'gennm': gennm, 'checked': checked};
            }
        }

        checkbox.prop('checked', checked);
        checkcnt += (checked ? 1 : -1);
    });

    $('#addinfo-peoplecnt').val(checkcnt);
    $('#addinfo-peoplecnt').trigger('input');
});

Object.keys(checkfunc).forEach((key) => {
    $('#addinfo-' + key).on('input blur', function(){
        const verify = checkfunc[key]($(this).val());
        checklist[key] = verify;

        if(verify){
            $(this).removeClass('invalid');

            flag = true;
            for(k in checklist){
                flag &&= checklist[k];
            }

            $('#addlist-overlay-confirm').attr('disabled', !flag);

        } else {
            $(this).addClass('invalid');
            flag = false;
            $('#addlist-overlay-confirm').attr('disabled', true);
        }
    });
});

$('#addinfo-peoplecnt, #addinfo-totalmoney').on('input', function(){
    const peoplecnt = parseInt($('#addinfo-peoplecnt').val());
    const totalmoney = parseInt($('#addinfo-totalmoney').val());

    let value;
    if(isNaN(peoplecnt) || peoplecnt == 0 || isNaN(totalmoney) || totalmoney == 0) value = 0;
    else value = Math.floor(totalmoney / peoplecnt);

    $('#addinfo-money').val(value);
    
    const myid = $(this).attr('id');
    const targetlist = ['peoplecnt', 'totalmoney', 'money'];
    for(key in targetlist){
        if('addinfo-' + targetlist[key] !== myid){
            $('#addinfo-' + targetlist[key]).trigger('blur');
        }
    }
});

$('#addlist-overlay-confirm').click(function(){
    let data = {};
    data['why'] = 'money_add';
    data['id'] = $('#addlist-id').val();

    for(key in checklist) data[key] = $('#addinfo-' + key).val();

    data['data'] = JSON.stringify(memory);

    $.ajax({
        url: '../api/moneyquery.php',
        type: 'post',
        data: data,
        dataType: 'json',
    }).done(function(res){
        alert_float(res.message, res.success);
        if(res.success){
            getMoneyTable();
            getMoneyList();
            $('#addlist-overlay').hide();
        }
    });
});

function getAddlist(){
    $('#addlist-controlall').prop('checked', false);
    const id = $('#addlist-id').val();
    const nm = $('#addlist-search-input').val();

    $.ajax({
        url: '../api/moneyquery.php',
        type: 'post',
        data: {
            why: 'money_addlist_get',
            nm: nm,
            id: id,
        },
        dataType: 'json',
    }).done(function(res){
        if(!res.success) 
            alert_float(res.message);
        else {
            const data = res.data;
            for(email in memory){
                if(data[email]){
                    data[email]['checked'] = memory[email]['checked'];
                } else if(nm == '#all') {
                    data[email] = {};
                    data[email]['gennm'] = memory[email]['gennm'];
                    data[email]['checked'] = memory[email]['checked'];
                }
            }

            const target = $('#addlist');
            target.html('');
            searchcnt = Object.keys(data).length;

            if(searchcnt == 0){
                target.append('<div class="overlay-list-row text-grey"><div>검색 결과가 없습니다.</div><div></div></div>');
                return;
            }

            for(email in data){
                const checked = data[email]['checked'] ? ' checked' : '';
                target.append('<div class="overlay-list-row"><div>' + data[email]['gennm'] + '</div><div><input type="checkbox" data-email="' + email + '"' + checked + '></div></div>');
            }

            $('#addlist input').click(function(){
                const email = $(this).attr('data-email');
                if(!memory[email]){
                    memory[email] = {};
                    memory[email]['gennm'] = $(this).parent().parent().children('div:first-child').html();
                    memory[email]['checked'] = $(this).is(':checked');
                    checkcnt += (memory[email]['checked'] ? 1 : -1);

                } else {
                    checkcnt += (memory[email]['checked'] ? -1 : 1);
                    delete memory[email]; // 두 번 클릭 => 변동 X
                }

                $('#addinfo-peoplecnt').val(checkcnt);
                $('#addinfo-peoplecnt').trigger('input');
            });
        }
    });
}

function modifyMoney(id){
    $('#addlist-id').val(id);
    memory = {};
    for(key in checklist) checklist[key] = true;

    getAddlist();

    $('#addlist-overlay-window > div').css('transform', 'translateX(0)');
    $('#addlist-controlall').prop('checked', false);
    $('#addlist-overlay-confirm').attr('disabled', false);

    $.ajax({
        url: '../api/moneyquery.php',
        type: 'post',
        data: {
            why: 'money_modify_get',
            id: id,
        },
        dataType: 'json',
    }).done(function(res){
        if(!res.success){
            alert_float(res.message);
            return;
        }

        $('#addinfo-date').val(res.date);
        $('#addinfo-tmi').val(res.tmi);
        $('#addinfo-peoplecnt').val(res.peoplecnt);
        $('#addinfo-totalmoney').val(res.totalmoney);
        $('#addinfo-money').val(res.money);

        checkcnt = parseInt(res.peoplecnt);

        $('#addlist-overlay').show();
    });
}

function removeMoney(id){
    if(confirm('정말로 삭제하시겠습니까?')){
        $.ajax({
            url: '../api/moneyquery.php',
            type: 'post',
            data: {
                why: 'money_remove',
                id: id,
            },
            dataType: 'json',
        }).done(function(res){
            alert_float(res.message, res.success);
            if(res.success){
                getMoneyTable();
                getMoneyList();
            }
        });
    }
}
