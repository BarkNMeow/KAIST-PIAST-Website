<?php
    require_once '../api/util.php';
    require_once '../api/phonebookquery.php';

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

    $navbartitle = '주소록';
?>
<!DOCTYPE html>
<head>
    <?php include 'parts/head.php'?>
    <?= vite("address_book.tsx") ?>
</head>
<body>
    <header class="border-bottom">
        <?php include 'parts/navbar.php'?>
    </header>
    <input type="hidden" id="gen" value="<?php echo $_SESSION['gen']?>"/>
    <main id="root">
    </main>
    <footer>
        <?php include 'parts/footer.php'?>
    </footer>
</body>
