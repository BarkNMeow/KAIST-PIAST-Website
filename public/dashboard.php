<?php
    require_once '../api/util.php';

    if(!logged_in()){
        $name = $_SERVER['PHP_SELF'];
        $name = str_replace('.php', '', $name);
        fast_redirect('login?r='.$name);
        return;
    }

    if(UNDER_MAINTENANCE && !is_dev()){
        fast_redirect('fixing');
        return;
    }

    global $pdo;
    try{
        $sql = $pdo->prepare('SELECT id, title FROM bbs WHERE bbstype >= 0 ORDER BY id DESC LIMIT 8');
        $sql->execute();
        $notice = $sql->fetchAll();

        $sql = $pdo->prepare('SELECT phonenum, maj, yr, bday FROM userinfo WHERE email = :email');
        $sql->bindParam(':email', $_SESSION['email']);
        $sql->execute();
        $userinfo = $sql->fetch();

        $sql = $pdo->prepare('SELECT * FROM userinfo WHERE email = :email');
        $sql->bindParam(':email', $_SESSION['email']);
        $sql->execute();
        $activity = $sql->fetch();

        $sql = $pdo->prepare('SELECT * FROM money WHERE email = :email');
        $sql->bindParam(':email', $_SESSION['email']);
        $sql->execute();
        $money = $sql->fetch();

        $sql = $pdo->prepare('SELECT f.*, b.title, s.composer FROM file f LEFT JOIN bbs b ON f.postid = b.id LEFT JOIN bbsscore s ON b.id = s.postid WHERE b.bbstype = :score ORDER BY b.id DESC LIMIT 8');
        $sql->bindValue(':score', BBS_SCORE);
        $sql->execute();
        $scorelist = $sql->fetchAll();

        $sql = $pdo->prepare('SELECT id, image FROM bbs WHERE bbstype = :gallery ORDER BY id DESC LIMIT 12');
        $sql->bindValue(':gallery', BBS_GALLERY);
        $sql->execute();
        $gallerylist = $sql->fetchAll();

    } catch(Exception $e){
        errlog($e);
        alert('요청 처리 중 오류가 발생했습니다.');
        return;
    }
?>
<!DOCTYPE html>
<head>
    <?php require 'parts/head.php'?>
    <?php css('assets/css/dashboard.css'); ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js" integrity="sha512-TW5s0IT/IppJtu76UbysrBH9Hy/5X41OTAbQuffZFU6lQ1rdcLHzpU5BzVvr/YFykoiMYZVWlr/PX1mDcfM9Qg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
