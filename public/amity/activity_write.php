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
        
        if(!is_amity_leader()){
            alert('친목조장만 접근 가능합니다!');
            redirect('activity');
            return;
        }
    }

    global $pdo;
    try{
        $sql = $pdo->prepare('SELECT id FROM amitygroup WHERE email = :email');
        $sql->bindParam(':email', $_SESSION['email']);
        $sql->execute();
        $groupid = ($sql->fetch())['id'];

        if(!$groupid){
            alert('담당하고 있는 친목조가 없습니다!');
            redirect('activity');
            return;
        }

        $sql = $pdo->prepare("SELECT CONCAT(gen, '기 ', nm) AS gennm, email FROM userinfo WHERE amitygroupid = :groupid ORDER BY gen ASC, nm ASC");
        $sql->bindParam(':groupid', $groupid);
        $sql->execute();
        $members = $sql->fetchAll();

        $ismod = isset($_GET['i']);
        $score = array();
        $imageList = array();

        if($ismod){
            $sql = $pdo->prepare('SELECT * FROM amityactivity WHERE id = :id');
            $sql->bindParam(':id', $_GET['i']);
            $sql->execute();
            $one = $sql->fetch();

            if(!$one || $one['type'] != 0){
                alert('존재하지 않는 게시물입니다!');
                redirect('activity');
                return;
            }

            if($one['groupid'] != $groupid){
                alert('본인이 작성한 게시물이 아닙니다!');
                redirect('activity');
                return;
            }

            $attendlist = json_decode($one['who'], true);

            $imageListRaw = json_decode($one['image'], true);
            foreach($imageListRaw as $hash => $dummy){
                $imageList[$hash] = $hash;
            }

            $score = json_decode($one['scoreboard'], true);
        }

        $sql = $pdo->prepare("SELECT * FROM amityActivityType where visible = 1");
        $sql->execute();
        $activityType = $sql->fetchAll();

    } catch(Exception $e){
        alert('요청 처리 중 오류가 발생했습니다.');
        errlog($e);
        redirect('activity');
        return;
    }

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
    <link href="//cdn.quilljs.com/1.3.6/quill.bubble.css" rel="stylesheet">
    <?php css("../assets/css/amity/activity_write.css"); ?>
    <script src="//cdn.quilljs.com/1.3.6/quill.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/compressorjs/1.1.1/compressor.min.js" integrity="sha512-VaRptAfSxXFAv+vx33XixtIVT9A/9unb1Q8fp63y1ljF+Sbka+eMJWoDAArdm7jOYuLQHVx5v60TQ+t3EA8weA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://unpkg.com/quill-image-compress@1.2.11/dist/quill.imageCompressor.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/core.min.js" integrity="sha512-t8vdA86yKUE154D1VlNn78JbDkjv3HxdK/0MJDMBUXUjshuzRgET0ERp/0MAgYS+8YD9YmFwnz6+FWLz1gRZaw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/sha1.min.js" integrity="sha512-NHw1e1pc4RtmcynK88fHt8lpuetTUC0frnLBH6OrjmKGNnwY4nAnNBMjez4DRr9G1b+NtufOXLsF+apmkRCEIw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/lib-typedarrays.min.js" integrity="sha512-IYLn1Vhe6FU/6vVifkxxGV8exi8kFXjrIVuNuYlGrQQ/gv3+fa/fPFY5Nh1QCB+EdUrY+QRVocT9jtxPzlkjWQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
