let page = 1, maxpage;
let loading = false;

setTimeout(() => {getAccountQuery();}, 300);

$('#account-search-filter').on('change', function(){
    $('#account-table-page').val(1);
    page = 1;
    getAccountQuery();
});

$('#account-search-btn').click(function(){
    $('#account-table-page').val(1);
    page = 1;
    getAccountQuery();
});

$('#account-search-input').on('keyup', function(key){
    if(key.keyCode == 13) {
        $(this).blur();
        $('#account-table-page').val(1);
        page = 1;
        getAccountQuery();
    }
});

$('#account-table-pp').on('change', function(){
    $('#account-table-page').val(1);
    page = 1;
    getAccountQuery();
});

$('#account-table-page').on('blur', function(){
    const targetpage = parseInt($('#account-table-page').val()); 
    addPage(targetpage - page);
});

$('#account-table-page').on('keyup', function(key){
    if(key.keyCode == 13) {
        $(this).blur();
    }
});

function addPage(n){
    const target = $('#account-table-page');
    let targetpage = page + n;

    console.log(targetpage);

    if(targetpage < 1) targetpage = 1;
    if(targetpage > maxpage) targetpage = maxpage;
    target.val(targetpage);
    
    if(targetpage != page){
        page = targetpage;
        getAccountQuery();
    }
}

function getAccountQuery(keeppos = false){
    const pp = $('#account-table-pp').val();
    const target = $('#account-table');
    const search = $('#account-search-input').val();
    const filter = $('#account-search-filter').val();

    const scrollX = target.scrollLeft();

    loading = true;
    setTimeout(() => { if(loading) $('#account-table-loading').show(); }, 300);

    $.ajax({
        url: '../api/managequery.php',
        type: 'post',
        data: {
            why: 'account_query',
            search: search,
            p: page,
            pp: pp,
            filter: filter,
        },
        dataType: 'json',
    }).done(function(res){
        loading = false;
        if(!res.success)
            alert_float(res.message, res.success);

        target.children('div:nth-child(n+4)').remove();
        target.append(res.content);

        if(res.cnt == 0) maxpage = 1;
        else maxpage = Math.floor((res.cnt - 1) / pp) + 1;

        $('#account-table-cnt').html(res.cnt);
        $('#account-table-page-max').html(maxpage);
        
        if(!keeppos){
            target.scrollLeft(scrollX);
            target.scrollTop(0);
        }
        $('#account-table-loading').hide();
    });
}

function updateAuthlvl(email, isAccepted){
    $.ajax({
        url: '../api/managequery.php',
        type: 'post',
        data: {
            why: 'update_authlvl',
            email: email,
            is_accepted: isAccepted,
        },
        dataType: 'json',
    }).done(function(res){
        alert_float(res.message, res.success);
        getAccountQuery(true);
    });
}

function removeWarning(email){
    $('#account-overlay').show();
    $('#account-delete-id').html(email);
    $('#account-delete-confirm').unbind().on('click', function(){ removeAccount(email); });
}

function removeAccount(email){
    const password = $('#delete-password').val();

    $.ajax({
        url: '../api/managequery.php',
        type: 'post',
        data: {
            why: 'remove_account',
            password: password,
            targetEmail: email,
        },
        dataType: 'json',
    }).done(function(res){
        $('#account-overlay').hide();
        $('#delete-password').val('');
        alert_float(res.message, res.success);
        getAccountQuery(true);
    });
}

// Exec starts here
const execname = ['회장', '부회장', '악장', '기획부장', '홍보부장', '총무'];

function showExecOverlay(n){
    $('#exec-code').val(n);
    $('#execlist-search-input').val('#all');
    $('#execlist-title').html(execname[n] + ' 변경')
    getExecQuery();

    $('#exec-overlay').show();
}

$('#execlist-search-btn').click(getExecQuery);
$('#execlist-all-btn').click(function(){
    $('#execlist-search-input').val('#all');
    getExecQuery();
})

$('#execlist-search-input').on('keyup', function(key){
    if(key.keyCode == 13) {
        getExecQuery();
        $(this).blur();
    }
});

function getExecQuery(){
    const name = $('#execlist-search-input').val();
    const code = $('#exec-code').val();
    if(name === '') return;

    $.ajax({
        url: '../api/managequery.php',
        type: 'post',
        data: {
            why: 'exec_query',
            nm: name,
            code: code,
        },
        dataType: 'json',
    }).done(function(res){
        $('#execlist').html(res.content);
        if(!res.success) alert_float(res.message, res.success);
    });
}

function modifyExec(email, code, add){
    if(confirm('정말 ' + (add ? '등록' : '삭제') + '하시겠습니까?')){
        $.ajax({
            url: '../api/managequery.php',
            type: 'post',
            data: {
                why: 'exec_modify',
                email: email,
                code: code,
                add: add,
            },
            dataType: 'json',
        }).done(function(res){
            alert_float(res.message, res.success);
            if(res.success)
                $('#exec-wrapper > div:nth-child(' + (code + 1) + ') > div:nth-child(2)').replaceWith(res.execlist);

            getExecQuery();
        });
    }
}