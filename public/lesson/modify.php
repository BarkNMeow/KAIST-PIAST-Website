<?php
    require_once '../../api/util.php';
    require_once '../../api/lessonquery.php';

    if(!logged_in()){
        $name = $_SERVER['PHP_SELF'];
        $name = str_replace('.php', '', $name);
        fast_redirect('https://kaist-piast.club/login?r='.$name);
    } else {
        if(!is_auth()){
            alert('계정 인증 후에 이용 가능합니다.');
            redirect('../');
            return;
        }

        if(!exec_auth(EXEC_CONCERT)){
            alert('악장만 접근 가능합니다!');
            redirect('../');
            return;
        }
    }

    $navbartitle = '레슨';
?>
<!DOCTYPE html>
<head>
    <?php include '../parts/head.php'?>
    <?php css("../assets/css/board.css"); ?>
    <?php css("../assets/css/overlaylist.css"); ?>
    <?php css("../assets/css/lesson/modify.css"); ?>
</head>
<body>
    <div class="overlay-shadow" style="display: none;" id="teacherlist-overlay">
        <div class="overlay-wrapper">
            <div class="overlay-top">
                <span class="overlay-title">레슨부장 관리</span>
                <i class="bi bi-x-lg" onclick="$('#teacherlist-overlay').hide();"></i>
            </div>
            <div>
                <div class="overlay-search-wrapper">
                    <input type="text" class="form-control search-input" id="teacherlist-search-input" placeholder="이름으로 검색" value="#all">
                    <button class="btn-grey btn-radius" id="teacherlist-search-btn"><i class="bi bi-search"></i></button>
                    <button class="btn-grey btn-radius" id="teacherlist-all-btn"><i class="bi bi-check-square"></i></button>
                </div>
                <div class="overlay-list-header">
                    <div>이름</div>
                    <div>추가</div>
                </div>
                <div class="overlay-list-wrapper" id="teacherlist">
                </div>
            </div>
        </div>
    </div>
    <div class="overlay-shadow" style="display: none;" id="studentlist-overlay">
        <input type="hidden" id="studentlist-id">
        <div class="overlay-wrapper">
            <div class="overlay-top">
                <span class="overlay-title">레슨부원 관리</span>
                <i class="bi bi-x-lg" onclick="$('#studentlist-overlay').hide();"></i>
            </div>
            <div>
                <div class="overlay-search-wrapper">
                    <input type="text" class="form-control search-input" id="studentlist-search-input" placeholder="이름으로 검색" value="#all">
                    <button class="btn-grey btn-radius" id="studentlist-search-btn"><i class="bi bi-search"></i></button>
                    <button class="btn-grey btn-radius" id="studentlist-all-btn"><i class="bi bi-check-square"></i></button>
                </div>
                <div class="overlay-list-header">
                    <div>이름</div>
                    <div>순번</div>
                    <div><input type="checkbox" id="studentlist-controlall"></div>
                </div>
                <div class="overlay-list-wrapper" id="studentlist">
                </div>
            </div>
            <div class="overlay-btn-wrapper">
                <button class="btn-black btn-radius" id="studentlist-confirm">확인</button>
            </div>
        </div>
    </div>
    <header>
        <?php include '../parts/navbar.php'?>
        <?php include 'subnavbar.php'?>
        <?php
            $pos = 1;
            if(is_lesson_student()) $pos += 1;
            if(is_lesson_teacher() || exec_auth(EXEC_CONCERT)) $pos += 1;
            echo '<script>makeSubNavFocus('.$pos.');</script>';
        ?>
    </header>
    <main>
        <div class="lesson-top">
            <button class="btn-white btn-radius" onclick="showTeacherlist()">레슨부장 관리</button>
            <button class="btn-black btn-radius" onclick="showTeacherlist()">블랙리스트</button>
        </div>
        <div id="lesson-wrapper">
            <?php echo lesson_infolist_get()['content']; ?>
        </div>
    </main>
    <footer>
        <?php include '../parts/footer.php'?>
    </footer>
    <?php script("../assets/js/lesson/modify.js"); ?>
</body>