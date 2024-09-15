<?php
    include_once '../api/util.php';

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
        $sql = $pdo->prepare('SELECT a.att, b.date FROM jungmochk a LEFT JOIN jungmoinfo b ON a.jungmoid = b.id WHERE a.email = :email ORDER BY b.date ASC');
        $sql->bindParam(':email', $_SESSION['email']);
        $sql->execute();
        $jungmochk = $sql->fetchAll();
        $jungmocnt = count($jungmochk);
        $attcnt = 0;

        foreach($jungmochk as $one){
            if($one['att'] == 2) $attcnt += 2;
            else if($one['att'] == 1) $attcnt += 1;
        }

        $attcnt /= 2.0;

        $sql = $pdo->prepare('SELECT * FROM userinfo WHERE email = :email');
        $sql->bindParam(':email', $_SESSION['email']);
        $sql->execute();
        $activity = $sql->fetch();

        $scorea = array();
        $scorep = array();

        if(is_lesson_teacher()) array_push($scorea, array('레슨부장', 2));
        if(is_amity_leader()) array_push($scorea, array('친목조장', 2));

        $sql = $pdo->prepare('SELECT COUNT(*) AS cnt FROM lessonstudent WHERE email = :email AND att >= :reqatt');
        $sql->bindParam(':email', $_SESSION['email']);
        $sql->bindValue(':reqatt', REQ_LESSON_ATT);
        $sql->execute();
        $lessonatt = ($sql->fetch())['cnt'];
        if($lessonatt) array_push($scorea, array('레슨 수료', $lessonatt));

        $amityatt =  $activity['amityatt'];
        if($amityatt >= REQ_AMITY_ATT2) array_push($scorea, array('친목조 참여', 2));
        else if($amityatt >= REQ_AMITY_ATT1) array_push($scorea, array('친목조 참여', 1));

        $sql = $pdo->prepare('SELECT ideaid FROM concertsong WHERE ideaid <= 0 AND email = :email GROUP BY ideaid');
        $sql->bindParam(':email', $_SESSION['email']);
        $sql->execute();
        $id = array_column($sql->fetchAll(), 'ideaid');

        foreach($id as $i){
            if($i == 0) array_push($scorep, array('정기연주회 참여', 2));
            else if($i == -1) array_push($scorep, array('테마연주회 참여', 2));
        }

        $sql = $pdo->prepare('SELECT COUNT(*) AS cnt FROM jungmopost WHERE email = :email');
        $sql->bindParam(':email', $_SESSION['email']);
        $sql->execute();
        
        $jungmoplay = ($sql->fetch())['cnt'];

        if($jungmoplay) array_push($scorep, array('정모 연주', $jungmoplay));

        $sql = $pdo->prepare('SELECT * FROM scoreinfo a LEFT JOIN scorelist b ON a.id = b.scoreid WHERE b.email = :email');
        $sql->bindParam(':email', $_SESSION['email']);
        $sql->execute();
        
        $rows = $sql->fetchAll();

        foreach($rows as $one){
            if($one['info'] === '') $desc = $one['tmi'];
            else $desc = $one['info'].($one['tmi'] ? '('.$one['tmi'].')' : '');

            if($one['type'] == 0) array_push($scorea, array($desc, $one['quantity']));
            else array_push($scorep, array($desc, $one['quantity']));
        }

        $sql = $pdo->prepare('SELECT * FROM money WHERE email = :email');
        $sql->bindParam(':email', $_SESSION['email']);
        $sql->execute();
        $money = $sql->fetch();

    } catch(Exception $e){
        alert('요청 처리 중 오류가 발생했습니다.');
        errlog($e);
        redirect('../');
        return;
    }

    $moneyconfig = json_decode(file_get_contents(dirname(__DIR__).'/data/json/moneyconfig.json'), true);
    $navbartitle = '내 활동';
?>

