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

    $config = updateIdeaConfig();

    global $pdo;
    try{
        $sql = $pdo->prepare('SELECT * FROM concertidea WHERE id = :ideaid');
        $sql->bindParam(':ideaid', $_GET['i']);
        $sql->execute();
        $row = $sql->fetch();

        if(!$row){
            alert('존재하지 않거나 삭제된 게시물입니다.');
            redirect('idea');
            return;
        }

        $sql = $pdo->prepare('SELECT sem from userinfo WHERE email = :email');
        $sql->bindParam(':email', $_SESSION['email']);
        $sql->execute();
        
        $sem = ($sql->fetch())['sem'];

        if($sem < 4){
            $sql = $pdo->prepare('SELECT * FROM concertlike WHERE ideaid = :ideaid AND email = :email');
            $sql->bindParam(':ideaid', $_GET['i']);
            $sql->bindParam(':email', $_SESSION['email']);
            $sql->execute();
            $lkrow = $sql->fetch();

            if(!$lkrow){
                $sql = $pdo->prepare('INSERT INTO concertlike (ideaid, email) VALUES (:ideaid, :email)');
                $sql->bindParam(':ideaid', $_GET['i']);
                $sql->bindParam(':email', $_SESSION['email']);
                $sql->execute();
                $liked = 0;
            } else {
                $liked = $lkrow['lk'];
            }

            $sql = $pdo->prepare('SELECT * FROM concertlike WHERE ideaid != :ideaid AND email = :email AND lk = 1');
            $sql->bindParam(':ideaid', $_GET['i']);
            $sql->bindParam(':email', $_SESSION['email']);
            $sql->execute();
            
            $likeother = ($sql->fetch()) ? true : false;
        }
    } catch(Exception $e){
        alert('유효하지 않은 접근입니다.');
        errlog($e);
        redirect('idea');
        return;
    }

    $navbartitle = '연주회';
?>
<!DOCTYPE html>
<head>
    <?php include '../parts/head.php'?>
    <?php css("../assets/css/postview.css"); ?>
    <?php css("../assets/css/concert/idea_view.css"); ?>
</head>
<body>
    <header>
        <?php include '../parts/navbar.php'?>
        <?php include 'subnavbar.php'?>
        <script>makeSubNavFocus(1);</script>
    </header>
    <main>
        <div class="post-title border-bottom">
            <?php echo htmlspecialchars($row['title']) ?>
            <?php
                $delcond = ($config['active'] && (exec_auth(EXEC_CONCERT) || $row['email'] == $_SESSION['email']));
                $fixcond = ($row['email'] == $_SESSION['email'] && $config['active']);

                if($delcond || $fixcond){
                    echo '<div class="post-menu">
                                <button id="post-dropdown-btn">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                            <div id="post-dropdown" class="post-menu-dropdown" style="display: none">';

                    if($delcond) echo '<div id="post-delete-btn">삭제<i class="bi bi-trash-fill"></i></div>';
                    if($fixcond) echo '<a href="idea_write?i='.$row['id'].'">수정<i class="bi bi-pencil-fill"></i></a>';

                    echo '</div></div>';
                }
            ?>
        </div>
        <div class="theme-name">테마 이름: <?php echo htmlspecialchars($row['theme']); ?></div>
        <div class="main-post ql-editor">
            <?php echo purify_full($row['main']) ?>
        </div>
        <div class="post-option-wrapper">
            <?php
                if($sem < 4 && $config['active']){
                    $disabled = $likeother ? ' disabled' : '';

                    if($liked) echo '<button id="post-like-btn" class="post-like-btn btn-radius liked" '.$disabled.'><i class="bi bi-heart-fill"></i> '.$row['lk'].'</button>';
                    else echo '<button id="post-like-btn" class="post-like-btn btn-radius" '.$disabled.'><i class="bi bi-heart"></i> '.$row['lk'].'</button>';
                }

                if(exec_auth(EXEC_CONCERT)){
                    echo '<button class="notice-btn btn-radius" id="idea-elect-btn">테마 선정<i class="bi bi-check-lg"></i></button>';
                }
            ?>

            <!-- <button class="btn-white btn-radius" id="post-com-btn"><i class="bi bi-chat-dots"></i> <?php echo $row['comcnt']; ?></button> -->

            <a href="idea"><button class="btn-black btn-radius">목록 <i class="bi bi-list"></i></button></a>
        </div>
        <div class="songlist-wrapper border">
            <div class="songlist-title border-bottom"><i class="bi bi-music-note-list"></i> 연주 참가</div>
            <div class="songlist" id="songlist">
                <!-- 여기에 댓글을 불러옵니다 -->
                <?php echo load_song($_GET['i'], $_SESSION['email'])['content'];
                if($config['active']){
                    if(is_ob()){
                        echo '<div class="border-bottom ob-notyet text-grey">
                                지금은 투표 기간입니다! OB분들은 정식 신청 기간까지 기다려주세요 :)
                            </div>';
                    } else {
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
                }
                ?>
            </div>
        </div>
    </main>
    <footer>
        <?php include '../parts/footer.php'?>
    </footer>
    <input value="<?php echo $_GET['i'] ?>" id="id" style="display:none;">
    <input value="0" id="songid" style="display:none;">
    <input value="999999" id="inputpos" style="display:none;">
    <input value="0" id="hiddenpos" style="display:none;">
    <?php script("../assets/js/concert/idea_view.js"); ?>
</body>