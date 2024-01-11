let scoreBoard = null;
let transform = 0;
let mintransform = $('.tag-btn-window').width() - $('.tag-btn-container')[0].scrollWidth;
mintransform = mintransform > 0 ? 0 : mintransform;



window.onresize = function(){
    mintransform = $('.tag-btn-window').width() - $('.tag-btn-container')[0].scrollWidth;
    mintransform = mintransform > 0 ? 0 : mintransform;
}

// Add Score
let checklist = {'date': false, 'type': false, 'desc': true, 'score': false};
let checkfunc = {'date': val => (val !== ''),
                 'type': val => (val !== ''),
                 'desc': val => ($('#addinfo-type').val() != '-1' || val !== ''),
                 'score': val => (!isNaN(val) && val.search(/[\+|\.]/) < 0 && (parseInt(val) > 0 || parseInt(val) < 0)),
                };

let grouplist = new Set();
let searchcnt = 0;
let flag = false;

function showAddlist(){
    checklist = {'date': false, 'type': false, 'desc': true, 'score': false};
    grouplist = new Set();
    flag = false;

    $('#addlist-title').html('친목조 선택');
    $('#addlist-overlay-window > div').css('transform', 'translateX(0)');
    
    $('#addlist-controlall').prop('checked', false);
    $('#addlist-search-input').val('#all');

    $('#addlist-overlay input, #addlist-overlay select').removeClass('invalid');

    $('#addinfo-date').val('');
    $('#addinfo-type').val('');
    $('#addinfo-desc').val('');
    $('#addinfo-score').val('');
    $('#addlist-overlay-confirm').attr('disabled', true);

    getAddlist();
    $('#addlist-overlay').show();
}

$('#addlist-overlay-next').click(function(){
    $('#addlist-overlay-window > div').css('transform', 'translateX(calc(-100% - 2rem))');
    $('#addlist-title').html('친목점수 부여');
});

$('#addlist-overlay-prev').click(function(){
    $('#addlist-overlay-window > div').css('transform', 'translateX(0)');
    $('#addlist-title').html('친목조 선택');
});

$('#addlist-search-btn').click(getAddlist);

$('#addlist-all-btn').click(function(){
    $('#addlist-search-input').val('#all');
    getAddlist();
});

$('#addlist-search-input').on('keyup', function(key){
    if(key.keyCode == 13) {
        getAddlist();
        $(this).blur();
    }
});

$('#addlist-controlall').change(function(){
    const checked = $(this).is(':checked');
    if(searchcnt == 0) return;

    $('#addlist > div').each(function(){
        const checkbox = $(this).find('input');
        const id = checkbox.attr('data-id');
        const thischecked = checkbox.is(':checked');

        if(checked != thischecked){
            if(checked) grouplist.add(id);
            else grouplist.delete(id);
        }

        checkbox.prop('checked', checked);
    });

    verifyInput();
});

function getAddlist(){
    $('#addlist-controlall').prop('checked', false);
    const nm = $('#addlist-search-input').val();

    $.ajax({
        url: '../api/amityquery.php',
        type: 'post',
        data: {
            why: 'amity_addlist_get',
            nm: nm,
        },
        dataType: 'json',
    }).done(function(res){
        if(!res.success) 
            alert_float(res.message);
        else {
            const data = res.data;
            const target = $('#addlist');
            target.empty();
            searchcnt = Object.keys(data).length;

            if(searchcnt == 0){
                target.append('<div class="overlay-list-row text-grey"><div>검색 결과가 없습니다.</div><div></div></div>');
                return;
            }

            for(id in data){
                const checked = grouplist.has(data[id]['groupid']) ? ' checked' : '';
                target.append('<div class="overlay-list-row"><div>' + data[id]['groupnm'] + ' <span>(' +  data[id]['gennm'] + ')</span></div><div><input type="checkbox" data-id="' + data[id]['groupid'] + '"' + checked + '></div></div>');
            }

            $('#addlist input').click(function(){
                const id = $(this).attr('data-id');
                const checked = $(this).is(':checked');
                $('#addlist input[data-id="' + id + '"]').prop('checked', checked);

                if(checked){
                    grouplist.add(id);
                } else {
                    grouplist.delete(id);
                }

                verifyInput();
            });
        }
    });
}

