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
    <link href="../assets/css/manage/activity.css" rel="stylesheet"> 
</head>
<body>
    <!-- <div class="overlay-shadow" style="display: none" id="overlay" class="overlay-shadow">
        <div class="overlay-wrapper">
            <h1>검색 필터 설정</h1>
            <div>
                <span>인정학기</span>
                <input type="checkbox">
                <input type="checkbox">
                <input type="checkbox">
                <input type="checkbox">
                <input type="checkbox">
            </div>

            <div>
                <span>연속 미인정</span>
                <input type="checkbox">
                <input type="checkbox">
            </div>
        </div>
    </div> -->
    <header>
        <?php include '../parts/managenavbar.php'?>
    </header>
    <main>
        <script>makeSubNavFocus(1);</script>
        <div class="table-container">
            <div class="table-option">
                <div>
                    <i class="bi bi-funnel-fill"></i>
                    <select id="activity-search-filter">
                        <option value="0">전체</option>
                        <option value="1" selected>휴동</option>
                        <option value="2">집행부원</option>
                        <option value="3">제명 가능</option>
                    </select>
                    <button onclick="$('#overlay').show();"><i class="bi bi-three-dots-vertical"></i></button>
                </div>
                <div>
                    총&nbsp;<span id="activity-table-cnt">0</span>명 선택됨
                </div>
                <div>
                    <button id="activity-table-mode-btn"><i class="bi bi-eye-fill"></i> 보기 모드</button>
                </div>
                <div>
                    <input id="activity-search-input" placeholder="이름으로 검색">
                    <button class="btn-radius btn-grey" id="activity-search-btn"><i class="bi bi-search"></i></button>
                </div>
            </div>
            <div class="table viewonly" id="activity-table">
                <div class="table-loading" id="activity-table-loading">
                    <i class="bi bi-arrow-repeat"></i>불러오는 중...
                </div>
                <div>이름</div>
                <div class="table-header-cell">
                    <div>인정 학기</div>
                    <div>연속 미인정</div>
                    <div>휴동 횟수</div>
                    <div>휴동</div>
                    <div>집행</div>
                    <div>정당</div>
                    <div>무단</div>
                </div>
                <div></div><div class="table-no-result text-grey">불러오는 중...</div>
            </div>
            <div class="table-index">
                <div>페이지 당
                    <select id="activity-table-pp">
                        <option value="15">15개</option>
                        <option value="30">30개</option>
                        <option value="50">50개</option>
                    </select>
                </div>
                <div>
                    <button><i class="bi bi-chevron-double-left" onclick="addPage(-5)"></i></button>
                    <button><i class="bi bi-chevron-left" onclick="addPage(-1)"></i></button>
                    <span class="activity-table-page-input"><input value="1" id="activity-table-page"> / <span id="activity-table-page-max"><?php echo intdiv($activitytable['cnt'] - 1, 15) + 1; ?></span></span>
                    <button><i class="bi bi-chevron-right" onclick="addPage(1)"></i></button>
                    <button><i class="bi bi-chevron-double-right" onclick="addPage(5)"></i></button>
                </div>
            </div>
        </div>
        <?php script('../assets/js/manage/activity.js'); ?>
    </main>
    <footer>
        <?php include '../parts/footer.php'?>
    </footer>
</body>