<!DOCTYPE html>
<head>
    <?php include 'parts/head.php'?>
    <?php css('../assets/css/myactivity.css'); ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js" integrity="sha512-TW5s0IT/IppJtu76UbysrBH9Hy/5X41OTAbQuffZFU6lQ1rdcLHzpU5BzVvr/YFykoiMYZVWlr/PX1mDcfM9Qg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
</head>
<body>
    <header class="border-bottom">
        <?php include 'parts/navbar.php'?>
    </header>
    <main>
        <div class="tab-btn-wrapper border-bottom">
            <div class="tab-btn-menu-wrapper">
                <button class="tab-btn selected" onclick="changeTab(0, this);">요약</button>
            </div>
            <div class="tab-btn-menu-wrapper">
                <button class="tab-btn" onclick="changeTab(1, this);">활동 내역</button>
            </div>
            <div class="tab-btn-menu-wrapper">
                <button class="tab-btn" onclick="changeTab(2, this);">정산</button>
            </div>
            <div id="tab-btn-border-bottom"></div>
        </div>
        <div class="tab-container">
            <div class="history-container border">
                <div class="history-sem">
                    <div class="history-title">활동 인정</div>
                    <div class="history-value">
                        <?php
                            $sem_pass = true;
                            if(!is_ob()){
                                if($activity['ascore'] < REQ_ASCORE) $sem_pass = false;
                                if($activity['pscore'] < REQ_PSCORE) $sem_pass = false;
                                if($activity['jmscore'] * 10 < $jungmocnt * REQ_JMSCORE) $sem_pass = false;
                                if($money['duepaid'] + $money['finebillpaid'] < $money['due'] + $money['bill'] + $money['fine']) $sem_pass = false;
                            }

                            echo ($sem_pass ? '예' : '아니요');
                        ?>
                    </div>
                </div>
                <div>
                    <div>
                        <div class="history-title">활동 인정 학기</div>
                        <div class="history-value"><?php echo $activity['sem']; ?>학기</div>
                    </div>
                    <div>
                        <div class="history-title">연속 미인정 학기</div>
                        <div class="history-value"><?php echo $activity['streak']; ?>학기</div>
                    </div>
                    <div>
                        <div class="history-title">휴동 횟수</div>
                        <div class="history-value"><?php echo $activity['restcnt']; ?>학기</div>
                    </div>
                    <div>
                        <div class="history-title">휴동 상태</div>
                        <div class="history-value">
                        <?php
                            if($activity['rest'] == 2) echo '예(정당한 사유)';
                            else if($activity['rest'] == 1) echo '예(무단 휴동)';
                            else echo '아니요'; 
                        ?>
                        </div>
                    </div>
                    <div>
                        <div class="history-title">신입부원</div>
                        <div class="history-value"><?php echo ($activity['sem'] + $activity['streak'] + $activity['restcnt']) ? '아니요' : '예'; ?></div>
                    </div>
                    <div>
                        <div class="history-title">동연 집행부원</div>
                        <div class="history-value"><?php echo $activity['cuexec'] ? '예' : '아니요'; ?></div>
                    </div>
                </div>
            </div>
            <div class="activity-summary-container border">
                <div class="graph-container">
                    <canvas id="charta"></canvas>
                </div>
                <div class="graph-container">
                    <canvas id="chartp"></canvas>
                </div>
                <div class="graph-container">
                    <canvas id="chartj"></canvas>
                </div>
                <div class="graph-container">
                    <canvas id="chartb"></canvas>
                </div>
            </div>
        </div>
        <div class="tab-container scorelist-container" style="display: none;">
            <div class="border">
                <div class="scorelist-jm">
                    <div class="scorelist-jm-title">정모 출석</div>
                    <div>
                        <?php
                            if(!is_ob()){
                                echo '0 / 0 <span class="text-grey">(&ge; 0)</span>';
                            } else {
                                echo round($attcnt, 1).' / '.$jungmocnt.' <span class="text-grey">(&ge; '.round($jungmocnt * REQ_JMSCORE / 10, 1).')</span>';
                            }
                        ?>
                    </div>
                </div>
                <?php
                    if(is_ob()){
                        echo '<div class="scorelist-jm-table ob text-grey">ob는 정모 출석 내역이 표시되지 않습니다 😥</div>';
                    } else {
                        echo '<div class="scorelist-jm-table">';
                        $icon = array('<i class="bi bi-x-lg"></i>', '<i class="bi bi-triangle"></i>', '<i class="bi bi-circle"></i>', '-');
                        foreach($jungmochk as $one){
                            echo '<div>
                                    <div class="scorelist-jm-head">'.str_replace('-', '/', substr($one['date'], 5)).'</div>
                                    <div class="scorelist-jm-body '.($one['att'] >= 4 ? 'text-grey':'').'">'.$icon[$one['att'] % 4].'</div>
                                </div>';
                        }
                        echo '</div>';
                    }
                ?>
            </div>
            <div class="border">
                <div class="scorelist-title border-bottom">
                    <div>활동 점수</div>
                    <div><?php echo $activity ? $activity['ascore'].'점 / <span class="text-grey">'.REQ_ASCORE.'점</span>' : '정보 없음'; ?></div>
                </div>
                <?php
                    if($scorea){
                        foreach($scorea as $one){
                            echo '<div class="scorelist-row"><div>'.$one[0].'</div><div>'.$one[1].'점</div></div>';
                        }
                    } else {
                        echo '<div class="scorelist-norow text-grey"><div>🤔</div>아직 활동 내역이 없군요...</div>';
                    }
                ?>
            </div>
            <div class="border">
                <div class="scorelist-title border-bottom">
                    <div>피아노 점수</div>
                    <div><?php echo $activity ? $activity['pscore'].'점 / <span class="text-grey">'.REQ_PSCORE.'점</span>' : '정보 없음'; ?></div>
                </div>
                <?php
                    if($scorep){
                        foreach($scorep as $one){
                            echo '<div class="scorelist-row"><div>'.$one[0].'</div><div>'.$one[1].'점</div></div>';
                        }
                    } else {
                        echo '<div class="scorelist-norow text-grey"><div>🤔</div>아직 활동 내역이 없군요...</div>';
                    }
                ?>
            </div>
        </div>
        <div class="tab-container" style="display: none;">
            <div class="bankacc-container border">
                <div>입금 계좌: <?php echo htmlspecialchars($moneyconfig['accountnum']); ?></div>
            </div>
            <div class="due-container border">
                <div>회비</div>
                <div class="border-right" <?php echo $money['duepaiddate'] ? '' : 'style="color: var(--nored)"' ?>>
                    <?php echo ($money['due'] - $money['duepaid'])."원 미납(총 {$money['due']}원)" ?>
                </div>
                <div>동비</div>
                <div <?php echo $money['duepaiddate'] ? '' : 'style="color: var(--nored)"' ?>>
                    <?php echo ($money['due'] - $money['duepaid'])."원 미납(총 {$money['due']}원)" ?>
                </div>
            </div>
            <div class="border">
                <div>정산 및 벌금</div>
                <div>
                    <div>청구 일자 여기</div>
                    <div>납부해야하는 금액 여기</div>
                </div>
            </div>
        </div>
    </main>
    <footer>
        <?php include 'parts/footer.php'?>
    </footer>
    <script>
        const maxa = <?php echo REQ_ASCORE ?>;
        const maxp = <?php echo REQ_PSCORE ?>;
        const maxj = <?php echo REQ_JMSCORE ?>;
        const maxb = <?php echo $money['due'] + $money['bill'] + $money['fine'] ?>;

        const a = <?php echo $activity['ascore']?>;
        const p = <?php echo $activity['pscore']?>;
        const j = <?php echo $activity['jmscore']?>;
        const b = <?php echo $money['duepaid'] + $money['finebillpaid'] ?>;
    </script>
    <?php script('assets/js/myactivity.js'); ?>
</body>