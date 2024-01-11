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

    $pages =  get_page($_SESSION['gen'], 10, 0);
    $navbartitle = '주소록';
?>
<!DOCTYPE html>
<head>
    <?php include 'parts/head.php'?>
    <link href="../assets/css/phonebook.css" rel="stylesheet">
</head>
<body>
    <header class="border-bottom">
        <?php include 'parts/navbar.php'?>
    </header>
    <input type="hidden" id="gen" value="<?php echo $_SESSION['gen']?>"/>
    <main id="root">
        <div class="phonebook-tab">
            <button class="border selected"><i class="bi bi-telephone-fill"></i></button>
            <button class="border"><i class="bi bi-bookmark-star-fill"></i></button>
            <button class="border"><i class="bi bi-search"></i></button>
        </div>
        <div class="phonebook-wrapper border">
            <div id="page-left" class="shown">
                <?php echo $pages['left']; ?>
            </div>
            <div id="page-right">
                <?php echo $pages['right']; ?>
            </div>
            <div id="page-left-idx" class="border-top shown"><?php echo $pages['leftp']; ?></div>
            <div id="page-right-idx" class="border-top"><?php echo $pages['rightp']; ?></div> 
        </div>
    </main>
    <footer>
        <?php include 'parts/footer.php'?>
    </footer>
    <?php script('../assets/js/phonebook.js'); ?>
</body>
