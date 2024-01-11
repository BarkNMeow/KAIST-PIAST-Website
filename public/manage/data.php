<?php
    require_once '../../api/authquery.php';
    require_once '../../api/util.php';
    
    if(!logged_in() || !is_exec()) fast_redirect('../');

    global $pdo;
    try{
        $sql = $pdo->prepare('SELECT COUNT(*) AS jungmocnt FROM jungmoinfo');
        $sql->execute();
        $jungmocnt = ($sql->fetch())['jungmocnt'];

        $sql = $pdo->prepare("SELECT CONCAT(gen, '기 ', nm) AS gennm FROM userinfo WHERE
                (ascore < :ascore OR pscore < :pscore OR jmscore * 10 < :jungmocnt * 2 * :jmscore OR
                email IN (SELECT email FROM money WHERE duepaid < due OR finebillpaid < fine + bill))
                AND sem < 4 AND rest = 0 AND streak = 1");

        $sql->bindValue(':ascore', REQ_ASCORE);
        $sql->bindValue(':pscore', REQ_PSCORE);
        $sql->bindParam(':jungmocnt', $jungmocnt);
        $sql->bindValue(':jmscore', REQ_JMSCORE);
        $sql->execute();
        $fails = array_column($sql->fetchAll(), 'gennm');

        $sql = $pdo->prepare('SELECT * FROM (SELECT email, inittime, COUNT(*) - COUNT(inittime) AS leftcnt FROM timeinfo) tmp WHERE email = :email');
        $sql->bindParam(':email', $_SESSION['email']);
        $sql->execute();
        $row = $sql->fetch();

        $inittime = $row['inittime'];
        $leftcnt = $row['leftcnt'];

    } catch (Exception $e){
        errlog($e);
        alert('요청 처리 중 오류가 발생했습니다.');
        redirect('../');
        return;
    }
?>

<!DOCTYPE html>
<head>
    <?php include '../parts/head.php'?>
    <?php css('../assets/css/manage/manage.css'); ?>
    <?php css('../assets/css/manage/data.css'); ?>
</head>
<body>
    <header>
        <?php include '../parts/managenavbar.php'?>
        <div class="tab-btn-wrapper border-bottom">
            <div>
                <div class="tab-btn-menu-wrapper">
                    <button class="tab-btn selected" onclick="changeTab(0, this);">다운로드</button>
                </div>
                <div class="tab-btn-menu-wrapper">
                    <button class="tab-btn" onclick="changeTab(1, this);">무결성 검정</button>
                </div>
                <div class="tab-btn-menu-wrapper">
                    <button class="tab-btn" onclick="changeTab(2, this);">초기화</button>
                </div>
                <div id="tab-btn-border-bottom"></div>
            </div>
        </div>
    </header>
    <main>
        <script>makeSubNavFocus(4);</script> 
        <div class="content-wrapper">
            <div class="tab-container">
                <div class="backup-link-wrapper">
                    <a href="download?w=all" class="border" download>전체 활동 내역<i class="bi bi-file-earmark-spreadsheet-fill"></i></a>
                    <a href="download?w=p" class="border" download>피아노점수 목록<i class="bi bi-file-earmark-spreadsheet-fill"></i></a>
                    <a href="download?w=j" class="border" download>정모 출석 내역<i class="bi bi-file-earmark-spreadsheet-fill"></i></a>
                    <a href="download?w=m" class="border" download>회계 장부<i class="bi bi-file-earmark-spreadsheet-fill"></i></a>
                    <a href="download?w=s" class="border" download>활동인정 여부(미구현)<i class="bi bi-file-earmark-spreadsheet-fill"></i></a>
                    <a href="download?w=l" class="border" download>레슨 출석표(미구현)<i class="bi bi-file-earmark-spreadsheet-fill"></i></a>
                </div>
            </div>
            <div class="tab-container" style="display: none;">

            </div>
            <div class="tab-container" style="display: none;">
                <div class="content-warning mb-3">
                    초기화 시 다음과 같은 일들이 일어납니다:
                    <ul>
                        <li>정모/연주회/레슨/친목조 내역 초기화</li>
                        <li>피아노 점수/활동 점수/친목조 점수 초기화</li>
                        <li>레슨부장/친목조장 직위 해제</li>
                        <li>단, 테마 아이디어 투표 및 회계 내역은 보존됩니다.</li>
                        <?php 
                            if(count($fails)) echo '<li>또한, 다음 부원들이 회칙에 따라 <span class="warning-text">제명</span> 가능해집니다:<br>'.implode(', ', $fails).'</li>';
                        ?>
                    </ul>
                    초기화 하기 전 데이터가 백업되었는지 확인해주세요. <button class="btn-white btn-radius" onclick="changeTab(0, $('.tab-btn-menu-wrapper:first-child button'));">데이터 백업</button><br><br>
                    초기화가 이루어지기 위해서는 최초 발의 이후 3일 안에 <strong>임원진 전원의 동의</strong>가 필요합니다.<br>
                    초기화에 동의하시면 비밀번호를 입력하고 확인을 눌러주세요.
                </div>     
                <div class="password-form">
                    <?php
                        if($inittime && time() - strtotime($inittime) < 3 * 86400){
                            echo '초기화에 동의하셨습니다. '.$leftcnt.'명의 동의가 더 필요합니다.';
                        } else {
                            echo '비밀번호: 
                                <input type="password" id="password" placeholder="비밀번호" class="form-control password-input">
                                <button id="init-confirm-btn" class="btn-white btn-radius">확인</div>';
                        }
                    ?>
                </div>
            </div>
        </div>     
    </main>
    <?php script('../assets/js/manage/init.js'); ?>
    <footer>
        <?php include '../parts/footer.php'?>
    </footer>
</body>