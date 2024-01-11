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

        if(!exec_auth(EXEC_VICE)){
            alert('부회장만 접근 가능합니다!');
            redirect('../');
            return;
        }
    }

    $navbartitle = '친목조';
?>
<!DOCTYPE html>
<head>
    <?php include '../parts/head.php'?>
    <?php css("../assets/css/board.css"); ?>
    <?php css("../assets/css/overlaylist.css"); ?>
    <?php css("../assets/css/amity/modify.css"); ?>
</head>
<body>
    <div class="overlay-shadow" style="display: none;" id="leaderlist-overlay">
        <div class="overlay-wrapper">
            <div class="overlay-top">
                <span class="overlay-title">친목조장 관리</span>
                <i class="bi bi-x-lg" onclick="$('#leaderlist-overlay').hide();"></i>
            </div>
            <div>
                <div class="overlay-search-wrapper">
                    <input type="text" class="form-control search-input" id="leaderlist-search-input" placeholder="이름으로 검색" value="#all">
                    <button class="btn-grey btn-radius" id="leaderlist-search-btn"><i class="bi bi-search"></i></button>
                    <button class="btn-grey btn-radius" id="leaderlist-all-btn"><i class="bi bi-check-square"></i></button>
                </div>
                <div class="overlay-list-header">
                    <div>이름</div>
                    <div>추가</div>
                </div>
                <div class="overlay-list-wrapper" id="leaderlist">
                </div>
            </div>
        </div>
    </div>
    <div class="overlay-shadow" style="display: none;" id="memberlist-overlay">
        <input type="hidden" id="memberlist-id">
        <div class="overlay-wrapper">
            <div class="overlay-top">
                <span class="overlay-title">친목조원 관리</span>
                <i class="bi bi-x-lg" onclick="$('#memberlist-overlay').hide();"></i>
            </div>
            <div>
                <div class="overlay-search-wrapper">
                    <input type="text" class="form-control search-input" id="memberlist-search-input" placeholder="이름으로 검색" value="#all">
                    <button class="btn-grey btn-radius" id="memberlist-search-btn"><i class="bi bi-search"></i></button>
                    <button class="btn-grey btn-radius" id="memberlist-all-btn"><i class="bi bi-check-square"></i></button>
                    <button class="btn-grey btn-radius" id="memberlist-sex-btn"><i class="bi bi-gender-male"></i></button>
                </div>
                <div class="overlay-list-header">
                    <div>이름</div>
                    <div><input type="checkbox" id="memberlist-controlall"></div>
                </div>
                <div class="overlay-list-wrapper" id="memberlist">
                </div>
            </div>
            <div class="overlay-btn-wrapper">
                <button class="btn-black btn-radius" id="memberlist-confirm">확인</button>
            </div>
        </div>
    </div>
    <header>
        <?php include '../parts/navbar.php'?>
        <?php include 'subnavbar.php'?>
        <script>makeSubNavFocus(3);</script>
    </header>
    <main>
        <div class="tab-btn-wrapper border-bottom">
            <div class="tab-btn-menu-wrapper">
                <button class="tab-btn selected" onclick="amityChangeTab(0, this);">친목조 구성</button>
            </div>
            <div class="tab-btn-menu-wrapper">
                <button class="tab-btn" onclick="amityChangeTab(1, this);">친목점수 항목</button>
            </div>
            <div id="tab-btn-border-bottom"></div>
        </div>
        <div class="tab-container">
            <div class="group-top">
                <button class="btn-white btn-radius" onclick="showLeaderlist()">친목조장 관리</button>
                <div>
                    성별 표시
                    <input type="checkbox" id="sex-reveal">
                </div>
            </div>
            <div id="group-wrapper">
                <?php echo group_list_get()['content']; ?>
            </div>
        </div>
        <div class="tab-container" style="display: none">
            <div class="type-grid type-header">
                <div>설명</div>
                <div>계산식</div>
                <div></div>
            </div>
            <div id="type-wrapper">
                <?php echo activity_type_get()['content']; ?>
            </div>
        </div>
        <?php script("../assets/js/amity/modify.js"); ?>
    </main>
    <footer>
        <?php include '../parts/footer.php'?>
    </footer>
</body>