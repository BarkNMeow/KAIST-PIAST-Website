let page = 1, maxpage;
let loading = false;
let viewonly = true;

setTimeout(() => {getActivityQuery();}, 300);

$('#activity-table-mode-btn').click(function(){
    if(viewonly){
        $(this).html('<i class="bi bi-pencil-fill"></i> 수정 모드');
        viewonly = false;
    } else {
        $(this).html('<i class="bi bi-eye-fill"></i> 보기 모드');
        viewonly = true;
    }

    getActivityQuery(true);
})

$('#activity-search-filter').on('change', getActivityQuery);

$('#activity-search-btn').click(function(){
    $('#activity-table-page').val(1);
    getActivityQuery();
});

$('#activity-search-input').on('keyup', function(key){
    if(key.keyCode == 13) {
        $('#activity-table-page').val(1);
        $(this).blur();
        getActivityQuery();
    }
});

$('#activity-table-pp').on('change', getActivityQuery);

$('#activity-table-page').on('change', () => {addPage(0);});
$('#activity-table-page').on('keyup', function(key){
    if(key.keyCode == 13) {
        $(this).blur();
        addPage(0);
    }
});

function addPage(n){
    const target = $('#activity-table-page');
    let targetpage = page + n;

    if(targetpage < 1) targetpage = 1;
    if(targetpage > maxpage) targetpage = maxpage;
    target.val(targetpage);
    
    if(targetpage != page){
        page = targetpage;
        getActivityQuery();
    }
}

function getActivityQuery(keeppos = false){
    const pp = $('#activity-table-pp').val();
    const target = $('#activity-table');
    const search = $('#activity-search-input').val();
    const filter = $('#activity-search-filter').val();

    const scrollX = target.scrollLeft();

    loading = true;
    setTimeout(() => { if(loading) $('#activity-table-loading').show(); }, 300);

    $.ajax({
        url: '../api/managequery.php',
        type: 'post',
        data: {
            why: 'activity_query',
            search: search,
            p: page,
            pp: pp,
            filter: filter,
            viewonly: viewonly,
        },
        dataType: 'json',
    }).done(function(res){
        loading = false;

        if(!res.success)
            alert_float(res.message, res.success);

        $('#activity-table > div:nth-child(n+4)').remove();
        target.append(res.content);

        if(res.cnt == 0) maxpage = 1;
        else maxpage = Math.floor((res.cnt - 1) / pp) + 1;

        $('#activity-table-cnt').html(res.cnt);
        $('#activity-table-page-max').html(maxpage);
        
        if(!keeppos){
            target.scrollLeft(scrollX);
            target.scrollTop(0);
        }

        $('#activity-table-loading').hide();
    });
}

function modifyActivity(email, type, dom){
    let value;
    if(type <= 2) value = $(dom).val();
    else value = ($(dom).is(':checked') ? 1 : 0);

    $.ajax({
        url: '../api/managequery.php',
        type: 'post',
        data: {
            why: 'activity_modify',
            email: email,
            type: type,
            value: value,
        },
        dataType: 'json',
    }).done(function(res){
        alert_float(res.message, res.success);
        getActivityQuery(true);
    });
}