<?php
    require_once '../../api/jungmoquery.php';
    require_once '../../api/util.php';

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

    $navbartitle = '정모';
?>
<!DOCTYPE html>
<head>
    <?php include '../parts/head.php'?>
    <link href="//cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <?php css("../assets/css/main.css"); ?>
    <?php css("../assets/css/jungmo/apply.css"); ?>
    <script src="//cdn.quilljs.com/1.3.6/quill.js"></script>
    <script src="https://unpkg.com/quill-image-compress@1.2.11/dist/quill.imageCompressor.min.js"></script>
</head>
<body>
    <header>
        <?php include '../parts/navbar.php'?>
        <?php include 'subnavbar.php'?>
        <script>makeSubNavFocus(0);</script>
    </header>
    <main>
        <?php
        if(exec_auth(EXEC_PLAN))
            echo '<div class="jungmo-add-bar box-shadow-inner">
                    <input type="date" id="date" class="form-control">
                    <div class="divider"></div>
                    브실골: <input type="checkbox" id="isbsg" value="1">
                    연주회: <input type="checkbox" id="isconcert" value="1">
                    <button class="add-jungmo-btn btn-radius">정모 생성</button>
                    <button class="add-jungmo-btn mobile btn-radius">생성</button>
            </div>';
        ?>
        <?php echo get_post_all()['content']; ?>
    </main>
    <div id="post-input-container" style="display: none;">
        <div class="post-top">
            제목: <input class="form-control" id="title" placeholder="작곡가, 곡 제목" maxlength="100">
            길이: <input class="form-control" id="m" maxlength="2">분 <input class="form-control" id="s" maxlength="2">초
        </div>
        <div id="quill">
        </div>
        <div class="post-bottom">
            <span id="post-char-cnt">11 / 2000</span>
            <button id="jungmo-cancel-btn">
                취소
            </button>
            <button id="jungmo-post-btn">
                등록
            </button>
        </div>
    </div>
    <footer>
        <?php include '../parts/footer.php'?>
    </footer>
    <input id="id" type="hidden" value="">
    <input id="postid" type="hidden" value="0">
    <?php script("../assets/js/jungmo/apply.js"); ?>
</body>