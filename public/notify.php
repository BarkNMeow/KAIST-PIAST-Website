<?php
    require_once '../api/util.php';

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
        $sql = $pdo->prepare('SELECT COUNT(*) AS cnt FROM (SELECT MIN(view) AS view FROM notify WHERE email = :email GROUP BY type, descdata) tmp WHERE view = 0');
        $sql->bindParam(':email', $_SESSION['email']);
        $sql->execute();
        $cnt = ($sql->fetch())['cnt'];
    } catch(Exception $e){
        alert('요청 처리 중 오류가 발생했습니다.');
        errlog($e);
        return;
    }

    $navbartitle = '알림';
?>
<!DOCTYPE html>
<head>
    <?php include 'parts/head.php'?>
    <link href="../assets/css/notify.css" rel="stylesheet">
</head>
<body>
    <?php include 'parts/up.php'?>
    <header class="border-bottom">
        <?php include 'parts/navbar.php'?>
    </header>
    <main>
        <div class="notify-top-wrapper">
            <div>새로운 알림이 <span id="notify-cnt"><?php echo $cnt; ?></span>개 있습니다.</div>
            <div><button id="btn-notify-readall" class="btn-white btn-radius">모두 읽음</button></div>
        </div>
        <div id="notify-content" class="border-top mt-3">
            <div id="notify-arrow-repeat" class="border-bottom">
                <i class="bi bi-arrow-repeat text-grey"></i>
            </div>
        </div>
        <div class="btn-notify-add-wrapper">
            <button id="btn-notify-add" style="display: none;">더 보기</button>
        </div>
    </main>
    <footer>
        <?php include 'parts/footer.php'?>
    </footer>
    <?php script('../assets/js/notify.js'); ?>
    <script>setNotcnt(<?php echo $cnt; ?>)</script>
</body>
