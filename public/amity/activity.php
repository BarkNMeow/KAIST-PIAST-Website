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
        $sql = $pdo->prepare('SELECT id, groupnm FROM amitygroup');
        $sql->execute();
        $grouplist = $sql->fetchAll();

    } catch (Exception $e){
        alert('요청 처리 중 오류가 발생했습니다.');
        redirect('../');
        return;
    }

    if(!isset($_GET['p'])) $_GET['p'] = 0;
    if(!isset($_GET['pp'])) $_GET['pp'] = 15;

    $navbartitle = '친목조';
?>
<!DOCTYPE html>
<head>
    <?php include '../parts/head.php'?>
    <?php css("../assets/css/board.css"); ?>
    <?php css("../assets/css/overlaylist.css"); ?>
    <?php css("../assets/css/amity/activity.css"); ?>
</head>
<body>
    <?php
    if(exec_auth(EXEC_VICE)) echo '
    <div class="overlay-shadow" style="display: none;" id="addlist-overlay">
        <div class="overlay-wrapper">
            <div class="overlay-top">
                <span class="overlay-title" id="addlist-title">친목조 선택</span>
                <i class="bi bi-x-lg" onclick="$(\'#addlist-overlay\').hide();"></i>
            </div>
            <div id="addlist-overlay-window">
                <div>
                    <div class="overlay-search-wrapper">
                        <input type="text" class="form-control search-input" id="addlist-search-input" placeholder="이름으로 검색" value="#all">
                        <button class="btn-grey btn-radius" id="addlist-search-btn"><i class="bi bi-search"></i></button>
                        <button class="btn-grey btn-radius" id="addlist-all-btn"><i class="bi bi-check-square"></i></button>
                    </div>
                    <div class="overlay-list-header">
                        <div>조 이름 (이름)</div>
                        <div><input type="checkbox" id="addlist-controlall"></div>
                    </div>
                    <div class="overlay-list-wrapper" id="addlist">
                    </div>
                    <div class="overlay-btn-wrapper">
                        <button class="btn-black btn-radius" id="addlist-overlay-next">다음</button>
                    </div>
                </div>
                <div>
                    <div>
                        날짜:
                        <input type="date" id="addinfo-date">
                    </div>
                    <div>
                        사유:
                        <select id="addinfo-type">
                            <option value="">선택하세요</option>
                            <option value="1">날갱</option>
                            <option value="2">친목조게임</option>
                            <option value="-1">기타</option>
                        </select>
                    </div>
                    <div>
                        설명:
                        <input id="addinfo-desc" placeholder="기타일 때 설명 필수" maxlength="20">
                    </div>
                    <div>
                        점수:
                        <input id="addinfo-score" placeholder="점수" type="number">
                    </div>
                    <div class="overlay-btn-wrapper">
                        <button class="btn-white btn-radius" id="addlist-overlay-prev">이전</button>
                        <button class="btn-black btn-radius" id="addlist-overlay-confirm" disabled>확인</button>
                    </div>
                </div>
            </div>
        </div>
    </div>';
    ?>
    <header>
        <?php include '../parts/navbar.php'?>
        <?php include 'subnavbar.php'?>
        <script>makeSubNavFocus(1);</script>
    </header>
    <main>
        <div class="tag-bar">
            <span>태그:</span>
            <div class="tag-btn-window">
                <div class="left-hide" style="display: none;"></div>
                <div class="tag-btn-container">
                    <?php
                        $selected = (!isset($_GET['a']) && !isset($_GET['g']) && !isset($_GET['t'])) ? ' selected' : '';
                        echo '<button class="btn-tag'.$selected.'" name="all">전체</button>';

                        $selected = (isset($_GET['a']) && $_GET['a'] == 0) ? ' selected' : '';
                        echo '<button class="btn-tag'.$selected.'" name="a" value="0">미승인</button>';

                        $selected = (isset($_GET['t']) && $_GET['t'] == 0) ? ' selected' : '';
                        echo '<button class="btn-tag'.$selected.'" name="t" value="0">활동내역</button>';

                        $selected = ($_GET['t'] == 1) ? ' selected' : '';
                        echo '<button class="btn-tag'.$selected.'" name="t" value="1">날갱</button>';

                        foreach($grouplist as $group){
                            $selected = ($_GET['g'] == $group['id']) ? ' selected' : '';
                            echo '<button class="btn-tag'.$selected.'" name="g" value="'.$group['id'].'">'.htmlspecialchars($group['groupnm']).'</button>';
                        }
                    ?>
                </div>
                <div class="right-hide"></div>
            </div>
            <div>
                <button class="btn-move-window" name="left"><i class="bi bi-chevron-left"></i></button>
                <button class="btn-move-window" name="right"><i class="bi bi-chevron-right"></i></button>
            </div>
        </div>
        <table class="board">
            <colgroup>
                <col style="width: 7rem">   
                <col style="width: 7rem">   
                <col style="width: auto">
                <col style="width: 5rem">
            </colgroup>
            <tr>
                <th>
                    날짜
                </th>
                <th>
                    조
                </th>
                <th>
                    제목
                </th>
                <th>
                    점수
                </th>
            </tr>
            <?php 
                $result = activity_get($_GET['p'], $_GET['pp'], $_GET['a'] , $_GET['t'], $_GET['g']);
                echo $result['content']; 
            ?>
        </table>
        <div class="option-wrapper">
            페이지 당:
            <select id="page-select">
            <?php
                $page_sel[$_GET['pp']] = 'selected';
                echo '<option '.$page_sel[15].' value="15">15개</option>';
                echo '<option '.$page_sel[30].' value="30">30개</option>';
                echo '<option '.$page_sel[50].' value="50">50개</option>';
            ?>
            </select>
            <?php 
                if(exec_auth(EXEC_VICE)){
                    echo '<button class="btn-white btn-radius" id="score-add-btn" onclick="showAddlist();">점수 부여</button>';
                }

                if(is_amity_leader()){
                    echo '<a href="activity_write"><button class="btn-white btn-radius">활동 쓰기</button></a>';
                }
            ?>
        </div>
        <div class="option-invisible mt-3" style="text-align: center">
            <div class="selector-wrapper">
            <?php
                echo $result['selector'];
            ?>
            </div>
        </div>
    </main>
    <footer>
        <?php include '../parts/footer.php'?>
    </footer>
    <input style="display:none;" id="page" value="<?php echo $_GET['p']; ?>">
    <?php script("../assets/js/amity/activity.js"); ?>
</body>