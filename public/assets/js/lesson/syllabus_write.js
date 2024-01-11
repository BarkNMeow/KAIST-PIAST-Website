const originalHtml = $('.submit-wrapper button').html();
let songlistTable = []

let editor = quillEditor('#quill');

$('#lesson-title').on('input', checkValid);
$('#lesson-max').on('input', checkValid);
editor.on('text-change', checkValid);

$('#lesson-title, #lesson-max').on('input blur', function(){
    const me = $(this);
    if($(this).val() === '') me.addClass('invalid');
    else me.removeClass('invalid');
})

function checkValid(){
    let flag = true;
    if($('#lesson-title').val().length == 0) flag = false;
    if($('#lesson-max').val().length == 0) flag = false;
    if(editor.getLength() == 1) flag = false;
    
    const maxstudent = parseInt($('#lesson-max').val());
    if(isNaN(maxstudent)) flag = false;
    else {
        if(maxstudent <= 0 || maxstudent > 127) flag = false;
    }

    $('.submit-wrapper button').attr('disabled', !flag);
}

$('.submit-wrapper button').on('click', function(){
    const title = $('#lesson-title').val();
    const maxstudent = $('#lesson-max').val();
    const main = $('.ql-editor').html();
    const id = $('#id').val();
    $(this).html('업로드 중 <i class="bi bi-arrow-repeat"></i>');

    $.ajax({
        url: '../api/lessonquery.php',
        type: 'post',
        data: {
            why: 'lesson_post',
            title: title,
            maxstudent: maxstudent,
            main: main,
            id: id,
        },
        dataType: 'json',
    }).done(function(res){
        if(!res.success){
            alert_float(res.message);
            $('.submit-wrapper button').html(originalHtml);
        } else {
            console.log(res.postid);
            const bindpromise = callOnSubmit('bbs', res.postid);
            bindpromise.then((bindres) => {
                window.location.href = 'list'
            }).catch((bindres) => {
                $.ajax({
                    url: '../api/lessonquery.php',
                    type: 'post',
                    data: {
                        why: 'lesson_delete',
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