Object.keys(checkfunc).forEach((key) => {
    $('#addinfo-' + key).on('input blur', function(){
        let verify = checkfunc[key]($(this).val());
        checklist[key] = verify;

        if(checklist[key]) $(this).removeClass('invalid');
        else $(this).addClass('invalid');

        if(key == 'type') {
            const target = $('#addinfo-desc');
            checklist['desc'] = checkfunc['desc'](target.val());
            verify &&= checklist['desc'];

            if(checklist['desc']) target.removeClass('invalid');
            else target.addClass('invalid');
        }

        if(verify){
            verifyInput();
        } else {
            flag = false;
            $('#addlist-overlay-confirm').attr('disabled', true);
        }
    });
});

function verifyInput(){
    flag = true;
    for(k in checklist){
        flag &&= checklist[k];
    }

    flag &&= (grouplist.size > 0);

    $('#addlist-overlay-confirm').attr('disabled', !flag);
}

$('#addlist-overlay-confirm').click(function(){
    let data = {};
    data['why'] = 'activity_give';
    data['id'] = $('#addlist-id').val();

    for(key in checklist) data[key] = $('#addinfo-' + key).val();

    data['group'] = JSON.stringify(Array.from(grouplist));

    $.ajax({
        url: '../api/amityquery.php',
        type: 'post',
        data: data,
        dataType: 'json',
    }).done(function(res){
        alert_float(res.message, res.success);
        if(res.success){
            window.location.reload();
        }
    });
})

// List
$('#page-select').on('change', function(){
    $('#page').val(0);
    loadBoard();
});

$('.btn-tag').click(function(){
    const name = $(this).attr('name');
    if(name == 'all'){
        $('.btn-tag[name!=all]').removeClass('selected');
        $(this).addClass('selected');
    } else {
        const original = $(this).hasClass('selected');
        $('.btn-tag[name=all]').removeClass('selected');

        if(name == 't' || name == 'a'){
            $('.btn-tag[name=a]').removeClass('selected');
            $('.btn-tag[name=t]').removeClass('selected');
        } else if(name == 'g'){
            $('.btn-tag[name=g]').removeClass('selected');
        }

        if(original) $(this).removeClass('selected');
        else $(this).addClass('selected');
    }

    loadBoard();
});

$('.btn-move-window').click(function(){
    const isRight = $(this).attr('name') == 'right';
    if(isRight){
        transform = (transform - 200 > mintransform ? transform - 200 : mintransform);
        $('.left-hide').show();
    } else {
        transform = (transform + 200 < 0 ? transform + 200 : 0);
        $('.right-hide').show();
    }

    if(transform >= 0) $('.left-hide').hide();
    if(transform <= mintransform) $('.right-hide').hide();
    $('.tag-btn-container').css('transform', 'translateX(' + transform + 'px)');
}); 

$('.bi-trash-fill').click(function(){
    if(confirm('정말 삭제하시겠습니까?')){
        const id = $(this).attr('name');
        $.ajax({
            url: '../api/amityquery.php',
            type: 'post',
            data: {
                why: 'activity_delete',
                id: id,
            },
            dataType: 'json',
        }).done(function(res){
            alert(res.message)
            if(res.success) window.location.reload();
        });
    }
});

function loadBoard(){
    const page = $('#page').val();
    const perpage = $('#page-select').val();

    let query = '';
    let name = ['g', 'a', 't'];
    for(let i = 0; i < name.length; i++){
        let char = name[i];
        $('.btn-tag[name=' + char + ']').each(function(){
            if($(this).hasClass('selected')){
                query = query + '&' + char + '=' + $(this).val();
            }
        });
    }

    if($('.btn-tag[name=all]').hasClass('selected')){
        query = '';
    }

    window.location.href = "activity?&p=" + page + "&pp=" + perpage + query;
}

function setpage(n){
    $('#page').val(n);
    loadBoard();
}
