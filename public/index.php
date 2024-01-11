<?php
    include_once '../api/util.php';

    if(logged_in()){
        fast_redirect('dashboard');
    }
?>
<!DOCTYPE html>
<head>
    <?php include 'parts/head.php'?>
    <link href="assets/css/index.css" rel="stylesheet">
</head>
<body>
    <div class="login-background">
        <div class="login-shadow">
            카이스트 유일의 피아노 동아리, 피아스트입니다<br>
            <button><a href="login">로그인 및 회원가입</a></button>
        </div>
    </div>
</body>