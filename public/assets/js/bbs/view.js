let menuvisible = false;

const commenticon = '<i class="bi bi-chat-dots" viewBox="0 0 16 16"></i>';
const noticeicon = '<i class="bi bi-megaphone-fill"></i>';

$('#post-dropdown-btn').click((e) => {
    e.stopPropagation();
    dropdownVisibility(!menuvisible);
});
$(document).click(function(){
    dropdownVisibility(false);
});

$('#post-like-btn').on('click', function(){
    const id = $('#id').val();
    const target = $(this);

    $.ajax({
        url: '../api/bbsquery.php',
        type: 'post',
        data: {
            why: 'update_like',
            id: id,
        },
        dataType: 'json',
    }).done(function(res){
        if(!res.success){
            alert_float(res.message);
        } else {
            if(res.active){
                target.addClass('liked');
                target.html('<i class="bi bi-heart-fill"></i> ' + res.likes);
            } else {
                target.removeClass('liked');
                target.html('<i class="bi bi-heart"></i> ' + res.likes);
            }
        }
    });
});

$('#post-delete-btn').on('click', function(){
    if(confirm('정말 삭제하시겠습니까?')){
        const id = $('#id').val();

        $.ajax({
            url: '../api/bbsquery.php',
            type: 'post',
            data: {
                why: 'delete_post',
                id: id,
            },
            dataType: 'json',
        }).done(function(res){
            if(!res.success){
                alert_float(res.message);
            } else {
                alert('성공적으로 삭제되었습니다.');
                window.location.href = $('.post-option-wrapper a:last-child').attr('href');
            }

            dropdownVisibility(false);
        });
    }
});

$('#post-notice-btn').on('click', function(){
    const id = $('#id').val();

    $.ajax({
        url: '../api/bbsquery.php',
        type: 'post',
        data: {
            why: 'toggle_notice',
            id: id,
        },
        dataType: 'json',
    }).done(function(res){
        alert_float(res.message, res.success);
        if(res.success){
            var result = '공지 ';
            if(res.active) result += '삭제';
            else result += '등록';
            result += '&nbsp;' + noticeicon;
            $('#post-notice-btn').html(result);
        }

        dropdownVisibility(false);
    });
});

$('.com-btn').on('click', function(){
    const postid = $('#id').val();
    const comid = $('#comid').val();
    const respid = $('#respid').val();
    const main = $('#quill .ql-editor').html();

    $.ajax({
        url: '../api/comquery.php',
        type: 'post',
        data: {
            why: 'add_comment',
            postid: postid,
            comid: comid,
            respid: respid,
            main: main,
        },
        dataType: 'json',
    }).done(function(res){
        if(!res.success){
            alert_float(res.message);
        } else {
            loadReply();
        }
    });
});

$('.com-cancel-btn').on('click', initializeInput);

Quill.register("modules/imageCompressor", imageCompressor);

var options = {
    debug: 'warn',
    modules: {
        imageCompressor: {
            quality: 1,
            maxWidth: 1,
            maxHeight: 1,
            imageType: 'image/jpeg',
        },
        toolbar: [
            [{'size': ['small', false, 'large', 'huge']}],
            ['bold', 'italic', 'underline', 'strike'],
            [{'background': []}, {'color': []}],
            ['link'],
        ]
    },
    scrollingContainer: '#comment-input-container',
    placeholder: '댓글 작성...',
    readOnly: false,
    theme: 'snow'
  };

var editor = new Quill('#quill', options);
$('.ql-toolbar.ql-snow').css('padding', '4px');
$('#quill .ql-editor').css('padding', '12px');

editor.on('text-change', function(delta, oldDelta, source){
    const len = $('#quill .ql-editor').html().length;
    const target = $('#comment-char-cnt');

    target.html(len + ' / 2000');
    if(len > 2000) target.css('color', 'red');
    else target.css('color', 'black');
});

// start of function definition

