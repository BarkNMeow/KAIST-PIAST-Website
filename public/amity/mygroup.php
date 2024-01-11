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
        $sql = $pdo->prepare('SELECT g.id, g.groupnm, g.score, u.email, CONCAT(u.gen, \'기 \', u.nm) AS gennm FROM 
                            amitygroup g LEFT JOIN userinfo u ON g.id = u.amitygroupid
                            WHERE g.id IN (SELECT amitygroupid FROM userinfo WHERE email = :email) ORDER BY u.amity DESC, u.gen ASC, u.nm ASC');
        $sql->bindParam(':email', $_SESSION['email']);
        $sql->execute();
        $rows = $sql->fetchAll();

        if(!$rows){
            alert('속해있는 친목조가 없습니다.');
            echo "<script>window.href.location = history.go(-1)</script>";
            return;
        }

        $groupid = $rows[0]['id'];
        $groupnm = $rows[0]['groupnm'];
        $totalscore = $rows[0]['score'];

        $sql = $pdo->prepare('SELECT date, id, who, scoreboard, score, accept, type FROM amityactivity WHERE groupid = :groupid');
        $sql->bindParam(':groupid', $groupid);
        $sql->execute();
        $activities = $sql->fetchAll();

        $sql = $pdo->prepare('SELECT 1 + COUNT(*) as rank FROM amitygroup WHERE score > (SELECT score FROM amitygroup WHERE id = :groupid)');
        $sql->bindParam(':groupid', $groupid);
        $sql->execute();
        $rank = ($sql->fetch())['rank'];

        $sql = $pdo->prepare('SELECT * FROM amityActivityType where visible = 1');
        $sql->execute();
        $scorehow = $sql->fetchAll();

        $scorechart_activity = array_fill(0, count($scorehow), 0);
        $scorechart_other = array_fill(0, 3, 0);
        $labellist = array_merge(array_column($scorehow, 'descr'), array('날갱', '친목조 게임', '기타'));

        $attenddate = array();
        $attendrow = array();
        foreach($rows as $one){
            $attendrow[$one['email']] = array('gennm' => $one['gennm'], 'echostr' => '');
        }

        foreach($activities as $activity){
            $scoreboard = json_decode($activity['scoreboard'], true);
            if($activity['type'] == 0){
                if($activity['accept']){
                    foreach($scoreboard as $score){
                        $scorechart_activity[$score['event']] += _calc_score($score['event'], $score['n'], $score['k']);
                    }
                }

                array_push($attenddate, array($activity['id'], YMDtoMD($activity['date'])));
                $attendlist = json_decode($activity['who'], true);
                
                foreach($attendrow as $email => $dummy){
                    if(in_array($email, $attendlist))
                        $attendrow[$email]['echostr'] .= '<div><i class="bi bi-circle"></i></div>';
                    else
                        $attendrow[$email]['echostr'] .= '<div></div>';
                }

            } else {
                $id = ($activity['type'] < 0 ? 2 : $activity['type'] - 1);
                $scorechart_other[$id] += $activity['score'];
            }
        }
        $scorelist = array_merge($scorechart_activity, $scorechart_other);

    } catch (Exception $e){
        alert('요청 처리 중 오류가 발생했습니다.');
        errlog($e);
        redirect('../');
        return;
    }

    $navbartitle = '친목조';
?>
<!DOCTYPE html>
<head>
    <?php include '../parts/head.php'?>
    <?php css("../assets/css/board.css"); ?>
    <?php css("../assets/css/amity/mygroup.css"); ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js" integrity="sha512-TW5s0IT/IppJtu76UbysrBH9Hy/5X41OTAbQuffZFU6lQ1rdcLHzpU5BzVvr/YFykoiMYZVWlr/PX1mDcfM9Qg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
</head>
<body>
    <header>
        <?php include '../parts/navbar.php'?>
        <?php include 'subnavbar.php'?>
        <script>makeSubNavFocus(2);</script>
    </header>
    <main>
        <div class="groupinfo-wrapper border">
            <div class="groupnm">
                <?php echo '<span id="name-span">'.htmlspecialchars($groupnm).'</span>';
                    if($rows[0]['email'] == $_SESSION['email']) {
                        echo '<input style="display: none" maxlength="20" id="name-fix-input" placeholder="조 이름 입력">';
                        echo '<i class="bi bi-pencil-fill" id="name-fix-btn"></i>';
                    }
                ?>
            </div>
            <div class="groupinfo-list">
                <div class="title">🐔</div> 
                <div><?php echo $rows[0]['gennm']; ?></div>
                <div class="title">🏆</div>
                <div><?php echo $totalscore; ?>점</div>
                <div class="title">🏅</div>
                <div><?php echo $rank; ?>위</div>
            </div>
            <div class="groupinfo-list">
                <div class="title">🐤</div> 
                <div> 
                <?php
                    $memberrow = array_splice(array_column($attendrow, 'gennm'), 1);
                    if(count($memberrow)) echo '<span class="nowrap">'.implode('</span>, <span class="nowrap">', $memberrow).'</span>';
                    else echo '없음';
                ?>
                </div>
            </div>
        </div>
        <div class="border" id="chart-block">
            <span class="box-title-small">친목 점수</span>
            <?php
                if($totalscore > 0){
                    echo '
                    <div id="chart-score">'.$totalscore.'점</div>
                    <div id="canvas-container">
                        <canvas id="chart" ></canvas>
                    </div>';
                } else {
                    echo '<div class="text-grey">획득한 친목점수가 없습니다! 아직은요...</div>';
                }
            ?>

        </div>
        <div class="border">
        <span class="box-title-small">참여 기록</span>
            <?php
                $datecnt = count($attenddate);
                if($datecnt > 0){
                    echo '';
                    echo '<div class="attend-table">';

                    // foreach($attenddate as $date){
                    //     echo '<th><a href="activity_view?i='.$date[0].'" target="amityactivity">'.$date[1].'</a></th>';
                    // }
                    // echo str_repeat('<th></th>', $datefill);
                    // echo '</tr>';

                    echo '<div class="attend-table-head"><div></div>';
                    foreach($attenddate as $date){
                        echo '<div><a href="activity_view?i='.$date[0].'" target="amityactivity">'.$date[1].'</a></div>';
                    }
                    echo '</div>';

                    foreach($attendrow as $email => $row){
                        echo '<div class="attend-table-row'.($email == $_SESSION['email'] ? ' selected' : '').'"><div>'.$row['gennm'].'</div>'.$row['echostr'].'</div>';
                    }
                    
                    echo '</div>';
                    echo '<span class="ascore-info">* 친목조 '.REQ_AMITY_ATT1.'회 출석시 활동점수 1점, '.REQ_AMITY_ATT2.'회 출석시 2점</span>';
                } else {
                    echo '<div class="attend-norecord border-top border-bottom"><span>🐶</span><br>진행한 친목조 활동이 없습니다! 친목조장에게 놀자고 졸라보는 건 어떨까요?</div>';
                }
            ?>
        </div>
        <!-- <div class="border">
            <div class="box-title-small">
                사진첩
            </div>
            <div class="photo-wrapper">
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
            </div>
        </div> -->
        <script>
            const labels = <?php echo json_encode($labellist, JSON_UNESCAPED_UNICODE); ?>;
            const data = <?php echo json_encode($scorelist, JSON_UNESCAPED_UNICODE); ?>;
        </script>
        <?php script("../assets/js/amity/mygroup.js"); ?>
    </main>
    <footer>
        <?php include '../parts/footer.php'?>
    </footer>
</body>