<?php
    require_once '../../api/util.php';
    require_once '../../api/managequery.php';
    
    if(!(logged_in() and is_exec())) fast_redirect('../');

    global $pdo;
    try{
        $sql = $pdo->prepare('SELECT CONCAT(gen, \'기 \', nm) AS gennm, exec FROM userinfo WHERE exec > 0 AND authlvl = 2 ORDER BY gen ASC, nm ASC');
        $sql->execute();
        $rows = $sql->fetchAll();

    } catch (Exception $e){
        errlog($e);
        alert('요청 처리 중 오류가 발생했습니다.');
        return;
    }
?>

<!DOCTYPE html>
<head>
    <?php include '../parts/head.php'?>
    <link href="../assets/css/manage/manage.css" rel="stylesheet">
    <link href="../assets/css/manageboard.css" rel="stylesheet">
    <link href="../assets/css/overlaylist.css" rel="stylesheet">
    <link href="../assets/css/manage/account.css" rel="stylesheet">
</head>
<body>
    <div class="overlay-shadow" style="display: none" id="account-overlay" class="overlay-shadow">
        <div class="overlay-wrapper">
            <h1>정말 삭제하시겠습니까?</h1>
            계정 삭제시 계정 정보와 활동 내역 등 모든 것이 복구 불가합니다.
            <span id="account-delete-id"></span> 계정을 삭제하려면 비밀번호를 입력하고 삭제를 눌러주세요.
            <div>
                <input type="password" placeholder="비밀번호" id="delete-password">
                <button class="btn-radius btn-nored" id="account-delete-confirm">삭제</button>
                <button class="btn-radius btn-black" onclick="$('#account-overlay').hide();">취소</button>
            </div>
        </div>
    </div>
    <div class="overlay-shadow" style="display: none" id="exec-overlay" class="overlay-shadow">
        <div class="overlay-wrapper">
            <div class="overlay-top">
                <span class="overlay-title" id="execlist-title"></span>
                <i class="bi bi-x-lg" onclick="$('#exec-overlay').hide();"></i>
            </div>
            <div class="overlay-search-wrapper">
                <input type="text" class="form-control search-input" id="execlist-search-input" placeholder="이름으로 검색" value="#all">
                <button class="btn-grey btn-radius" id="execlist-search-btn"><i class="bi bi-search"></i></button>
                <button class="btn-grey btn-radius" id="execlist-all-btn"><i class="bi bi-check-square"></i></button>
            </div>
            <div class="overlay-list-header">
                <div>이름</div>
                <div>수정</div>
            </div>
            <div class="overlay-list-wrapper" id="execlist">
            </div>
            <div class="overlay-btn-wrapper">
                <button class="btn-black btn-radius" onclick="$('#exec-overlay').hide();">확인</button>
            </div>
        </div>
    </div>
    <header>
        <?php include '../parts/managenavbar.php'?>
        <div class="tab-btn-wrapper border-bottom">
            <div>
                <div class="tab-btn-menu-wrapper">
                    <button class="tab-btn selected" onclick="changeTab(0, this);">승인/삭제</button>
                </div>
                <div class="tab-btn-menu-wrapper">
                    <button class="tab-btn" onclick="changeTab(1, this);">임원진 권한</button>
                </div>
                <div id="tab-btn-border-bottom"></div>
            </div>
        </div>
        <script>makeSubNavFocus(0);</script>
    </header>
    <main>
        <div class="tab-container table-container">
            <div class="table-option">
                <div>
                    총&nbsp;<span id="account-table-cnt">0</span>명이 선택되었습니다.
                </div>
                <div>
                    <i class="bi bi-funnel-fill"></i>
                    <select id="account-search-filter">
                        <option value="0">전체</option>
                        <option value="1">인증 중 </option>
                        <option value="2" selected>대기</option>
                        <option value="3">승인됨</option>
                    </select>
                </div>
                <div>
                    <input id="account-search-input" placeholder="이름으로 검색">
                    <button class="btn-radius btn-grey" id="account-search-btn"><i class="bi bi-search"></i></button>
                </div>
            </div>
            <div class="table" id="account-table">
                <div class="table-loading" id="account-table-loading">
                    <i class="bi bi-arrow-repeat"></i>불러오는 중...
                </div>
                <div>이름</div>
                <div class="table-header-cell">
                    <div>이메일</div>
                    <div>성별</div>
                    <div>학번</div>
                    <div>생년월일</div>
                    <div>승인</div>
                    <div>삭제</div>
                </div>
                <div class="table-name"></div>
                <div class="table-no-result text-grey">불러오는 중...</div>
            </div>
            <div class="table-index">
                <div>페이지 당
                    <select id="account-table-pp">
                        <option value="15">15개</option>
                        <option value="30">30개</option>
                        <option value="50">50개</option>
                    </select>
                </div>
                <div>
                    <button><i class="bi bi-chevron-double-left" onclick="addPage(-5)"></i></button>
                    <button><i class="bi bi-chevron-left" onclick="addPage(-1)"></i></button>
                    <span class="account-table-page-input"><input value="1" id="account-table-page"> / <span id="account-table-page-max"><?php echo intdiv($accounttable['cnt'] - 1, 15) + 1; ?></span></span>
                    <button><i class="bi bi-chevron-right" onclick="addPage(1)"></i></button>
                    <button><i class="bi bi-chevron-double-right" onclick="addPage(5)"></i></button>
                </div>
            </div>
        </div>
        <div class="tab-container" style="display: none">
            <div id="exec-wrapper">
                <?php
                    $exec_name = array('회장', '부회장', '악장', '기획부장', '홍보부장', '총무');

                    for($i = 0; $i < 6; $i++){
                        echo '<div class="border exec-row">';
                        echo '<div>'.$exec_name[$i].'</div>';

                        $gennm = array();
                        foreach($rows as $row){
                            if(($row['exec'] >> $i) % 2 == 1){
                                array_push($gennm, $row['gennm']);
                            }
                        }

                        if(count($gennm)) echo '<div>'.implode(', ', $gennm).'</div>';
                        else echo '<div class="text-grey">'.$exec_name[$i].(has_batchim($exec_name[$i]) ? '으로' : '로').' 등록된 사람이 없습니다!</div>';

                        echo '<div><button class="btn-grey btn-radius" onclick="showExecOverlay('.$i.')">수정</button></div>';
                        echo '</div>';
                    }
                ?>
            </div>
        </div>
        <?php script('../assets/js/manage/account.js'); ?>
    </main>
    <footer>
        <?php include '../parts/footer.php'?>
    </footer>
    <input type="hidden" id="exec-code">
</body>