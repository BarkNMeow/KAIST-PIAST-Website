const originalHtml = $('.submit-wrapper button').html();
let editor = quillEditor('#quill');
let pdfHandler = new Promise((resolve, reject) => {resolve();})

$('.bbs-title').on('input', checkValid);
$('.bbs-tag').on('input', checkValid);
$('#bbs-file').on('change', function(){
    pdfHandler = new Promise((resolve, reject) => {
        const fileReader = new FileReader();

        fileReader.onload = function(ev) {
            PDFJS.getDocument(fileReader.result).then((pdf) => {
                let innerHandler = [];

                for(let i = 1; i <= 2; i++){
                    innerHandler.push(new Promise((rs, rj) => {
                        pdf.getPage(i).then((page) => {
                            const viewport = page.getViewport(1);
                            const canvas = document.getElementById('page' + i);
                            const context = canvas.getContext('2d');
        
                            canvas.height = viewport.height;
                            canvas.width = viewport.width;
                            console.log(canvas.width, canvas.height);
                    
                            const task = page.render({canvasContext: context, viewport: viewport})
                            task.promise.then(function(){
                                canvas.toBlob((blob) => rs(blob), 'image/png', 0.3);
                            });
                        }, (dummy) => rs(null));
                    }));
                }

                Promise.all(innerHandler).then((values) => resolve(values));
            }, function(error){
                console.log(error);
            });
        };

        fileReader.readAsArrayBuffer($(this)[0].files[0]);
    });

    $('.uploaded-name').remove();
    checkValid();
});

$('input[type=radio]').on('change', checkValid);
$('input[type=range]').on('input', function(){
    let diff = parseInt($(this).val());
    let star = '';
    let desc = '';

    if(diff <= 2) desc = '나비야';
    else if(diff <= 4) desc = '쉬움';
    else if(diff <= 6) desc = '적절';
    else if(diff <= 8) desc = '어려움';
    else if(diff == 9) desc = 'Liszt에게 쉬움';
    else desc = '겁.나.어.렵.습.니.다!';

    for(let i = 0; i < 5; i++){
        if(diff >= 2) star += '<i class="bi bi-star-fill"></i>';
        else if(diff == 1) star += '<i class="bi bi-star-half"></i>';
        else star += '<i class="bi bi-star"></i>';

        diff -= 2;
    }

    $('#score-star').html(star);
    $('#score-diff-desc').html(desc);
});

function checkValid(){
    let flag = true;
    if($('#bbs-title').val() == '') flag = false;
    if($('#bbs-composer').val() == '') flag = false;
    if($('#bbs-tag').val() == '') flag = false;
    if($('input[name=score-type]:checked').length == 0) flag = false;
    if($('input[name=score-genre]:checked').length == 0) flag = false;

    const file =  $('#bbs-file')[0].files[0];
    if($('#i').val() == 0 && !file){
        flag = false;
    }

    if(file && file.size > 32 * 1024 * 1024){
        flag = false;
    }

    $('.submit-wrapper button').attr('disabled', !flag);
}

$('.submit-wrapper button').on('click', function(){
    $(this).html('업로드 중 <i class="bi bi-arrow-repeat"></i>');

    pdfHandler.then((blob) => {
        let bbsData = new FormData();
        const file =  $('#bbs-file')[0].files[0];

        bbsData.append('why', 'new_score'); // 악보 수정하는 기능 없음?
        bbsData.append('title', $('#bbs-title').val());
        bbsData.append('composer', $('#bbs-composer').val());
        bbsData.append('tag', $('#bbs-tag').val());
        bbsData.append('file', file);

        if(blob){
            if(blob[0]) bbsData.append('image1', blob[0]);
            if(blob[1]) bbsData.append('image2', blob[1]);
        }

        const type = parseInt($('input[name=score-type]:checked').val());
        const genre = parseInt($('input[name=score-genre]:checked').val());
    
        bbsData.append('scoretype', type);
        bbsData.append('genre', genre);
        bbsData.append('diff', parseInt($('#score-diff').val()));
        bbsData.append('main', $('.ql-editor').html());
        bbsData.append('id', $('#i').val());
    
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
});