function dropdownVisibility(visible){
    if(visible){
        $(this).addClass('selected');
        $('#post-dropdown').show();
    } 
    else {
        $(this).removeClass('selected');
        $('#post-dropdown').hide();
    }

    menuvisible = visible;
}

function showHidden(){
    const hidden = $('#hiddenpos').val();
    if(hidden != 0){
        $('#comment-tab > div:nth-child(' + hidden + ')').show();
        $('#hiddenpos').val(0);
    }
}

function initializeInput(){
    editor.deleteText(0, editor.getLength());

    $('#comment-tab').append($('#comment-input-container'));
    showHidden();

    $('#comment-input-container').removeClass('reply');
    $('#quill .ql-editor').attr('data-placeholder', '댓글 작성...');
    $('.com-btn').html('게시');

    $('#comid').val(0);
    $('#respid').val(0);
    $('#inputpos').val(999999);
}

function moveInputReply(childno, respid){
    showHidden();

    var targetPos;
    if($('#inputpos').val() <= childno) targetPos = childno + 1;
    else targetPos = childno;

    const target = $('#comment-tab > div:nth-child(' + targetPos + ')');

    $('#comment-input-container').insertAfter(target);
    $('#comment-input-container').addClass('reply');
    $('#quill .ql-editor').attr('data-placeholder', '답글 작성...');

    $('#comid').val(0);
    $('#respid').val(respid);
    $('#inputpos').val(targetPos);
}

function moveInputFix(childno, comid, isReply){
    showHidden();

    let targetPos;
    if($('#inputpos').val() <= childno) targetPos = childno + 1;
    else targetPos = childno;

    const target = $('#comment-tab > div:nth-child(' + targetPos + ')');
    $('#comment-input-container').insertAfter(target);
    target.hide();

    if(isReply) $('#comment-input-container').addClass('reply');

    $('#quill .ql-editor').attr('data-placeholder', '수정...');
    $('.com-btn').html('수정');

    $('#quill .ql-editor').html(target.find('div[name=main]').html());

    $('#comid').val(comid);
    $('#respid').val(0);
    $('#inputpos').val(targetPos);
    $('#hiddenpos').val(childno);
}

function loadReply(){
    const postid = $('#id').val();
    $.ajax({
        url: '../api/comquery.php',
        type: 'post',
        data: {
            why: 'load_comment',
            postid: postid,
        },
        dataType: 'json',
    }).done(function(res){
        if(!res.success){
            alert_float(res.message);
        } else {
            $('#comment-tab > div:not(#comment-input-container)').remove();
            $('#comment-tab').prepend(res.content);
            $('#comment-tab-btn').html('댓글 (' + res.comcnt + ')');
            $('#post-com-btn').html('<i class="bi bi-chat-dots"></i> ' + res.comcnt);
            initializeInput();
        }
    });
}

function deleteReply(comid){
    if(confirm('정말로 삭제하시겠습니까?')){
        $.ajax({
            url: '../api/comquery.php',
            type: 'post',
            data: {
                why: 'delete_comment',
                comid: comid,
            },
            dataType: 'json',
        }).done(function(res){
            alert_float(res.message, res.success);
            if(res.success){
                loadReply();
            }
        });
    }
}

function updateComLike(dom, comid){
    $.ajax({
        url: '../api/comquery.php',
        type: 'post',
        data: {
            why: 'like_comment',
            comid: comid,
        },
        dataType: 'json',
    }).done(function(res){
        if(!res.success){
            alert_float(res.message);
        } else {
            const target = $(dom).parent();
            var inner = '';
            if(res.active){
                inner = '<i class="bi bi-heart-fill liked" onclick="updateComLike(this, ' + comid +')"></i>';
            } else {
                inner = '<i class="bi bi-heart" onclick="updateComLike(this, ' + comid +')"></i>';
            }
            inner += '<br>' + res.likes;

            target.html(inner);
        }
    });
}