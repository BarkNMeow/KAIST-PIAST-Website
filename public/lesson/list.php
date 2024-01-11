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
    }

    $config = updateLessonConfig();
    $navbartitle = '레슨';

?>
<!DOCTYPE html>
<head>
    <?php include '../parts/head.php'?>
    <?php css("../assets/css/board.css"); ?>
    <?php css("../assets/css/lesson/list.css"); ?>
</head>
<body>
    <?php
        if(exec_auth(EXEC_CONCERT)){
            $disable = ($config['active'] ? ' disabled' : '');
            echo '<div class="overlay-shadow" style="display: none" id="overlay" class="overlay-shadow">
                    <div class="overlay-wrapper">
                        <h1>시간 설정</h1>
                        <div>
                            <span class="option-name">신청 시작</span>
                            <input type="date" id="start-date"'.$disable.'>
                            <input type="time" id="start-time"'.$disable.'>
                            '.($config['active'] ? '' : '<button id="start-init" class="btn-radius">X</button>').'
                        </div>
                        <div>
                            <span class="option-name">신청 종료</span>
                            <input type="date" id="end-date">
                            <input type="time" id="end-time">
                            <button id="end-init" class="btn-radius">X</button>
                        </div>
                        <div>
                            <button class="btn-white btn-radius" onclick="$(\'#overlay\').hide();">취소</button>
                            <button class="btn-black btn-radius" id="btn-confirm">확인</button>
                        </div>
                    </div>
                </div>';
        }
    ?>
    <div class="overlay-shadow" style="display: none" id="priority-overlay" class="overlay-shadow">
        <div class="overlay-wrapper">
            <span class="overlay-title">레슨 배정 우선 순위</span>
            <div class="border-top">
                <?php
                    $priority = _get_priority($_SESSION['email']);
                    $desc = array('동연 집행부원', '이번 학기 신입부원', '테마 및 정기 연주회 참가자', '위에 해당되지 않는 현역부원', '위에 해당되지 않는 OB', '지난 학기 레슨 미수료 부원');
                    $hue = 0;
                    $huestep = 360 / count($desc);

                    for($i = 1; $i <= count($desc); $i++){
                        $myp = $i == (7 - $priority);

                        if($myp) $background = ' style="background: hsl('.$hue.', 90%, 80%)"';
                        else $background = '';

                        echo '<div class="priority-row border-bottom"'.$background.'>
                                <div>
                                    <span class="priority-rank">'.$i.'순위</span>
                                    '.$desc[$i - 1].'
                                </div>
                                <div>'.($myp ? '<i class="bi bi-check-lg"></i>' : '').'</div>
                            </div>';

                        $hue += $huestep;
                    }
                    
                ?>
            </div>
            <div>
                <button class="btn-black btn-radius" onclick="$('#priority-overlay').hide();">확인</button>
            </div>
        </div>
    </div>
    <header>
        <?php include '../parts/navbar.php'?>
        <?php include 'subnavbar.php'?>
        <script>makeSubNavFocus(0);</script>
    </header>
    <main>
        <div class="timer-container">
            <div id="lesson-apply-timer"></div>
            <?php if(exec_auth(EXEC_CONCERT)) echo '<button class="btn-radius btn-grey" id="lesson-apply-setting">설정</button>'; ?>
        </div>
        <div class="table-wrapper">
            <div class="table-header">
                <div>레슨 이름</div>
                <div>인원</div>
                <div>확인</div>
            </div>
            <div id="lesson-table">
                <?php echo _lesson_applylist_get(); ?>
            </div>
        </div>
        <div class="bottom-btn-wrapper">
        <button class="btn-black btn-radius" onclick="$('#priority-overlay').show();">우선 순위</button>
        <?php
            if(is_lesson_teacher() && !$config['active']){
                echo '
                    <a href="syllabus_write">
                        <button class="btn-white btn-radius">계획서 쓰기</button>
                    </a>
                ';
            }
        ?>
        </div>
    </main>
    <footer>
        <?php include '../parts/footer.php'?>
    </footer>
    <input style="display:none;" id="active" value="<?php echo ($config['active'] ? 1 : 0); ?>">
    <input style="display:none;" id="start" value="<?php echo $config['start'];?>">
    <input style="display:none;" id="end" value="<?php echo $config['end'];?>">
    <?php script("../assets/js/lesson/list.js"); ?>
</body>