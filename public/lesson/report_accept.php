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
        
        if(!is_lesson_teacher() && !exec_auth(EXEC_CONCERT)){
            alert('레슨부장과 악장만 접근 가능합니다!');
            redirect('../');
            return;
        }
    }

    global $pdo;
    try{
        $sql = $pdo->prepare('SELECT COUNT(*) AS cnt FROM lessonlist WHERE email = :email');
        $sql->bindParam(':email', $_SESSION['email']);
        $sql->execute();
        $cnt = ($sql->fetch())['cnt'];
        
        if(!exec_auth(EXEC_CONCERT)){ 
            if($cnt == 0){
                alert('진행하고 있는 레슨이 없습니다!');
                redirect('../');
                return;
            }

            $sql = $pdo->prepare('SELECT CONCAT(u.gen, \'기 \', u.nm) AS gennm, l.id AS lessonid, bbs.title, CONCAT(tmp.gen, \'기 \', tmp.nm) AS studentgennm, tmp.* 
                                FROM userinfo u INNER JOIN lessonlist l ON u.email = l.email LEFT JOIN bbs ON l.postid = bbs.id LEFT JOIN
                                (SELECT u.gen, u.nm, s.email AS studentemail, s.lessonid AS tmpid, l.date, l.id AS reportid, l.accept FROM 
                                userinfo u INNER JOIN lessonstudent s ON u.email = s.email AND s.selected > 0 LEFT JOIN lessonreport l ON s.lessonid = l.lessonid AND s.email = l.email) 
                                tmp ON l.id = tmp.tmpid WHERE l.email = :email ORDER BY l.id ASC, tmp.gen ASC, tmp.nm ASC, tmp.date ASC');

        } else {
            $sql = $pdo->prepare('SELECT CONCAT(u.gen, \'기 \', u.nm) AS gennm, l.id AS lessonid, bbs.title, CONCAT(tmp.gen, \'기 \', tmp.nm) AS studentgennm, tmp.* 
                                FROM userinfo u INNER JOIN lessonlist l ON u.email = l.email LEFT JOIN bbs ON l.postid = bbs.id LEFT JOIN
                                (SELECT u.gen, u.nm, s.email AS studentemail, s.lessonid AS tmpid, l.date, l.id AS reportid, l.accept FROM 
                                userinfo u INNER JOIN lessonstudent s ON u.email = s.email AND s.selected > 0 LEFT JOIN lessonreport l ON s.lessonid = l.lessonid AND s.email = l.email) 
                                tmp ON l.id = tmp.tmpid ORDER BY (l.email = :email) DESC, l.id ASC, tmp.gen ASC, tmp.nm ASC, tmp.date ASC');
        }

        $sql->bindParam(':email', $_SESSION['email']);
        $sql->execute();
        $rows = $sql->fetchAll();

    } catch(Exception $e){
        errlog($e);
        alert('요청 처리 중 오류가 발생했습니다.');
        redirect('../');
        return;
    }

    $datalist = array();
    $lessonid = -1;
    $studentemail = '';

    $lessonpnt = -1;
    $studentpnt = -1;

    foreach($rows as $one){
        if($lessonid != $one['lessonid']){
            array_push($datalist, array('id'=> $one['lessonid'], 'gennm' => $one['gennm'], 'title' => $one['title'], 'student' => array()));
            $lessonid = $one['lessonid'];

            $lessonpnt++;
            $studentemail = '';
            $studentpnt = -1;
        } 

        if($studentemail != $one['studentemail']){
            array_push($datalist[$lessonpnt]['student'], array('email' => $one['studentemail'], 'gennm' => $one['studentgennm'], 'report' => array()));
            $studentemail = $one['studentemail'];

            $studentpnt++;
        } 

        if($one['reportid'] !== NULL){
            array_push($datalist[$lessonpnt]['student'][$studentpnt]['report'], array('date' => $one['date'], 'accept' => $one['accept'], 'id' => $one['reportid']));
        }
    }

    $navbartitle = '레슨';
?>
<!DOCTYPE html>
<head>
    <?php include '../parts/head.php'?>
    <link href="//cdn.quilljs.com/1.3.6/quill.bubble.css" rel="stylesheet">
    <?php css("../assets/css/lesson/report_write.css"); ?>
    <?php css("../assets/css/lesson/report_accept.css"); ?>
</head>
<body>
    <header>
        <?php include '../parts/navbar.php'?>
        <?php include 'subnavbar.php'?>
        <?php
            if(is_lesson_student()) $pos = 2;
            else $pos = 1;
            echo '<script>makeSubNavFocus('.$pos.');</script>'
        ?>
    </header>
    <main>
        <div class="tab-btn-wrapper border-bottom">
            <div class="tab-btn-menu-wrapper">
                <button class="tab-btn selected" onclick="changeTab(0, this);">출석표</button>
            </div>
            <div class="tab-btn-menu-wrapper">
                <button class="tab-btn" onclick="changeTab(1, this);" id="tab-btn-report">레슨일지</button>
            </div>
            <div id="tab-btn-border-bottom"></div>
        </div>
        <div class="tab-container">
            <?php
                if(count($datalist)){
                    foreach($datalist as $lesson){
                        echo '<div class="att-table" id="lesson'.$lesson['id'].'">';
                        echo '<div class="lesson-title">'.$lesson['gennm'].' - '.$lesson['title'].'</div>';
    
                        if(count($lesson['student'])){
                            foreach($lesson['student'] as $student){
                                echo '<div class="student-name border-bottom">'.$student['gennm'].'</div>';
                                echo '<div class="report-btn-list border-bottom">';
                                
                                if(count($student['report'])){
                                    foreach($student['report'] as $report){
                                        $date = substr($report['date'], 5, 2).'/'.substr($report['date'], 8, 2).($report['accept'] == 1 || $report['accept'] == 2 ? '<i class="bi bi-exclamation-lg"></i>' : '');
                                        echo '<button class="'.($report['accept'] >= 2 ? 'btn-report-accepted' : 'btn-report-rejected').' btn-radius" name=btn'.$report['id'].' onclick="loadReportByInfo('.$lesson['id'].', \''.$student['email'].'\', '.$report['id'].')">'.$date.'</button>';
                                    }
                                } else {
                                    echo '<span class="text-grey">작성한 레슨일지가 없습니다!</span>';
                                }
    
                                echo '</div>';
                            }
                        } else {
                            echo '<div class="no-student text-grey border-bottom">이 레슨에 포함된 레슨부원이 없습니다 😢</div>';
                        }
    
    
                        echo '</div>';
                    }
                } else {
                    echo 
                    '<div class="att-no-lesson">
                        <span>😕</span><br>생성된 레슨이 없습니다! 레슨부장을 추가했나요?<br>
                    </div>';
                }
                
            ?>
        </div>
        <div class="tab-container" style="display: none">
            <div class="report-select-container">
                <button id="report-showlist" class="btn-radius btn-black"><i class="bi bi-list"></i></button>
                <select id="report-lessonid">
                    <option value="" style="display: none">레슨을 선택하세요.</option>
                    <?php
                        foreach($datalist as $lesson){
                            echo '<option value="'.$lesson['id'].'">'.$lesson['gennm'].' - '.$lesson['title'].'</option>';
                        }
                    ?>
                </select>
            </div>
            <div id="report-container">
                <div id="report-list">
                    <button class="border-bottom text-grey">레슨일지 없음</button>
                    <?php
                        $lessonlist = reportlist_get($lessons[0]['id'])['content'];
                        foreach($lessonlist as $one){
                            echo '<button onclick="loadReport('.$one['id'].', this)" class="border-bottom '.($one['accept'] >= 2 ? 'text-accept' : 'text-reject').'">'.$one['date'].'</button>';
                        }
                    ?>
                </div>
                <div>
                    <div class="input-wrapper">
                        <i class="bi bi-person-fill"></i>
                        <select id="report-name" disabled>

                        </select>
                        <i class="bi bi-calendar-fill"></i>
                        <select id="report-date" disabled>

                        </select>
                        <div>

                        </div>
                    </div>
                    <div class="image">
                        <div id="image-inner">
                            <i class="bi bi-card-image"></i>
                            레슨일지를 선택하세요.
                        </div>
                        <div id="image-viewer" style="display: none;">
                        </div>
                    </div>
                    <div id="quill" class="ql-editor"></div>
                    <div class="btn-bottom-wrapper">
                        <div id="report-status"></div>
                        <button class="btn-radius btn-nored" id="btn-reject" disabled>거절</button>
                        <button class="btn-radius btn-yesgreen" id="btn-accept" disabled>승인</button>
                    </div>
                </div>
                <div class="shadow"></div>
            </div>
        </div>
    </main>
    <footer>
        <?php include '../parts/footer.php'?>
    </footer>
    <?php script("../assets/js/lesson/report_accept.js"); ?>
</body>