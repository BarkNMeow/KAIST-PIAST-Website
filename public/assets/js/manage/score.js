let copystr = '';
let memory = {};
let searchcnt = 0;

let loading = false;

setTimeout(() => {getScoreTable();}, 300);
getAddTable();

// 활동 인정
$('#score-search-filter').on('change', getScoreTable);

$('#score-table-copy').click(function(){
    if(navigator.clipboard){
        navigator.clipboard.writeText(copystr);
        alert_float('내용이 복사되었습니다!', true);
    } else {
        alert_float('브라우저에서 복사 API를 지원하지 않습니다 :(', false);
    }
})


function getScoreTable(){
    const target = $('#score-table');
    const num = parseInt($('#score-search-filter').val());

    if(num == 1 || num == 2) $('#score-table-copy').show();
    else $('#score-table-copy').hide();

    const scrollX = target.scrollLeft();

    loading = true;
    setTimeout(() => { if(loading) $('#score-table-loading').show(); }, 300);

    $.ajax({
        url: '../api/managequery.php',
        type: 'post',
        data: {
            why: 'score_table_get',
            num: num,
        },
        dataType: 'json',
    }).done(function(res){
        loading = false;
        if(!res.success) alert_float(res.message, res.success);

        target.children('div:nth-child(n + 4)').remove();
        $('#score-table').append(res.content);
        $('#score-table-cnt').html(res.count);
        copystr = res.copystr;

        target.scrollLeft(scrollX);
        target.scrollTop(0);

        $('#score-table-loading').hide();
    });
}
        

// 점수 추가 / 수정
$('#scorelist-add').click(function(){
    memory = {};
    searchcnt = 0;
    $('#overlay-window > div').css('transform', 'translateX(0)');
    $('#addlist-title').html('부여 대상 선택');

    $('#addlist-controlall').prop('checked', false);

    $('#addlist-id').val('');

    $('#addlist').html('<div class="overlay-list-row text-grey"><div>검색 결과가 없습니다.</div><div></div></div>');
    
    $('#addinfo-type').attr('disabled', false);
    $('#addinfo-type').val(0);

    $('#addinfo-a-why').val(0);
    $('#addinfo-p-why').val(0);
    $('#addinfo-a-why').show();
    $('#addinfo-p-why').hide();
    $('#addinfo-tmi').val('');
    $('#addinfo-quantity').val('');

    $('#overlay-confirm').attr('disabled', true);

    $('#overlay').show();
});

$('#overlay-next').click(function(){
    $('#overlay-window > div').css('transform', 'translateX(calc(-100% - 2rem))');
    $('#addlist-title').html('부여 내용');
});

$('#overlay-prev').click(function(){
    $('#overlay-window > div').css('transform', 'translateX(0)');
    $('#addlist-title').html('부여 대상 선택');
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
    });
});

$('#addinfo-type').change(function(){
    if($(this).val() == '0'){
        $('#addinfo-a-why').show();
        $('#addinfo-p-why').hide();
    } else {
        $('#addinfo-p-why').show();
        $('#addinfo-a-why').hide();  
    }

    validateInput();
});

$('#addinfo-a-why, #addinfo-p-why').change(function(){
    const score = $(this).val();
    if(score != '0' && score != '1') $('#addinfo-quantity').val(1);
    else $('#addinfo-quantity').val('');

    validateInput();
});

$('#addinfo-tmi, #addinfo-quantity').on('input', validateInput);

$('#overlay-confirm').click(function(){
    const id = $('#addlist-id').val();
    const type = parseInt($('#addinfo-type').val());
    const quantity = parseInt($('#addinfo-quantity').val());

    let info = '';
    if(!type){
        info = $('#addinfo-a-why').val();
    } else {
        info = $('#addinfo-p-why').val();
    }

    const tmi = $('#addinfo-tmi').val();
    const data = JSON.stringify(memory);

    $.ajax({
        url: '../api/managequery.php',
        type: 'post',
        data: {
            why: 'score_add',
            id: id,
            type: type,
            quantity: quantity,
            info: info,
            tmi: tmi,
            data: data,
        },
        dataType: 'json',
    }).done(function(res){
        alert_float(res.message, res.success);
        if(res.success){
            getScoreTable();
            getAddTable();
            $('#overlay').hide();
        }
    });
})

function getAddlist(){
    $('#addlist-controlall').prop('checked', false);

    const id = $('#addlist-id').val();
    const nm = $('#addlist-search-input').val();

    $.ajax({
        url: '../api/managequery.php',
        type: 'post',
        data: {
            why: 'score_addlist_get',
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
                } else {
                    delete memory[email]; // 두 번 클릭 => 변동 X
                }
            });
        }
    });
}

function validateInput(){
    let flag = true;
    const type = $('#addinfo-type').val()

    let value;
    if(type == '0'){
        value = $('#addinfo-a-why').val();
    } else {
        value = $('#addinfo-p-why').val();
    }

    if(value == '0') flag = false;
    else if(value == '' && $('#addinfo-tmi').val() == '') flag = false;

    const scoreval = parseInt($('#addinfo-quantity').val());
    if(isNaN(scoreval) || scoreval == 0) flag = false;

    $('#overlay-confirm').attr('disabled', !flag);
}

// 점수 부여 내역
$('#add-type').change(getAddTable);

function getAddTable(){
    const type = parseInt($('#add-type').val());

    $.ajax({
        url: '../api/managequery.php',
        type: 'post',
        data: {
            why: 'add_table_get',
            type: type,
        },
        dataType: 'json',
    }).done(function(res){
        if(!res.success) alert_float(res.message);
        $('#scorelist').html(res.content);
    });
}

function removeScore(id){
    if(confirm('정말로 삭제하시겠습니까?')){
        $.ajax({
            url: '../api/managequery.php',
            type: 'post',
            data: {
                why: 'score_remove',
                id: id,
            },
            dataType: 'json',
        }).done(function(res){
            alert_float(res.message, res.success);
            if(res.success){
                getAddTable();
                getScoreTable();
            }
        });
    }
}

function modifyScore(id){
    $('#addlist-id').val(id);
    memory = {};
    getAddlist();

    $('#overlay-window > div').css('transform', 'translateX(0)');
    $('#addlist-controlall').prop('checked', false);
    $('#overlay-confirm').attr('disabled', false);

    $.ajax({
        url: '../api/managequery.php',
        type: 'post',
        data: {
            why: 'score_modify_get',
            id: id,
        },
        dataType: 'json',
    }).done(function(res){
        if(!res.success){
            alert_float(res.message);
            return;
        }

        $('#addinfo-type').val(res.type);
        $('#addinfo-type').attr('disabled', true);

        if(res.type == 0){
            $('#addinfo-a-why').val(res.info);
            $('#addinfo-p-why').val(0);
            $('#addinfo-a-why').show();
            $('#addinfo-p-why').hide();
        } else {
            $('#addinfo-a-why').val(0);
            $('#addinfo-p-why').val(res.info);
            $('#addinfo-a-why').hide();
            $('#addinfo-p-why').show();
        }

        $('#addinfo-tmi').val(res.tmi);
        $('#addinfo-quantity').val(res.quantity);

        $('#overlay').show();
    });
}