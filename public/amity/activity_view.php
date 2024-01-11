<?php
    require_once '../../api/util.php';
    require_once '../../api/amityquery.php';

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
        $sql = $pdo->prepare('SELECT * FROM amityactivity WHERE id = :id');
        $sql->bindParam(':id', $_GET['i']);
        $sql->execute();
        $row = $sql->fetch();

        if(!$row || $row['type'] != 0){
            alert('존재하지 않는 게시물입니다!');
            redirect('activity');
            return;
        }

        $sql = $pdo->prepare("SELECT CONCAT(gen, '기 ', nm) AS gennm, email FROM userinfo WHERE amitygroupid = :groupid");
        $sql->bindParam(':groupid', $row['groupid']);
        $sql->execute();
        $gennm = $sql->fetchAll();

        $sql = $pdo->prepare("SELECT CONCAT(b.gen, '기 ', b.nm) AS gennm, b.email, a.groupnm FROM amitygroup a LEFT JOIN userinfo b ON a.email = b.email WHERE a.id = :groupid");
        $sql->bindParam(':groupid', $row['groupid']);
        $sql->execute();
        $head = $sql->fetch();

        $sql = $pdo->prepare("SELECT * FROM amityActivityType WHERE visible = 1");
        $sql->execute();
        $activityType = $sql->fetchAll();

    } catch(Exception $e){
        alert('요청 처리 중 오류가 발생했습니다.');
        errlog($e);
        redirect('activity');
        return;
    }

    $attendemail = json_decode($row['who'], true);
    $attendgennm = array();
    foreach($gennm as $person){
        if(in_array($person['email'], $attendemail))
            array_push($attendgennm, $person['gennm']);
    }

    $imageList = json_decode($row['image'], true);
    $score = json_decode($row['scoreboard'], true);

    $config = json_decode(file_get_contents('../../data/json/amityscore.json'), true);
    $minn = $config['minn'];

    if(!isset($minn)){
        alert('최소 n이 설정되어 있지 않습니다!');
        redirect('activity');
        return;
    }

    $navbartitle = '친목조';
?>
<!DOCTYPE html>
<head>
    <?php include '../parts/head.php'?>
    <?php css("../assets/css/amity/activity_write.css"); ?>
    <?php css("../assets/css/amity/activity_view.css"); ?>
