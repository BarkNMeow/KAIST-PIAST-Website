<?php
    require_once '../../api/authquery.php';
    require_once '../../api/util.php';
    require_once '../../api/moneyquery.php';
    
    if(!logged_in() || !is_exec()) fast_redirect('../');
    $moneyconfig = json_decode(file_get_contents('../../data/json/moneyconfig.json'), true);
?>

<!DOCTYPE html>
<head>
    <?php include '../parts/head.php'?>
    <?php css('../assets/css/manage/manage.css'); ?>
    <?php css('../assets/css/manageboard.css'); ?>
    <?php css('../assets/css/overlaylist.css'); ?>
    <?php css('../assets/css/manage/money.css'); ?>
</head>
<body>
    <?php
        if(exec_auth(EXEC_AFFAIR)){
            echo '
            <div id="setting-overlay" class="overlay-shadow" style="display: none;">
                <div class="overlay-wrapper">
                    <div class="overlay-title">회계 설정</div>
                    <div>
                        회비 <input id="setting-due" type="number" placeholder="회비" value="'.$moneyconfig['due'].'">원
                    </div>
                    <div>
                        계좌 <input id="setting-accountnum" placeholder="모든 현역부원에게 표출됩니다." maxlength="30" value="'.htmlspecialchars($moneyconfig['accountnum']).'">
                    </div>
                    <div>
                        장부 초기화 <a href="download?g=m" download><button id="setting-init-btn" class="btn-nored btn-radius">초기화</button></a><br>
                        <span class="text-grey setting-warning">* 신입부원 가입 후, 첫 정모 전 초기화해주세요.</span>
                    </div>
                    <div>
                        <button class="btn-white btn-radius" onclick="$(\'#setting-overlay\').hide();">취소</button>
                        <button id="setting-confirm-btn" class="btn-black btn-radius">확인</button>
                    </div>
                </div>
            </div>';
        }
    ?>
    <div id="addlist-overlay" class="overlay-shadow" style="display: none;">
        <div class="overlay-wrapper">
            <input id="addlist-id" type="hidden">
            <div class="overlay-top">
                <span class="overlay-title" id="addlist-title">정산 대상</span>
                <i class="bi bi-x-lg" onclick="$('#addlist-overlay').hide();"></i>
            </div>
            <div id="addlist-overlay-window">
                <div>
                    <div class="overlay-search-wrapper">
                        <input type="text" class="form-control search-input" id="addlist-search-input" placeholder="이름으로 검색" value="#all">
                        <button class="btn-grey btn-radius" id="addlist-search-btn"><i class="bi bi-search"></i></button>
                        <button class="btn-grey btn-radius" id="addlist-all-btn"><i class="bi bi-check-square"></i></button>
                        <button class="btn-grey btn-radius" id="addlist-full-btn"><i class="bi-people-fill"></i></button>
                    </div>
                    <div class="overlay-list-header">
                        <div>이름</div>
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
                        설명:
                        <input id="addinfo-tmi" maxlength="40" placeholder="설명">
                    </div>
                    <div>
                        정산 인원:
                        <input id="addinfo-peoplecnt" placeholder="인원수" type="number">
                    </div>
                    <div>
                        정산 총액:
                        <input id="addinfo-totalmoney" placeholder="총액" type="number">
                    </div>
                    <div>
                        인당 금액:
                        <input id="addinfo-money" placeholder="인당 금액" type="number">
                    </div>
                    <div class="overlay-btn-wrapper">
                        <button class="btn-white btn-radius" id="addlist-overlay-prev">이전</button>
                        <button class="btn-black btn-radius" id="addlist-overlay-confirm" disabled>확인</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <header>
        <?php include '../parts/managenavbar.php'?>
        <div class="tab-btn-wrapper border-bottom">
            <div>
                <div class="tab-btn-menu-wrapper">
                    <button class="tab-btn selected" onclick="changeTab(0, this);">회계 장부</button>
                </div>
                <div class="tab-btn-menu-wrapper">
                    <button class="tab-btn" onclick="changeTab(1, this);">정산</button>
                </div>
                <div id="tab-btn-border-bottom"></div>
            </div>
        </div>
    </header>
    <main>
        <div class="tab-container table-container">
            <div class="table-option">
                <div>
                    총&nbsp;<span id="money-table-cnt">0</span>명이 선택되었습니다.
                </div>
                <?php
                    if(exec_auth(EXEC_AFFAIR)){
                        echo '
                        <div>
                            <button id="money-table-mode-btn"><i class="bi bi-eye-fill"></i> 보기 모드</button>
                        </div>
                        ';
                    }
                ?>
                <div>
                    <i class="bi bi-funnel-fill"></i>
                    <select id="money-search-filter">
                        <option value="0" selected>전체</option>
                        <option value="1">회비 미납</option>
                        <option value="2">정산/벌금 미납</option>
                        <option value="3">동비 미납</option>
                        <option value="4">OB/휴동</option>
                    </select>
                </div>
                <?php
                    if(exec_auth(EXEC_AFFAIR)){
                        echo '
                        <div>
                            <button class="btn-grey btn-radius" onclick="$(\'#setting-overlay\').show()">설정</button>
                        </div>
                        ';
                    }
                ?>
            </div>
            <div class="table noindex" id="money-table">
                <div class="table-loading" id="money-table-loading">
                    <i class="bi bi-arrow-repeat"></i>불러오는 중...
                </div>
                <div>이름</div>
                <div class="table-header-cell">
                    <div>회비</div>
                    <div>정산/벌금</div>
                    <div>필요</div>
                    <div>납부</div>
                    <div>일자</div>
                    <div>필요</div>
                    <div>납부</div>
                </div>
                <div></div><div class="table-no-result text-grey">불러오는 중...</div>
            </div>
        </div>
        <div class="tab-container" style="display: none">
            <div class="moneylist-option">
                총계: <span id="moneylist-total">0</span>원
                <button id="moneylist-add" class="btn-grey btn-radius">정산 추가</button>
            </div>
            <div id="moneylist">

            </div>
        </div>  
    </main>
    <?php script('../assets/js/manage/money.js'); ?>
    <footer>
        <?php include '../parts/footer.php'?>
    </footer>
</body>