<?php
    require_once '../api/util.php';

    if(!UNDER_MAINTENANCE) fast_redirect('./');
?>
<!DOCTYPE html>
<head>
    <?php include 'parts/head.php'?>
</head>
<body>
    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 70vh; min-height: 350px; padding: 3rem 0">
        <img src="assets/img/fix.png"></img>
        <div style="font-weight: var(--medium); font-size: 1.5rem;">PIAST 웹사이트는 현재 수리중입니다!</div>
        <div style="margin-top: .5rem;">점검 사유: DB 구조 변경<br>예상 점검 시간: 몰루</div>
        <div style="margin: 1.25rem 0;">
            <a href="./"><button class="btn-white btn-radius">처음으로 돌아가기</button></a>
        </div>
    </div>
</body>