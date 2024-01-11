<?php
    require_once '../../api/util.php';
    require_once '../../api/concertquery.php';

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

    global $pdo;
    try{
        $sql = $pdo->prepare('SELECT * FROM concertidea WHERE id = -1');
        $sql->execute();
        $row = $sql->fetch();
        
    } catch (Exception $e){
        alert('유효하지 않은 접근입니다.');
        errlog($e);
    }

    $config = json_decode(file_get_contents('../../data/json/ideaconfig.json'), true);
    $navbartitle = '연주회';
?>
<!DOCTYPE html>
<head>
    <?php include '../parts/head.php'?>
    <?php css("../assets/css/postview.css"); ?>
    <?php css("../assets/css/concert/idea_view.css"); ?>
    <?php css("../assets/css/concert/apply.css"); ?>
</head>
<body>
    <div id="overlay" class="overlay-shadow" style="display: none">
        <div class="overlay-wrapper">
            <span class="overlay-title border-bottom"><?php echo htmlspecialchars($row['title']); ?></span>
            <span class="theme-name">테마 이름: <?php echo htmlspecialchars($row['theme']); ?> / 아카이빙됨</span>
            <div class="main-post ql-editor">
                <?php echo purify_full($row['main']); ?>
            </div>
            <div>
                <button class="btn-white btn-radius" onclick="$('#overlay').hide();">확인</button>
            </div>
        </div>
    </div>
    <header>
        <?php include '../parts/navbar.php'?>
        <?php include 'subnavbar.php'?>
        <script>makeSubNavFocus(0);</script>
    </header>
    <main>
        <div class="tab-btn-wrapper border-bottom">
            <div class="tab-btn-menu-wrapper">
                <button class="tab-btn selected" onclick="concertChangeTab(0, this);">테마연주회</button>
            </div>
            <div class="tab-btn-menu-wrapper">
                <button class="tab-btn" onclick="concertChangeTab(1, this);">정기연주회</button>
            </div>
            <div id="tab-btn-border-bottom"></div>
        </div>
        <div class="tab-container">
            <div class="songlist-wrapper border">
                <div class="concert-apply-top border-bottom">
                    <i class="bi bi-music-note-list"></i>&nbsp;테마연주회
                    <?php 
                        if($row){
                            echo ': '.htmlspecialchars($row['theme']);
                            echo '<button class="btn-radius btn-white" onclick="$(\'#overlay\').show();">제안서</button>';
                        } else {
                            echo ': 주제 선정 중입니다!';
                        }
                        
                        if(exec_auth(EXEC_CONCERT)){
                            if($config['concertapply']) echo '<button class="btn-radius btn-black" name="concert-activate-btn">비활성화</button>';
                            else echo '<button class="btn-radius btn-black" name="concert-activate-btn">활성화</button>';
                        }
                    ?> 
                </div>
                <div class="songlist">
                    <?php 
                        echo load_song(-1, $_SESSION['email'])['content']; 
                        if($config['concertapply']){
                            echo '<div id="song-input-container" class="border-bottom">
                                    <div>
                                        <i class="bi bi-music-note"></i><input id="song-title" placeholder="작곡가, 제목">
                                    </div>
                                    <div>
                                        <i class="bi bi-link"></i><input id="song-link" placeholder="참고 링크 (필수 X)">
                                    </div>
                                    <div>
                                        <i class="bi bi-stopwatch"></i><input type="number" id="song-m" maxlength="2" placeholder="분">:<input type="number" id="song-s" maxlength="2" placeholder="초">
                                        / <i class="bi bi-person-fill"></i><input id="song-perf" placeholder="연주자 수">
                                    </div>
                                    <div>
                                        <input id="song-id" style="display: none">
                                        <button id="song-cancel-btn">취소</button>
                                        <button id="song-add-btn" disabled>참가</button>
                                    </div>
                                </div>';
                        }
                    ?>
                </div>
            </div>
        </div>
        <div class="tab-container" style="display: none">
            <div class="songlist-wrapper border">
                <div class="concert-apply-top  border-bottom">
                    <i class="bi bi-music-note-list"></i>&nbsp;정기연주회
                    <?php
                        if(exec_auth(EXEC_CONCERT)){
                            if($config['concertapply']) echo '<button class="btn-radius btn-black" name="concert-activate-btn">비활성화</button>';
                            else echo '<button class="btn-radius btn-black" name="concert-activate-btn">활성화</button>';
                        }
                    ?>
                </div>
                <div class="songlist">
                    <?php echo load_song(0, $_SESSION['email'])['content']; ?>
                </div>
            </div>
        </div>
    </main>
    <footer>
        <?php include '../parts/footer.php'?>
    </footer>
    <input value="-1" id="id" style="display:none;">
    <input value="0" id="songid" style="display:none;">
    <input value="999999" id="inputpos" style="display:none;">
    <input value="0" id="hiddenpos" style="display:none;">
    <?php script("../assets/js/concert/idea_view.js"); ?>
    <?php script("../assets/js/concert/apply.js"); ?>
</body>