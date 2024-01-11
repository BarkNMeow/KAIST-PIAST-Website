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
        $sql = $pdo->prepare('SELECT sem from userinfo WHERE email = :email');
        $sql->bindParam(':email', $_SESSION['email']);
        $sql->execute();
        
        $sem = ($sql->fetch())['sem'];

    } catch(Exception $e){
        alert('유효하지 않은 접근입니다.');
        errlog($e);
        redirect('../');
        return;
    }

    $config = updateIdeaConfig();
    $navbartitle = '연주회';
?>
<!DOCTYPE html>
<head>
    <?php include '../parts/head.php'?>
    <?php css("../assets/css/concert/idea.css"); ?>
</head>
<body>
    <?php
        if(exec_auth(EXEC_CONCERT)){
            $disable = ($config['active'] ? ' disabled' : '');
            echo '<div class="overlay-shadow" style="display: none" id="overlay" class="overlay-shadow">
                    <div class="overlay-wrapper">
                        <h1>시간 설정</h1>
                        <div>
                            <span class="option-name">투표 시작</span>
                            <input type="date" id="start-date"'.$disable.'>
                            <input type="time" id="start-time"'.$disable.'>
                            '.($config['active'] ? '' : '<button id="start-init" class="btn-radius">X</button>').'
                        </div>
                        <div>
                            <span class="option-name">투표 종료</span>
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
    <header>
        <?php include '../parts/navbar.php'?>
        <?php include 'subnavbar.php'?>
        <script>makeSubNavFocus(1);</script>
    </header>
    <main>
        <div class="header-wrapper">
            <span><i class="bi bi-file-earmark-check"></i> 후보</span><br>
            <?php
                if(exec_auth(EXEC_CONCERT)) echo '<div id="concert-vote-timer" class="vote-setting"></div>';
                else echo '<div id="concert-vote-timer"></div>';
            ?>
        </div>
        <?php
            $result = load_idea(0, 0);
            echo $result['content'];
        ?>
        <div class="bottom-wrapper">
            정렬:
            <select id="sort-criteria">
                <option value="0">점수</option>
                <option value="1">좋아요</option>
                <option value="2">참가자</option>
            </select>
            <select id="sort-order">
                <option value="0">내림차순</option>
                <option value="1">오름차순</option>
            </select>
            <?php
                if($config['active'] && $sem < 4){
                    echo '<a href="idea_write"><button class="btn-white btn-radius">테마 제안</button></a>';
                }
                if($config['start'] == -1){
                    echo '<button class="btn-init btn-radius">초기화</button>';
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
    <?php script("../assets/js/concert/idea.js"); ?>
</body>