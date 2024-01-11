let memory = {};

// 레슨부장
function showTeacherlist(){
    $('#teacherlist-search-input').val('#all');
    getTeacher();
    $('#teacherlist-overlay').show();
}

$('#teacherlist-all-btn').click(function(){
    $('#leaderlist-search-input').val('#all');
    getTeacher();
});

$('#teacherlist-search-btn').click(getTeacher);

$('#teacherlist-search-input').on('keyup', function(key){
    if(key.keyCode == 13) {
        $(this).blur();
        getTeacher();
    }
});

function getTeacher(){
    const nm = $('#teacherlist-search-input').val();

    $.ajax({
        url: '../api/lessonquery.php',
        type: 'post',
        data: {
            why: 'teacher_get',
            nm: nm,
        },
        dataType: 'json',
    }).done(function(res){
        if(!res.success) alert_float(res.message);
        $('#teacherlist').html(res.content);
    });
}

function modifyTeacher(email){
    $.ajax({
        url: '../api/lessonquery.php',
        type: 'post',
        data: {
            why: 'teacher_modify',
            email: email,
        },
        dataType: 'json',
    }).done(function(res){
        alert_float(res.message, res.success);
        if(res.success){
            getTeacher();
            getLessonList();
        }
    });
}

function getLessonList(){
    $.ajax({
        url: '../api/lessonquery.php',
        type: 'post',
        data: {
            why: 'lesson_infolist_get',
        },
        dataType: 'json',
    }).done(function(res){
        if(res.success){
            $('#lesson-wrapper').html(res.content);
        } else {
            alert_float(res.message);
        }
    });
}

function removeLesson(id){
    if(confirm('정말로 이 레슨을 삭제하시겠습니까?')){
        $.ajax({
            url: '../api/lessonquery.php',
            type: 'post',
            data: {
                why: 'lesson_delete',
                id: id,
            },
            dataType: 'json',
        }).done(function(res){
            alert_float(res.message, res.success);
            if(res.success){
                getLessonList();
            }
        });
    }
}

// 레슨부원 관리
function showStudentlist(id){
    memory = {};

    $('#studentlist-id').val(id);
    $('#studentlist-search-input').val('#all');
    $('#studentlist-controlall').prop('checked', false);
    getStudent();
    $('#studentlist-overlay').show();
}

$('#studentlist-all-btn').click(function(){
    $('#studentlist-search-input').val('#all');
    getStudent();
});

$('#studentlist-search-btn').click(getStudent);

$('#studentlist-search-input').on('keyup', function(key){
    if(key.keyCode == 13) {
        $(this).blur();
        getStudent();
    }
});

$('#studentlist-controlall').change(function(){
    const checked = $(this).is(':checked');

    $('#studentlist > div').each(function(){
        const checkbox = $(this).find('input');
        const email = checkbox.attr('data-email');

        if(memory[email]){
            if(memory[email]['checked'] == checked) return;
            else delete memory[email];
        } else {
            const thischecked = checkbox.is(':checked');
            if(thischecked == checked) return;
            else {
                const gennm = $(this).children('div:first-child').html();
                const rank = $(this).children('div:nth-child(2)').html();
                memory[email] = {'gennm': gennm, 'checked': checked, 'rank': rank};
            }
        }

        checkbox.prop('checked', checked);
    });
});


function getStudent(){
    const id = $('#studentlist-id').val();
    const nm = $('#studentlist-search-input').val();

    $.ajax({
        url: '../api/lessonquery.php',
        type: 'post',
        data: {
            why: 'student_get',
            nm: nm,
            id: id,
        },
        dataType: 'json',
    }).done(function(res){
        if(!res.success) 
            alert_float(res.message);
        else {
            const data = res.data;
            for(email in memory){
                if(data[email]){
                    data[email]['checked'] = memory[email]['checked'];
                } else if(nm == '#all') {
                    data[email] = {};
                    data[email]['gennm'] = memory[email]['gennm'];
                    data[email]['checked'] = memory[email]['checked'];
                    data[email]['rank'] = memory[email]['rank'];
                }
            }

            const target = $('#studentlist');
            $('#studentlist').empty();

            if(Object.keys(data).length == 0){
                target.append('<div class="overlay-list-row text-grey"><div>검색 결과가 없습니다.</div><div></div></div>');
                return;
            }
            
            for(email in data){
                const checked = data[email]['checked'] ? ' checked' : '';
                const checkedrow = data[email]['checked'] ? '' : '  text-grey';
                target.append('<div class="overlay-list-row' + checkedrow + '"><div>' + data[email]['gennm'] + '</div><div>' + data[email]['rank'] + '</div><div><input type="checkbox" data-email="' + email + '" ' + checked + '></div></div>');
            }

            $('#studentlist input').click(function(){
                const email = $(this).attr('data-email');
                if(!memory[email]){
                    memory[email] = {};

                    memory[email]['gennm'] = $(this).parent().parent().children('div:first-child').html();
                    memory[email]['rank'] = $(this).parent().parent().children('div:nth-child(2)').html();
                    memory[email]['checked'] = $(this).is(':checked');
                } else {
                    delete memory[email]; // 두 번 클릭 => 변동 X
                }
            });
        }
    });
}

$('#studentlist-confirm').click(function(){
    if(Object.keys(memory).length == 0){
        $('#studentlist-overlay').hide();
        return;
    }

    const id = $('#studentlist-id').val();
    const data = JSON.stringify(memory);

    $.ajax({
        url: '../api/lessonquery.php',
        type: 'post',
        data: {
            why: 'student_modify',
            data: data,
            id: id,
        },
        dataType: 'json',
    }).done(function(res){
        alert_float(res.message, res.success);
        if(res.success){
            $('#studentlist-overlay').hide();
            getLessonList();
        }
    });
});