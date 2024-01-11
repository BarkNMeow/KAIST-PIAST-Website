let imageList = {};
Quill.register("modules/imageCompressor", imageCompressor);

let options = {
    debug: 'warn',
    modules: {
        imageCompressor: {
            quality: 1,
            maxWidth: 150,
            maxHeight: 150,
            imageType: 'image/jpeg',
        },
        toolbar: [
            [{'size': ['small', false, 'large', 'huge']}],
            ['bold', 'italic', 'underline', 'strike'],
            [{'background': []}, {'color': []}],
        ]
    },
    placeholder: 'ex) 이거보세요! 완전 부럽죠?',
    readOnly: false,
    theme: 'bubble'
  };

let editor = new Quill('#quill', options);
editor.on('text-change', function(delta, oldDelta, source) {
    if(delta.ops.filter(i => i.insert && i.insert.image).length > 0){
        const retain_del = delta.ops.filter(i => i.retain);
        const retain_len = (retain_del.length ? retain_del[0].retain : 0);
        editor.updateContents(new Delta().retain(retain_len).delete(1));
    }
});

$('.image-inner, .menu .bi-plus-circle').click(function(){
    $('#activity-file').trigger('click');
});

$('.bi-trash').click(function(){
    const target = $('#activity-image .image-viewer').eq(viewidx)
    const hash = target.attr('name');

    delete imageList[hash];
    target.remove();
    imagenum -= 1;
    viewidx = viewidx % imagenum;

    if(imagenum > 0) updateImageview();
    else {
        viewidx = 0;
        $('#activity-image').addClass('invalid');
        $('.image-inner').show();
        $('.image-ui').hide();
    }
    verifyInput();
});

$('#activity-file').change(function(){
    if($(this)[0].files && $(this)[0].files[0]) {
        const list = $(this)[0].files;

        $('.image-inner').hide();
        $('.image-ui').show();
        
        let promise = [];
        for(let i = 0; i < list.length; i++){
            // new Compressor(list[i], {

            // })
            let filePromise = new Promise(resolve => {
                const reader = new FileReader();
                reader.onload = e => {
                    const words = CryptoJS.lib.WordArray.create(e.target.result);
                    const hash = CryptoJS.SHA1(words);
                    if(!imageList[hash]){
                        imageList[hash] = list[i];
                        resolve([hash, e.target.result]);
                    } else {
                        resolve();
                    }
                }
                reader.readAsArrayBuffer(list[i]);
            });
            promise.push(filePromise);
        }

        Promise.all(promise).then(newList => {
            imagenum = Object.keys(imageList).length;
            for(let i = 0; i < newList.length; i++){
                if(!newList[i]) continue;
                let path = URL.createObjectURL(new Blob([newList[i][1]]));
                const last = $('<div class="image-viewer" name="' + newList[i][0] + '"></div>').appendTo('#activity-image');
                last.css('background-image', 'url(' + path + ')');
            }

            if(imagenum > 0) $('#activity-image').removeClass('invalid');

            updateImageview();
            verifyInput();
        });
    }
});

function getImageList(json){
    imageList = json;
}

// 확인
$('#activity-date').on('blur input', function(){
    if($(this).val() === '') $(this).addClass('invalid');
    else $(this).removeClass('invalid');
});

$('#activity-attendee input').click(function(){
    if(!$('#activity-attendee input').is(':checked')) $('#activity-attendee').addClass('invalid');
    else $('#activity-attendee').removeClass('invalid');
})

$('#activity-form input, .score-table input').on('input change', verifyInput);
editor.on('text-change', verifyInput);

function verifyInput(){
    let flag = true;
    if(!$('#activity-date').val()) flag = false;
    if(!$('#activity-attendee input').is(':checked')) flag = false;
    if(imagenum == 0) flag = false;

    $('#score-table > div input').each(function(){
        if(!$(this).is(':disabled') && $(this).val() == ''){
            flag = false;
        }
    })
    if($('#activity-score-total').html() === '0') flag = false;

    $('.submit-wrapper button').attr('disabled', !flag);
}

$('.submit-wrapper button').one('click', submit);
function submit(){
    let bbsData = new FormData();

    bbsData.append('why', 'activity_post');
    bbsData.append('id', $('#id').val());
    bbsData.append('date', $('#activity-date').val());
    bbsData.append('title', $('#activity-title').val());

    $('input[name=attend]').each(function(){
        if($(this).is(':checked')){
            bbsData.append('who[]', $(this).val());
        }
    })

    for(hash in imageList){
        bbsData.append('image[]', imageList[hash]);
    }

    bbsData.append('main', $('.ql-editor').html());

    let scoreboard = [];
    $('#score-table > div').each(function(){
        const event = parseInt($(this).find('select').val());
        const n = parseInt($(this).find('input[name=n]').val());
        const k = parseInt($(this).find('input[name=k]').val());
        if(event != -1){
            scoreboard.push({event: event, n: n, k: k});
        }
        
    })
    bbsData.append('scoreboard', JSON.stringify(scoreboard));

    const originalHtml = $(this).html();
    $(this).html('업로드 중 <i class="bi bi-arrow-repeat"></i>');

    $.ajax({
        url: '../api/amityquery.php',
        type: 'post',
        processData: false,
        contentType: false,
        data: bbsData,
        dataType: 'json',
    }).done(function(res){
        if(!res.success){
            alert_float(res.message);
            $('.submit-wrapper button').html(originalHtml);
            $('.submit-wrapper button').one('click', submit);
        } else {
            window.location.href = 'activity_view?i=' + res.id;
        }
    });
}