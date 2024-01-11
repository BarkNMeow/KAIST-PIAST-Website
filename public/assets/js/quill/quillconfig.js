Quill.register("modules/imageCompressor", imageCompressor);
Quill.register('modules/ImageResize', ImageResize);

const Delta = Quill.import('delta');

let insertedList = {}; // 삽입한 이미지 (업로드된 이미지)
let deletedList = {}; // 삽입되어 올라간 이미지 중 삭제 내역
let run = false;

const options = {
    debug: 'warn',
    modules: {
        imageCompressor: {
            quality: 1,
            maxWidth: 1920,
            maxHeight: 1080,
            imageType: 'image/jpeg',

        },
        imageResize: {
            modules: ['Resize']
        },
        toolbar: {
            container: [
                [{ 'size': ['small', false, 'large', 'huge'] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'background': [] }, { 'color': [] }],
                [{ 'list': 'ordered' }, { 'list': 'bullet' }, { 'indent': '-1' }, { 'indent': '+1' }, { 'align': [] }],
                ['link', 'image', 'video']
            ],
        },
    },
    scrollingContainer: '#scrolling-container',
    placeholder: '여기에 작성...',
    readOnly: false,
    theme: 'snow'
};

function quillEditor(id) {
    let editor = new Quill(id, options);
    getImgUrls = (delta) => delta.ops.filter(i => i.insert && i.insert.image).map(i => i.insert.image);

    editor.on('text-change', function (delta, oldDelta, source) {
        if (source == 'user') {
            const inserted = getImgUrls(delta);
            const deleted = getImgUrls(editor.getContents().diff(oldDelta));

            if (inserted[0] && inserted[0][0] != 'd') {
                const hash = inserted[0].substr(-40);
                if (deletedList[hash] > 1) deletedList[hash] -= 1;
                else delete deletedList[hash];
            }

            if (!inserted[0] && deleted[0] && deleted[0][0] != 'd') {
                const hash = deleted[0].substr(-40);
                if (deletedList[hash] == undefined) deletedList[hash] = 1;
                else deletedList[hash] += 1;
            }
        }
    });

    editor.getModule('toolbar').addHandler('image', () => { imageHandler(editor); })

    return editor;
}

function imageHandler(editor) {
    const input = document.createElement('input');
    input.setAttribute('type', 'file');
    input.setAttribute('multiple', 'multiple');
    input.setAttribute('accept', 'image/*');

    $(input).on('change', () => {
        if (input.files !== null) {
            for (let i = 0; i < input.files.length; i++) {
                let data = new FormData();
                data.append('why', 'upload');
                data.append('image', input.files[i]);

                $.ajax({
                    url: '../api/postimage.php',
                    type: 'post',
                    processData: false,
                    contentType: false,
                    data: data,
                    dataType: 'json',

                }).done(function (res) {
                    if (!res.success) {
                        alert_float(res.message);
                    } else {
                        const hash = res.hash;
                        if (insertedList[hash] == undefined) insertedList[hash] = 1;
                        else insertedList[hash] += 1;

                        const range = editor.getSelection(true);
                        editor.updateContents(new Delta().retain(range.index).delete(range.length).insert({ image: 'https://kaist-piast.club/image/postimage/' + res.hash }));
                    }
                });
            }

            $(input).val('');
        }
    })

    input.click();
}

$(window).bind('beforeunload', function () {
    return '정말로 나가시겠습니까?';
});

$(window).bind('pagehide', function () {
    if (!run) redo();
});

function redo() {
    run = true;
    let data = new FormData();
    const list = JSON.stringify(insertedList);

    data.append('why', 'clean');
    data.append('list', list);
    navigator.sendBeacon('../api/postimage.php', data);
}

function callOnSubmit(type, postid) {
    $(window).unbind('beforeunload');
    $(window).unbind('pagehide');

    let promise = new Promise((resolve, reject) => {
        const insertlist = JSON.stringify(insertedList);
        const deletelist = JSON.stringify(deletedList);

        $.ajax({
            url: '../api/postimage.php',
            type: 'post',
            data: {
                why: 'bind',
                insertlist: insertlist,
                deletelist: deletelist,
                type: type,
                id: postid,
            },
            dataType: 'json',
        }).done(function (res) {
            if (!res.success) {
                alert_float(res.message);

                $(window).bind('beforeunload', function () {
                    return '정말로 나가시겠습니까?';
                });

                $(window).bind('pagehide', function () {
                    if (!run) redo();
                });

                reject(res);
            } else {
                resolve(res);
            }
        });
    });

    return promise;
}