</head>
<body>
    <header class="border-bottom">
        <?php include 'parts/navbar.php' ?>
    </header>
    <main>
        <div class="welcome border">👋 환영합니다, <?php echo $_SESSION['gen'].'기 '.$_SESSION['nm']; ?>님!</div>
        <div class="board-grid">
            <div class="border recommend">
                <div class="title">제안</div>
                <div class="recommend-wrapper">
                <?php
                    $flag = true;
                    if(!is_ob()){
                        if($activity['ascore'] < REQ_ASCORE){
                            echo '<div class="recommend-title">활동 점수가 부족합니다!
                                <div class="recommend-desc">
                                    <p>활동 인정을 위해서는 활동점수 '.REQ_ASCORE.'점이 필요합니다.</p>
                                    <p>뒤풀이 참여, 연주회 스태프, 단체 행사 참여, 게릴라 이벤트 등을 통해 활동 점수를 노리세요!</p>
                                </div>
                            </div>';
                            $flag = false;
                        }


                        if($activity['pscore'] < REQ_PSCORE){
                            echo '<div class="recommend-title">피아노 점수가 부족합니다!
                                <div class="recommend-desc">
                                    <p>활동 인정을 위해서는 피아노점수 '.REQ_PSCORE.'점이 필요합니다.</p>
                                    <p>정모에 신청해서 피아노 점수를 얻어가세요!</p>
                                    <a href="jungmo/apply">정모 신청 <i class="bi bi-chevron-right"></i></a>
                                </div>
                            </div>';
                            $flag = false;
                        }


                        if($money['due'] > $money['duepaid'] and $_SESSION['gen'] < 15){
                            echo '<div class="recommend-title">회비가 납부되지 않았습니다!
                                <div class="recommend-desc">
                                    <p>활동 인정을 위해서는 반드시 회비를 납부해야 합니다.</p>
                                    <p>또한, 회비를 납부하기 전까지 모든 정모는 결석처리 됩니다.</p>
                                    <p>잊기 전에 빨리 납부하는 것이 좋겠죠?</p>
                                    <a href="myactivity">내 활동 <i class="bi bi-chevron-right"></i></a>
                                </div>
                            </div>';
                            $flag = false;
                        }


                        if($money['fine'] + $money['bill'] > $money['finebillpaid'] and $_SESSION['gen'] < 15){
                            echo '<div class="recommend-title">납부하지 않은 돈이 있습니다!
                                <div class="recommend-desc">
                                    <p>총무가 울고있습니다 😭😭</p>
                                    <p>어서 벌금/정산을 확인해주세요!</p>
                                    <a href="myactivity">내 활동 <i class="bi bi-chevron-right"></i></a>
                                </div>
                            </div>';
                            $flag = false;
                        }


                        if(!isset($userinfo['phonenum']) || !isset($userinfo['bday']) || !$userinfo['maj'] || !isset($userinfo['yr'])){
                            echo '<div class="recommend-title">입력하지 않은 정보가 있습니다!
                                <div class="recommend-desc">
                                    <p>다른 부원들이 본인에 대해 더 많은 것을 알 수 있도록 잠시 시간을 내어 정보를 입력해주세요!</p>
                                    <a href="myaccount">내 계정 <i class="bi bi-chevron-right"></i></a>
                                </div>
                            </div>';
                            $flag = false;
                        }
                    }

                    if($flag){
                        echo '<div class="no-recommend text-grey">
                            <div>🤔</div>
                            <p>딱히 제안할 내용이 없군요. </p>
                            <p>해야할 일을 모두 했다는 뜻입니다!</p>
                        </div>';
                    }
                    
                ?>
                </div>
            </div>
            <div class="border notice">
                <div class="title">
                    게시판
                    <a class="seemore" href="bbs/list?b=0">모두 보기...</a>
                </div>
                <div class="notice-wrapper">
                    <?php
                        foreach($notice as $one){
                            echo '<a href="bbs/view?i='.$one['id'].'">'.$one['title'].'</a>';
                        }
                    ?>
                </div>
            </div>
            <div class="border score">
                <div class="title">
                    PIAST 악보
                    <a class="seemore" href="score/list">모두 보기...</a>
                </div>
                <div class="score-list">
                    <?php
                        foreach($scorelist as $score){
                            echo '<a href="score/view?i='.$score['postid'].'">';
                                echo '<div class="thumbnail" style="background-image: url(image/scorethumbnail/'.$score['downloadhash'].'0'.$score['id'].')"></div>';
                                echo '<div class="score-title">'.$score['title'].'</div>';
                                echo '<div class="score-composer">'.$score['composer'].'</div>';
                            echo '</a>';
                        }

                        echo '<a href="score/list" class="score-more text-grey border">
                                <div>더 보기</div>
                                <button><i class="bi bi-arrow-right"></i></button>
                            </a>'
                    ?>
                </div>
            </div>
            <div class="border gallery">
                <div class="title">
                    갤러리
                    <a class="seemore" href="bbs/list?b=<?php echo BBS_GALLERY; ?>">모두 보기...</a>
                </div>
                <div class="gallery-list">
                    <?php
                        $cnt = 0;
                        foreach($gallerylist as $gallery){
                            $hash = substr($gallery['image'], 2, 40);
                            echo '<a href="bbs/view?b=4&i='.$gallery['id'].'" style="background-image: url(image/postthumbnail/'.$hash.')"></a>';
                            $cnt++;
                        }
                        
                        for($i = 0; $i < 6 - $cnt; $i++){
                            echo '<div class="no-photo"></div>';
                        }

                    ?>
                </div>
            </div>
            <div class="border calendar">
                <div class="title">일정</div>
                <iframe src="https://calendar.google.com/calendar/embed?height=600&wkst=1&bgcolor=%23ffffff&ctz=Asia%2FSeoul&showTitle=0&showPrint=0&showTabs=0&showCalendars=0&showTz=0&showDate=1&src=cGlhc3Qua2Fpc3RAZ21haWwuY29t&src=a28uc291dGhfa29yZWEjaG9saWRheUBncm91cC52LmNhbGVuZGFyLmdvb2dsZS5jb20&color=%237986CB&color=%237986CB" style="border-width:0" width="800" height="600" frameborder="0" scrolling="no"></iframe>
            </div>
        </div>
    </main>    
    <footer>
        <?php include 'parts/footer.php'?>
    </footer>
</body>