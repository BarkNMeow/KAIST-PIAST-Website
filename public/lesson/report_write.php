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
        
        if(!is_lesson_student()){
            alert('레슨 수강생만 접근 가능합니다!');
            redirect('../');
            return;
        }
    }

    global $pdo;
    try{
        $sql = $pdo->prepare('SELECT l.*, bbs.title, CONCAT(u.gen, \'기 \', u.nm) AS gennm FROM 
                            lessonlist l LEFT JOIN userinfo u ON l.email = u.email LEFT JOIN bbs ON l.postid = bbs.id   
                            WHERE l.id IN (SELECT lessonid FROM lessonstudent WHERE email = :email)');
        $sql->bindParam(':email', $_SESSION['email']);
        $sql->execute();
        $lessons = $sql->fetchAll();

        $sql = $pdo->prepare('SELECT * FROM lessonreport WHERE email = :email ORDER BY lessonid ASC, date ASC');
        $sql->bindParam(':email', $_SESSION['email']);
        $sql->execute();
        $rows = $sql->fetchAll();

    } catch(Exception $e){
        alert('요청 처리 중 오류가 발생했습니다.');
        errlog($e);
        redirect('../');
        return;
    }

    $navbartitle = '레슨';
?>
<!DOCTYPE html>
<head>
    <?php include '../parts/head.php'?>
    <link href="//cdn.quilljs.com/1.3.6/quill.bubble.css" rel="stylesheet">
    <?php css("../assets/css/lesson/report_write.css"); ?>
    <script src="//cdn.quilljs.com/1.3.6/quill.js"></script>
</head>
<body>
    <header>
        <?php include '../parts/navbar.php'?>
        <?php include 'subnavbar.php'?>
        <script>makeSubNavFocus(1);</script>
    </header>
    <main>
        <div class="report-select-container">
            <button id="report-showlist" class="btn-radius btn-black"><i class="bi bi-list"></i></button>
            <select id="report-lessonid">
                <?php
                    foreach($lessons as $lesson){
                        echo '<option value="'.$lesson['id'].'">'.$lesson['gennm'].' - '.$lesson['title'].'</option>';
                    }
                    ?>
            </select>
        </div>
        <div id="report-container">
            <div id="report-list">
                <button class="border-bottom selected" id="new-report">새로운 일지</button>
                <?php
                    $lessonlist = reportlist_get($lessons[0]['id'])['content'];
                    foreach($lessonlist as $one){
                        echo '<button onclick="loadReport('.$one['id'].', this)" class="border-bottom '.($one['accept'] >= 2 ? 'text-accept' : 'text-reject').'">'.$one['date'].'</button>';
                    }
                ?>
            </div>
            <div>
                <div class="input-wrapper">
                    <i class="bi bi-calendar-fill"></i>
                    <input type="date" class="form-control" id="report-date">
                    <div id="report-status" class="text-grey">작성중</div>
                </div>
                <div class="image">
                    <div id="image-inner">
                        <i class="bi bi-card-image"></i>
                        인증사진 추가...
                    </div>
                    <div id="image-viewer" style="display: none;" title="수정하려면 이미지를 클릭하세요">
                    </div>
                </div>
                <div id="quill"></div>
                <div id="text-count">
                    0 / 1000
                </div>
                <div class="btn-bottom-wrapper">
                    <button class="btn-radius btn-nored" id="btn-delete">삭제</button>
                    <button class="btn-radius btn-white" id="btn-init">초기화</button>
                    <button class="btn-radius btn-black" id="btn-submit">제출</button>
                </div>
            </div>
            <div class="shadow"></div>
        </div>
        

        <input type="file" id="file" style="display: none">
        <input type="hidden" id="id">
        <!-- 레슨 일지 생성 -->
    </main>
    <footer>
        <?php include '../parts/footer.php'?>
    </footer>
    <?php script("../assets/js/lesson/report_write.js"); ?>
</body>