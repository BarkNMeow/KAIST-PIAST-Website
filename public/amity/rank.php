<?php
    include_once '../api/util.php';
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
        $sql = $pdo->prepare('SELECT a.id, a.groupnm, a.score, COUNT(IF(ac.accept = 1 AND ac.type = 0, 1, NULL)) AS cnt FROM amitygroup a LEFT JOIN amityactivity ac ON a.id = ac.groupid GROUP BY a.id ORDER BY a.score DESC');
        $sql->execute();
        $groups = $sql->fetchAll();

        $grouplist = array();
        foreach($groups as $group){
            array_push($grouplist, array('nm' => $group['groupnm'], 'id' => $group['id']));
        }

        $sql = $pdo->prepare('SELECT g.id, a.scoreboard, a.score, a.accept, a.type FROM amitygroup g LEFT JOIN amityactivity a ON g.id = a.groupid');
        $sql->execute();
        $activities = $sql->fetchAll();

        $sql = $pdo->prepare('SELECT * FROM amityActivityType where visible = 1');
        $sql->execute();
        $scorehow = $sql->fetchAll();

        $labellist = array_merge(array_column($scorehow, 'descr'), array('날갱', '친목조 게임', '기타'));
        $scorechart = array();
        $activityexist = false;

        foreach($activities as $activity){
            if(!isset($scorechart[$activity['id']])) $scorechart[$activity['id']] = array_fill(0, count($labellist), 0);

            $scoreboard = json_decode($activity['scoreboard'], true);
            if($activity['type'] == 0){
                if($activity['accept']){
                    foreach($scoreboard as $score){
                        $scorechart[$activity['id']][$score['event']] += _calc_score($score['event'], $score['n'], $score['k']);
                    }
                }

            } else {
                $id = ($activity['type'] < 0 ? 2 : $activity['type'] - 1);
                $scorechart[$activity['id']][count($scorehow) + $id] += $activity['score'];
            }

            if($activity['accept']) $activityexist = true;
        }

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
    <?php css("../assets/css/amity/rank.css")?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js" integrity="sha512-TW5s0IT/IppJtu76UbysrBH9Hy/5X41OTAbQuffZFU6lQ1rdcLHzpU5BzVvr/YFykoiMYZVWlr/PX1mDcfM9Qg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <style>
        <?php
        $groupcolor = array();
        if($groups){
            function sort_by_id($a, $b){
                if($a['id'] == $b['id']) return 0;
                else return $a['id'] > $b['id'] ? 1 : -1;
            }

            $huestep = 360 / count($groups);
            $hue = 0;
            $groups_id = $groups;
            usort($groups_id, 'sort_by_id');

            foreach($groups_id as $group){
                echo '.group'.$group['id'].'{
                    background: hsl('.intval($hue).', 100%, 70%);
                }';

                echo '.group'.$group['id'].'.op{
                    background: hsla('.intval($hue).', 100%, 70%, 0.4);
                }';
                $hue += $huestep;
            }
        }
        ?>
    </style>
</head>
<body>
    <header>
        <?php include '../parts/navbar.php'?>
        <?php include 'subnavbar.php'?>
        <script>makeSubNavFocus(0);</script>
    </header>
    <main>
        <div class="group-wrapper">
            <?php
                for($i = 0; $i < 3; $i++){
                    if(isset($groups[$i])){
                        $group = $groups[$i];
                        $nextscore = $groups[$i + 1]['score'];
                        echo '<div class="border">
                                <i class="bi bi-trophy-fill"></i>
                                <div class="group-title">'.$group['groupnm'].'</div>
                                <div class="group-score">'.$group['score'].'점<span class="group-scorediff">(+'.($group['score'] - $nextscore).')</span></div>';
                        
                        if($i == 0) echo '<div class="group-head">친목조 활동 '.$group['cnt'].'회</div>';
                        echo '</div>';
                    } else {
                        echo '<div class="border nogroup"></div>';
                    }
                }

                if(count($groups) > 3){
                    echo '<div class="border">';

                    for($i = 3; $i < count($groups); $i++){
                        $group = $groups[$i];
                        echo '<div class="rank-row">
                                <div>'.($i + 1).'위</div>
                                <div>'.$group['groupnm'].'</div>
                                <div>'.$group['score'].'점</div>
                            </div>';
                    }

                    echo '</div>';
                } else {
                    echo '<div class="border nogroup"></div>';
                }
            ?>
        </div>
        <div class="chart-container border">
            <div id="btn-wrapper">
                <button class="btn-radius selected" onclick="showGraphTab(1)">현재 점수</button>
                <button class="btn-radius" onclick="showGraphTab(2)">날갱</button>
            </div>
            <div id="graph-wrapper">
                <div class="barchart-container" style="height: <?php echo ($activityexist ? count($grouplist) * 4.75 : 15.5) ?>rem">
                    <?php
                        if($activityexist){
                            echo '<canvas id="barchart"></canvas>';
                        } else {
                            echo '<div class="canvas-nogroup text-grey">
                                <span>😮</span>
                                진행된 친목활동이 없습니다! 어서 놀러가세요!
                            </div>';
                        }
                    ?>
                </div>
                <div id="calendar-container" style="display: none;">
                    <div id="calendar">
                    </div>
                    <div>
                        <div class="calendar-legend-wrapper">
                            <?php
                            if($groups){
                                foreach($groups_id as $group){
                                    echo '<div class="calendar-legend" name="'.$group['id'].'" data-id="'.$group['id'].'">
                                        <div>'.$group['groupnm'].'</div>
                                        <div class="group'.$group['id'].'"></div>
                                    </div>';
                                }
                            } else {
                                echo '<div class="calendar-nolegend text-grey">생성된 친목조가 없습니다!</div>';
                            }

                            ?>
                        </div>
                        <div class="calendar-nav">
                            <div id="calendar-year"></div>
                            <button id="calendar-month-dec"></button>
                            <div id="calendar-month"></div>
                            <button id="calendar-month-inc"></button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <script>
            const grouplist = <?php echo json_encode($grouplist, JSON_UNESCAPED_UNICODE); ?>;
            const label = <?php echo json_encode($labellist, JSON_UNESCAPED_UNICODE); ?>;
            const bardata = <?php echo json_encode($scorechart, JSON_UNESCAPED_UNICODE); ?>;
        </script>
        <?php script("../assets/js/amity/rank.js"); ?>
    </main>
    <footer>
        <?php include '../parts/footer.php'?>
    </footer>
</body>