</head>
<body>
    <header>
        <?php include '../parts/navbar.php'?>
        <?php include 'subnavbar.php'?>
        <script>makeSubNavFocus(1);</script>
    </header>
    <main>
        <div id="activity-form">
            <div>
                <input type="date" id="activity-date"<?php echo ($ismod ? ' value="'.$one['date'].'"' : '')?>>
                <input placeholder="제목" maxlength="60" id="activity-title"<?php echo ($ismod ? ' value="'.htmlspecialchars($one['title']).'"' : '')?>>
            </div>
            <div id="activity-attendee">
                <?php
                    foreach($members as $person){
                        $checked = '';
                        if($ismod){
                            if(in_array($person['email'], $attendlist))
                                $checked = ' checked';
                        }
                        echo '<div>'.$person['gennm'].'<input type="checkbox" name="attend" value="'.$person['email'].'" '.$checked.'></div>';
                    }
                ?>
            </div>
            <div id="activity-image">
                <div class="image-inner"<?php if($ismod) echo ' style="display: none"'; ?>>
                    <i class="bi bi-card-image"></i>
                    활동사진 첨부...
                </div>
                <div class="image-ui"<?php if(!$ismod) echo ' style="display: none"'; ?>>
                    <div class="left-btn"><i class="bi bi-chevron-compact-left"></i></div>
                    <div class="menu-wrapper">
                        <div class="menu">
                            <span><?php echo '1 / '.count($imageList); ?></span>
                            <i class="bi bi-plus-circle" title="사진 추가"></i>
                            <i class="bi bi-trash" title="사진 삭제"></i>
                        </div>
                    </div>
                    <div class="right-btn"><i class="bi bi-chevron-compact-right"></i></div>
                </div>
                <?php
                    if($ismod){
                        $flag = true;
                        $visibility = '';
                        foreach($imageList as $hash){
                            echo '<div class="image-viewer" style="'.$visibility.'background-image:url(../../image/amity/'.$hash.$_GET['i'].')" name="'.$hash.'"></div>';
                            if($flag){
                                $visibility = 'display: none; ';
                                $flag = false;
                            }   
                        }
                    }
                ?>
            </div>
            <div id="quill">
                <?php if($ismod) echo purify_small($one['main']); ?>
            </div>
        </div>
        <div class="score-table-container border">
            <div class="score-table-header"><i class="bi bi-trophy-fill"></i> 친목조 점수</div>
            <div id="score-table">
                <?php
                    $totalscore = 0;
                    array_push($score, array('event' => -1, 'n' => null, 'k' => null));
                    foreach($score as $event){
                        $eventid = $event['event'];
                        $n = $event['n'];
                        $k = $event['k'];
                        $score = _calc_score($eventid, $n, $k);

                        echo '<div class="score-row">';
                        echo '<select name="select-event">';
                        echo '<option value="-1">활동을 선택하세요.</option>';
                        foreach($activityType as $one){
                            $desc = _get_desc($one['id']);
                            echo '<option value="'.$one['id'].'"'.(($eventid == $one['id']) ? ' selected' : '').'>'.$one['descr'].'</option>';
                        }
                        echo '</select>';
                        echo '<input name="n" type="number" placeholder="n"'.($n ? ' value="'.$n.'"' : ' disabled').'>';
                        echo '<input name="k" type="number" placeholder="k"'.($k ? ' value="'.$k.'"' : ' disabled').'>';
                        echo '<div>=</div>';
                        echo '<div>'.($score > 0 ? $score : '?').'</div></div>';

                        $totalscore += max($score, 0);
                    }
                ?>
            </div>
            <div>
                <div>총 점수:</div>
                <div id="activity-score-total"><?php echo $totalscore; ?></div>
            </div>
        </div>
        <div class="submit-wrapper">
            <button class="btn-black btn-radius" disabled>활동 <?php echo ($ismod ? '수정' : '쓰기')?></button>
        </div>
        <input type="file" id="activity-file" style="display: none" accept="image/*" multiple>
        <input id="id" value="<?php echo $_GET['i']?>" style="display: none">
        <?php script("../assets/js/amity/activity_core.js"); ?>
        <?php script("../assets/js/amity/activity_write.js"); ?>
        <script>getScoreboard(<?php echo json_encode($activityType, JSON_ENCODE); ?>)</script>
        <?php if($ismod) echo '<script>getImageList('.json_encode($imageList, JSON_ENCODE).')</script>'; ?>
    </main>
    <footer>
        <?php include '../parts/footer.php'?>
    </footer>
</body>