const originalHtml = $('.submit-wrapper button').html();
let songlistTable = []

let editor = quillEditor('#quill');

$('.idea-title').on('input', checkValid);
$('.idea-theme-title').on('input', checkValid);
$('input[type=radio]').on('click', checkValid);
editor.on('text-change', checkValid);

function checkValid(){
    let flag = true;
    if($('.idea-title').val().length == 0) flag = false;
    if($('.idea-theme-title').val().length == 0) flag = false;
    if(editor.getLength() == 1) flag = false;
    flag = flag && ($('#chk1n').is(':checked') && $('#chk2n').is(':checked') && $('#chk3y').is(':checked') && $('#chk4y').is(':checked'));
    $('.submit-wrapper button').attr('disabled', !flag);
}

$('.submit-wrapper button').on('click', function(){
    const title = $('.idea-title').val();
    const theme = $('.idea-theme-title').val();
    const main = $('.ql-editor').html();
    const id = $('#id').val();
    $(this).html('업로드 중 <i class="bi bi-arrow-repeat"></i>');

    $.ajax({
        url: '../api/concertquery.php',
        type: 'post',
        data: {
            why: 'idea_post',
            title: title,
            theme: theme,
            main: main,
            id: id,
        },
        dataType: 'json',
    }).done(function(res){
        if(!res.success){
            alert_float(res.message);
            $('.submit-wrapper button').html(originalHtml);
        } else {
            const bindpromise = callOnSubmit('concert', res.id);
            bindpromise.then((bindres) => {
                window.location.href = 'idea_view?i=' + res.id;
            }).catch((bindres) => {
                $.ajax({
                    url: '../api/concertquery.php',
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



