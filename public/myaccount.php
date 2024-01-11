<?php
    include_once '../api/authquery.php';
    include_once '../api/util.php';
    
    if(!logged_in()) fast_redirect('/');

    global $pdo;
    try{
        $sql = $pdo->prepare('SELECT * FROM userinfo WHERE email = :email');
        $sql->bindParam(':email', $_SESSION['email']);
        $sql->execute();
        $row = $sql->fetch();

    } catch (Exception $e){

    }

    $navbartitle = '내 계정';
?>

<!DOCTYPE html>
<head>
    <?php include 'parts/head.php'?>
    <link href="../assets/css/myaccount.css" rel="stylesheet">
</head>
<body>
    <header class="border-bottom">
        <?php include 'parts/navbar.php'?>
    </header>
    <main>
        <div class="account-auth-wrapper border"> 
            <?php
                if(is_auth()) echo '<i class="bi bi-check-lg"></i> 가입이 승인된 계정입니다!';
                else echo '<i class="bi bi-x-lg"></i> 아직 가입이 승인되지 않았습니다.';
            ?>
        </div>
        <div class="account-wrapper">
            <div class="account-info-wrapper border">
                <div class="account-title">
                    계정 정보 변경
                </div>
                <div>학번/학과</div>
                <div>
                    <input id="info-yr" value="<?php echo $row['yr']; ?>" maxlength="2">학번
                    <select id="info-maj">
                        <option value="0"<?php if($row['maj'] == 0) echo ' selected'?>>전공 선택</option>
                        <?php
                            $fp = fopen('../data/text/majlist.csv', 'r');
                            $i = 1;
                            while(($data = fgetcsv($fp)) !== false){
                                echo '<option value="'.$i.'"'.($row['maj'] == $i ? ' selected' : '').'>'.$data[0].'</option>';
                                $i += 1;
                            }
                        ?>
                    </select>
                </div>
                <div>생년월일</div>
                <div>
                    <?php
                        $bday = explode('-', $row['bday']);
                    ?>
                    <input id="info-by" placeholder="YYYY" type="number" value="<?php echo $bday[0]; ?>">년
                    <input id="info-bm" placeholder="MM" type="number" value="<?php echo $bday[1]; ?>">월
                    <input id="info-bd" placeholder="DD" type="number" value="<?php echo $bday[2]; ?>">일
                </div>
                <div>전화번호</div>
                <div>
                    <input id="info-phonenum" placeholder="전화번호" type="number" value="<?php echo $row['phonenum']; ?>">
                </div>
                <div>
                    <button class="btn-black btn-radius" id="info-update-btn">변경</button>
                </div>
            </div>
            <div class="account-info-wrapper border">
                <div class="account-title">
                    비밀번호 변경
                </div>
                <div>이전 비밀번호</div>
                <div>
                    <input id="passwd-old" type="password" placeholder="이전 비밀번호" autocomplete="current-password">
                </div>
                <div>새로운 비밀번호</div>
                <div>
                    <input id="passwd-new" type="password" placeholder="새로운 비밀번호" autocomplete="new-password">
                </div>
                <div></div>
                <div>
                    <meter></meter>
                </div>
                <div>
                    <button id="passwd-update-btn" class="btn-black btn-radius" disabled>변경</button>
                </div>
            </div>
        </div>
        <?php script('../assets/js/myaccount.js'); ?>
    </main>
    <footer>
        <?php include 'parts/footer.php'?>
    </footer>
</body>