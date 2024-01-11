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

        if(!is_lesson_teacher()){
            alert('레슨부장만 접근 가능합니다!');
            redirect('list');    
            return;        
        }
    }

    if(isset($_GET['i'])){
        global $pdo;

        $sql = $pdo->prepare('SELECT l.*, bbs.* FROM lessonlist l LEFT JOIN bbs ON l.postid = bbs.id WHERE l.id = :searchid');
        $sql->bindParam(':searchid', $_GET['i']);
        $sql->execute();
        $row = $sql->fetch();

        if(!$row){
            alert('존재하지 않는 글입니다.');
            redirect('syllabus');
            return;
        }

        if($row['email'] != $_SESSION['email']){
            alert('자기 자신의 글만 수정할 수 있습니다.');
            redirect('syllabus');
            return;
        }
    }

    $navbartitle = '레슨';
?>
<!DOCTYPE html>
<head>
    <?php include '../parts/head.php'?>
    <?php css("../assets/css/lesson/syllabus_write.css"); ?>
    <?php include '../parts/quilljs.php'?>
</head>
<body>
    <header>
        <?php include '../parts/navbar.php'?>
        <?php include 'subnavbar.php'?>
        <script>makeSubNavFocus(0);</script>
    </header>
    <main>
        <div class="title-table">
            <div>
                <!-- <i class="bi bi-mortarboard-fill"></i> -->
                <input id="lesson-title" placeholder="레슨 이름" maxlength="60" value="<?php if(isset($_GET['i'])) echo $row['title']; ?>">
            </div>
            <div>
                <!-- <i class="bi bi-people-fill"></i> -->
                <input id="lesson-max" placeholder="정원" type="number" value="<?php if(isset($_GET['i'])) echo $row['maxstudent']; ?>">
            </div>
        </div>
        <div id="quill">
            <?php if(isset($_GET['i'])) echo purify_full($row['main']); ?>
        </div>
        <div class="submit-wrapper">
            <?php
                if(isset($_GET['i'])) echo '<button class="btn-radius btn-black">레슨 수정</button>';
                else echo '<button class="btn-radius btn-black" disabled>레슨 등록</button>';
            ?>
        </div>
    </main>
    <footer>
        <?php include '../parts/footer.php'?>
    </footer>
    <input value="<?php echo $_GET['i']?>" id="id" style="display:none;">
    <?php script("../assets/js/lesson/syllabus_write.js"); ?>
</body>