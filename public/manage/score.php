<?php
    require_once '../../api/util.php';
    require_once '../../api/managequery.php';
    
    if(!(logged_in() && is_exec())) fast_redirect('../');
?>

<!DOCTYPE html>
<head>
    <?php include '../parts/head.php'?>
    <link href="../assets/css/manage/manage.css" rel="stylesheet">
    <link href="../assets/css/manageboard.css" rel="stylesheet">
    <link href="../assets/css/overlaylist.css" rel="stylesheet"> 
    <link href="../assets/css/manage/score.css" rel="stylesheet"> 
</head>
<body>
    <div id="overlay" class="overlay-shadow" style="display: none;">
        <div class="overlay-wrapper">
            <input id="addlist-id" type="hidden">
            <div class="overlay-top">
                <span class="overlay-title" id="addlist-title">점수 부여 대상</span>
                <i class="bi bi-x-lg" onclick="$('#overlay').hide();"></i>
            </div>
            <div id="overlay-window">
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
                        <button class="btn-black btn-radius" id="overlay-next">다음</button>
                    </div>
                </div>
                <div>
                    <div>
                        종류:
                        <select id="addinfo-type">
                            <option value="0">활동 점수</option>
                            <option value="1">피아노 점수</option>
                        </select>
                    </div>
                    <div>
                        사유:
                        <select id="addinfo-a-why">
                            <option value="0">선택하세요</option>
                            <option>마니또 활동</option>
                            <option>엠티 참여</option>
                            <option>개강총회</option>
                            <option>개강파티</option>
                            <option>종강총회</option>
                            <option>술자리 3회 출석</option>
                            <option>외부 실적</option>
                            <option>연주회 스태프</option>
                            <option>게릴라 이벤트</option>
                            <option value="">직접 입력</option>
                        </select>
                        <select id="addinfo-p-why" style="display: none">
                            <option value="0">선택하세요</option>
                            <option>버스킹 참여</option>
                            <option value="">직접 입력</option>
                        </select>
                    </div>
                    <div>
                        설명:
                        <input id="addinfo-tmi" maxlength="40" placeholder="설명">
                    </div>
                    <div>
                        점수:
                        <input id="addinfo-quantity" placeholder="점수" type="number">
                    </div>
                    <div class="overlay-btn-wrapper">
                        <button class="btn-white btn-radius" id="overlay-prev">이전</button>
                        <button class="btn-black btn-radius" id="overlay-confirm" disabled>확인</button>
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
                    <button class="tab-btn selected" onclick="changeTab(0, this);">점수 보기</button>
                </div>
                <div class="tab-btn-menu-wrapper">
                    <button class="tab-btn" onclick="changeTab(1, this);">점수 부여</button>
                </div>
                <div id="tab-btn-border-bottom"></div>
            </div>
        </div>
    </header>
    <main>
        <div class="tab-container table-container">
            <div class="table-option">
                <div>
                    총&nbsp;<span id="score-table-cnt">0</span>명이 선택되었습니다.
                </div>
                <button id="score-table-copy" style="display: none"><i class="bi bi-clipboard"></i></button>
                <div>
                    <i class="bi bi-funnel-fill"></i>
                    <select id="score-search-filter">
                        <option value="0" selected>전체</option>
                        <option value="1">인정</option>
                        <option value="2">종총 참가시 인정</option>
                        <option value="3">미인정</option>
                    </select>
                </div>
            </div>
            <div class="table noindex" id="score-table">
                <div class="table-loading" id="score-table-loading">
                    <i class="bi bi-arrow-repeat"></i>불러오는 중...
                </div>
                <div>이름</div>
                <div class="table-header-cell">
                    <div>동비</div>
                    <div>활동</div>
                    <div>피아노</div>
                    <div>정모</div>
                    <div>수상</div>
                    <div>인정</div>
                </div>
                <div></div><div class="table-no-result text-grey">불러오는 중...</div>
            </div>
        </div>
        <div class="tab-container" style="display: none;">
            <div class="scorelist-option">
                <i class="bi bi-funnel-fill"></i>&nbsp;
                <select class="form-control" id="add-type">
                    <option value="0">전체</option>
                    <option value="1">활동 점수</option>
                    <option value="2">피아노 점수</option>
                </select>
                <button id="scorelist-add" class="btn-grey btn-radius">점수 추가</button>
            </div>
            <div id="scorelist">

            </div>
        </div>
        <?php script('../assets/js/manage/score.js'); ?>
    </main>
    <footer>
        <?php include '../parts/footer.php'?>
    </footer>
</body>