</head>
<body>
    <header>
        <?php include '../parts/navbar.php'?>
        <?php include 'subnavbar.php'?>
        <script>makeSubNavFocus(1);</script>
    </header>
    <main>
        <div class="post-title border-bottom">
            <?php 
                if($row['title']) echo $row['title'];
                else echo $row['date'].': 활동내역';

                $delcond = ($_SESSION['email'] == $head['email'] || exec_auth(EXEC_VICE));
                $fixcond = ($_SESSION['email'] == $head['email']);

                if($delcond || $notcond || $repcond || $fixcond){
                    echo '<div class="post-menu">
                            <button id="post-dropdown-btn">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <div id="post-dropdown" class="post-menu-dropdown" style="display: none">';
                    
                    if($delcond) echo '<div id="activity-delete-btn">삭제<i class="bi bi-trash-fill"></i></div>';
                    if($fixcond) echo '<a href="activity_write?i='.$_GET['i'].'">수정<i class="bi bi-pencil-fill"></i></a>';

                    echo '</div></div>';
                }
            ?>
        </div>
        <div class="post-info">
            <?php echo $row['date'].' / '.htmlspecialchars($head['groupnm']).' / '.implode(', ', $attendgennm); ?>
        </div>
        <div id="activity-image">
            <div class="image-ui">
                <div class="left-btn"><i class="bi bi-chevron-compact-left"></i></div>
                <div class="menu-wrapper">
                    <div class="menu">
                        <span><?php echo '1 / '.count($imageList); ?></span>
                        <?php
                            $link = '../../image/amity/'.array_keys($imageList)[0].$row['id'];
                            $name = str_replace('-', '_', $row['date']).'_'.htmlspecialchars($head['groupnm']).'.png';

                            echo '<a id="img-download-btn" href="'.$link.'" download="'.$name.'">';
                        ?>
                            <i class="bi bi-download" title="다운로드"></i>
                        </a>
                    </div>
                </div>
                <div class="right-btn"><i class="bi bi-chevron-compact-right"></i></div>
            </div>
            <?php
                $flag = true;
                $visibility = '';
                foreach($imageList as $hash => $dummy){
                    echo '<div class="image-viewer" style="'.$visibility.'background-image:url(../../image/amity/'.$hash.$row['id'].')"></div>';
                    if($flag){
                        $visibility = 'display: none; ';
                        $flag = false;
                    }   
                }
            ?>
        </div>
        <div class="main ql-editor">
            <?php echo purify_small($row['main']); ?>
        </div>
        <div class="score-table-container border">
            <div class="score-table-header">
                <i class="bi bi-trophy-fill"></i> 친목조 점수
                <?php
                    if(exec_auth(EXEC_VICE)){
                        if($row['accept']) echo '<button class="btn-radius btn-white" id="accept-score-btn">승인됨 <i class="bi bi-check-lg"></i></button>';
                        else echo '<button class="btn-radius btn-white" id="accept-score-btn">점수 승인</button>';
                    }
                ?>
            </div>
            <div id="score-table">
                <?php
                    $totalscore = 0;
                    array_push($score, array('event' => -1, 'n' => null, 'k' => null));
                    foreach($score as $event){
                        $eventid = $event['event'];
                        $n = $event['n'];
                        $k = $event['k'];
                        $score = _calc_score($eventid, $n, $k);

                        if(exec_auth(EXEC_VICE)){
                            echo '<div class="score-row">';
                            echo '<select name="select-event">';
                            echo '<option value="-1">활동을 선택하세요.</option>';
                            foreach($activityType as $one){
                                $desc = _get_desc($one['id']);
                                echo '<option value="'.$one['id'].'"'.(($eventid == $one['id']) ? ' selected' : '').'>'.$desc.'</option>';
                            }
                            echo '</select>';
                            echo '<input name="n" type="number" placeholder="n"'.($n ? ' value="'.$n.'"' : ' disabled').'>';
                            echo '<input name="k" type="number" placeholder="k"'.($k ? ' value="'.$k.'"' : ' disabled').'>';

                        } else {
                            if($eventid < 0) break;

                            echo '<div class="score-row">';
                            echo '<div>'._get_desc($eventid).'</div>';
                            echo '<div>'.$n.'</div>';
                            echo '<div>'.$k.'</div>';
                        }

                        echo '<div>=</div>';
                        echo '<div>'.($score > 0 ? $score : '?').'</div></div>';

                        $totalscore += max($score, 0);
                    }
                ?>
            </div>
            <div id="score-total" <?php echo ($row['accept'] ? '' : 'class="text-grey"')?>>
                <div>총 점수:</div>
                <div id="activity-score-total"><?php echo $totalscore; ?></div>
            </div>
        </div>
        <div class="btn-list-wrapper">
            <?php
                $get = array();
                if(isset($_GET['pp'])) array_push($get, 'pp='.$_GET['pp']);
                if(isset($_GET['p'])) array_push($get, 'p='.$_GET['p']);
                if(isset($_GET['a'])) array_push($get, 'a='.$_GET['a']);
                if(isset($_GET['t'])) array_push($get, 't='.$_GET['t']);
                if(isset($_GET['g'])) array_push($get, 'g='.$_GET['g']);

                if(count($get) > 0) $get = '?'.implode('&', $get);
                else $get = '';
                echo '<a href="activity'.$get.'"><button class="btn-black btn-radius"><i class="bi bi-justify"></i> 목록으로</button></a>';
            ?>
        </div>
        <input id="id" value="<?php echo $_GET['i']?>" style="display: none">
        <?php script("../assets/js/amity/activity_core.js"); ?>
        <?php script("../assets/js/amity/activity_view.js"); ?>
        <script>getScoreboard(<?php echo json_encode($activityType, JSON_ENCODE); ?>)</script>
    </main>
    <footer>
        <?php include '../parts/footer.php'?>
    </footer>
</body>