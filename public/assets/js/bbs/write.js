const originalHtml = $('.submit-wrapper button').html();
let tagcnt = $('#tag').val().split(' ').length
let tagstrlen = $('#tag').val().length;

let editor = quillEditor('#quill');
getFileList();

$('#bbs-type').on('input', checkValid);
$('#bbs-title').on('input', checkValid);
$('#bbs-file').on('change', getFileList);

$('#bbs-type, #bbs-title').on('blur input', function(){
    const me = $(this);
    if(me.val()) me.removeClass('invalid');
    else me.addClass('invalid');
});

$('#bbs-tag').on('keyup', function(key){
    if(key.keyCode == 13 || key.keyCode == 32){
        addTag();
    }

    if(key.keyCode == 8){
        if($('#bbs-tag').val() === '' && tagcnt){
            const lasttag = $('#bbs-tag-fake > button:nth-last-child(2)');
            $('#bbs-tag').val(lasttag.attr('data-tag'));
            removeTag(lasttag);
        }
    }
});

function addTag(){
    const target = $('#bbs-tag');
    const tagval = target.val().replaceAll(' ', '');

    if(tagval == '') {
        target.val('');
        return;
    }
    
    if(tagstrlen > 0) tagstrlen++;
    tagstrlen += tagval.length;

    tagcnt++;
    target.attr('placeholder', '태그 입력');
    target.attr('maxlength', 40 - tagstrlen);
    target.before('<button onclick="removeTag(this)" data-tag="' + tagval + '">' + tagval + '<i class="bi bi-x-lg"></i></button>');
    target.val('');
}

function removeTag(dom){
    if(!tagcnt) return;

    const tagval = $(dom).attr('data-tag');
    tagstrlen -= tagval.length;
    if(tagcnt > 1) tagstrlen--;

    tagcnt--;
    if(tagstrlen == 0) $('#bbs-tag').attr('placeholder', '태그, 띄어쓰기로 구분해 입력');
    $('#bbs-tag').attr('maxlength', 40 - tagstrlen);
    $(dom).remove();
}

function getFileList(){
    var bbsData = new FormData();
    const files =  $('#bbs-file')[0].files;
    
    for(var i = 0; i < files.length; i++){
        if(files[i].size > 32 * 1024 * 1024){
            alert_float('파일의 크기가 너무 큽니다!');
            $('#bbs-file').eq(0).val('');
            return;
        }
        bbsData.append('file' + i, files[i]);
    }

    bbsData.append('why', 'file_list');
    bbsData.append('id', $('#i').val());
    $('#bbs-file-add').html('확인 중 <i class="bi bi-arrow-repeat"></i>')

    $.ajax({
        url: '../api/bbsquery.php',
        type: 'post',
        processData: false,
        contentType: false,
        data: bbsData,
        dataType: 'json',
    }).done(function(res){
        if(!res.success)
            alert_float(res.message);
        else {
            const size = Math.round((res.totalsize * 100 / (1024 * 1024))) / 100
            $('#file-table-wrapper').html(res.content);
            $('#file-limit-size').html('크기: ' + size + ' / 32MB');
            $('#file-limit-cnt').html('개수: ' + res.filecnt + ' / 10개');
            $('#size').val(res.totalsize);
            $('#filecnt').val(res.filecnt);
            $('#bbs-file-add').html('파일 추가');
            checkValid();
        }
    });
}

function checkValid(){
    let flag = true;
    const totalSize = $('#size').val();
    const filecnt = $('#filecnt').val();

    if(totalSize > 32 * 1024 * 1024){
        flag = false;
        $('#file-limit-size').addClass('input-tobig');
    } else {
        $('#file-limit-size').removeClass('input-tobig');
    }

    if(filecnt > 10){
        flag = false;
        $('#file-limit-cnt').addClass('input-tobig');
    } else {
        $('#file-limit-cnt').removeClass('input-tobig');
    }

    if(!flag)
        $('#bbs-file-summary').addClass('error').html('오류!');
    else {
        $('#bbs-file-summary').removeClass('error').html('총 ' +  filecnt + '개의 파일이 선택됨');
    }

    if($('#bbs-type').val() == '') flag = false;
    if($('#bbs-title').val().length == 0) flag = false;
    $('.submit-wrapper button').attr('disabled', !flag);
}

function deleteFile(fileid){
    if(confirm('정말 삭제하시겠습니까?')){
        $.ajax({
            url: '../api/bbsquery.php',
            type: 'post',
            data: {
                why: 'delete_file',
                fileid: fileid,
            },
            dataType: 'json',
        }).done(function(res){
            if(!res.success){
                alert_float(res.message);
            } else {
                getFileList();
            }
        });
    }
}

$('.submit-wrapper button').on('click', function(){
    let bbsData = new FormData();
    const files =  $('#bbs-file')[0].files;

    for(let i = 0; i < files.length; i++){
        bbsData.append('file' + i, files[i]);
    }

    let tag = '';
    $('#bbs-tag-fake button').each((_, dom) => {
        tag += ($(dom).attr('data-tag') + ' ');
    });
    tag = tag.trim();

    bbsData.append('why', 'new_post');
    bbsData.append('bbstype', $('#bbs-type').val());
    bbsData.append('title', $('#bbs-title').val());
    bbsData.append('tag', tag);
    bbsData.append('main', $('.ql-editor').html());
    bbsData.append('resp', $('#resp').val());
    bbsData.append('id', $('#i').val());

    $(this).html('업로드 중 <i class="bi bi-arrow-repeat"></i>');

    $.ajax({
        url: '../api/bbsquery.php',
        type: 'post',
        processData: false,
        contentType: false,
        data: bbsData,
        dataType: 'json',
    }).done(function(res){
        if(!res.success){
            alert_float(res.message);
            $('.submit-wrapper button').html(originalHtml);
        } else {
            const bindpromise = callOnSubmit('bbs', res.id);
            bindpromise.then((bindres) => {
                window.location.href = 'view?i=' + res.id;
            }).catch((bindres) => {
                $.ajax({
                    url: '../api/bbsquery.php',
                    type: 'post',
                    data: {
                        why: 'delete_post',
                        id: res.id,
                    },
                    dataType: 'json',
                }).done(function(res){
                    if(!res.success){
                        alert_float(res.message);
                    }
                });
            });
        }
    });
});


