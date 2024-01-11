<?php
    include_once '../../api/jungmoquery.php';
    include_once '../../api/authquery.php';
    include_once '../../api/util.php';

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

        if(!is_exec()){
            alert('임원진만 접근 가능합니다!');
            redirect('../');
            return;
        }
    }

    if(exec_auth(EXEC_VICE)){
        $log = json_decode(file_get_contents('../../data/json/jungmocheckdelta.json'), true);
        $spliceend = 1;
        while($spliceend < count($log)){
            if(time() - $log[$spliceend]['timestamp'] < 3 * 86400){ // 3일 이상 지난 로그는 폐기
                break;
            }
            $spliceend += 1;
        }
    
        array_splice($log, 1, $spliceend - 1);
        $np = fopen('../../data/json/jungmocheckdelta.json', 'w');
        fwrite($np, json_encode($log, JSON_ENCODE));
        fclose($np);
    }

    // global $pdo;
    // try{
    //     // 정모 다음 날, 여전히 동비를 안냈다면 확정
    //     $sql = $pdo->prepare('UPDATE jungmochk j LEFT JOIN jungmoinfo info ON j.jungmoid = info.id LEFT JOIN money m ON j.email = m.email 
    //                         SET j.att = 4 WHERE j.att = 7 AND ADDTIME(info.date, \'24:00:00\') < CURRENT_TIMESTAMP AND (m.duepaiddate IS NULL OR info.date < m.duepaiddate)');
    //     $sql->execute();

    //     $sql = $pdo->prepare('UPDATE money m INNER JOIN 
    //             (SELECT SUM(
    //                 CASE WHEN (att = 2 || att = 3 || att = 7) THEN 0 
    //                 WHEN att = 1 THEN :latefine
    //                 ELSE :absentfine END) 
    //             AS finesum, email FROM jungmochk GROUP BY email) tmp ON m.email = tmp.email
    //             SET m.fine = tmp.finesum');

    //     $sql->bindValue(':latefine', FINE_JMLATE);
    //     $sql->bindValue(':absentfine', FINE_JMABSENT);
    //     $sql->execute();

    // } catch(Exception $e){
    //     errlog($e);
    //     alert('요청 처리 중 오류가 발생했습니다.');
    //     redirect('../');
    //     return;
    // }

    $navbartitle = '정모';
?>

<!DOCTYPE html>
<head>
    <?php include '../parts/head.php'?>
    <?php css("../assets/css/manageboard.css"); ?>
    <?php css("../assets/css/jungmo/check.css"); ?>
</head>
<body>
    <header>
        <?php include '../parts/navbar.php'?>
        <?php include 'subnavbar.php'?>
        <script>makeSubNavFocus(2);</script>
    </header>
    <main>
        <div class="table-container">
            <div class="table-option">
                <div>
                    <span id="jungmo-table-cnt">0</span>명이 선택되었습니다.
                </div>
                <div>
                    <button id="jungmo-autosave">자동 저장: 켜짐</button>
                </div>
                <div>
                    <button id="jungmo-update-btn">저장</button>
                    <button id="jungmo-init-btn">초기화</button>
                </div>
            </div>
            <div class="table noindex" id="jungmo-table">
                <div class="table-loading" id="jungmo-table-loading">
                    <i class="bi bi-arrow-repeat"></i>불러오는 중...
                </div>
                <div>이름</div>
                <div class="table-header-cell" id="jungmo-table-header">
                    
                </div>
                <div class="table-name"></div>
                <div class="table-no-result text-grey">불러오는 중...</div>
            </div>
        </div>
    </main>
    <?php
        script("../assets/js/jungmo/check.js");
    ?>
    <footer>
        <?php include '../parts/footer.php'?>
    </footer>
</body>