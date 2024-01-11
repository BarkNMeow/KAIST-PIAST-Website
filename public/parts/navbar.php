<nav id="navbar">
    <div>
        <a href="/dashboard" class="navbar-logo" title="홈으로">
            <!-- Logo svg -->
            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 140 140" style="enable-background:new 0 0 140 140;" xml:space="preserve" fill="currentColor">
                <style type="text/css">
                    .st2{stroke: #000;stroke-miterlimit:0;}
                    .st3{fill:none;stroke:#000;stroke-miterlimit:0;}
                </style>
                <path class="st2" d="M-389.26,41.51c0-9.63-18.27-22.59-18.27-22.59l-71.21,40.47c0,0-0.9,6.09,3.03,3.93s24.95-14.54,24.95-14.54  c6.88,3.73,22.59,5.3,43.62,5.89C-386.12,55.26-389.26,41.51-389.26,41.51z M-396.2,45.66c-10.56,8.03-48.1-0.74-48.1-0.74  l34.97-20.24C-409.32,24.68-386.38,38.19-396.2,45.66z"/>
                <path class="st3" d="M-455.28,108.31"/>
                <path class="st2" d="M-465.39,52.98l3.44,55.33h6.68l-2.36-31.04c0,0,12.14-4.32,13.34-5.89C-443.1,69.8-405.57,92-405.57,92  l3.73-3.93L-465.39,52.98z M-459.21,70.59v-7.86l7.27,3.93L-459.21,70.59z"/>
                <polygon class="st2" points="-405.96,93.57 -403.41,98.88 -386.51,88.27 -384.74,125.01 -376.69,125.01 -380.22,83.75   -369.81,77.85 -372.56,69.41 "/>
                <path class="st2" d="M-405.96,33.45c5.5,26.33,4.13,52.85,4.13,52.85c-0.98-28.1-12.18-51.08-12.18-51.08L-405.96,33.45z"/>
                <path class="st2" d="M-401.05,39.35c0,0,3.54,1.18,2.55,3.73c-0.98,2.55-15.33,14.54-15.33,14.54s-9.04,11.4,3.93,7.47  c12.97-3.93,18.08-4.72,18.08-4.72s14.93-1.18,7.07,11.59s-14.93,14.93-14.93,14.93s-4.12-1.28,0-5.65  c4.12-4.37,7.46-7.71,7.46-7.71s7.07-10.41-5.7-7.86c-12.77,2.55-14.93,3.54-14.93,3.54s-19.45,3.14-6.29-12.77  C-419.13,56.44-413.43,50.15-401.05,39.35z"/>
                <rect x="-365.86" y="14.99" style="fill:none;" width="231.49" height="75.97"/>
                <rect x="-361.49" y="95.26" style="fill:none;" width="231.49" height="26.1"/>
                <path class="st2" d="M103.61,39.55c0-9.63-18.27-22.59-18.27-22.59L14.13,57.43c0,0-0.9,6.09,3.03,3.93  c3.93-2.16,24.95-14.54,24.95-14.54c6.88,3.73,22.59,5.3,43.62,5.89C106.76,53.3,103.61,39.55,103.61,39.55z M96.68,43.7  c-10.56,8.03-48.1-0.74-48.1-0.74l34.97-20.24C83.55,22.72,106.5,36.23,96.68,43.7z"/>
                <path class="st3" d="M37.6,106.35"/>
                <path class="st2" d="M27.48,51.02l3.44,55.33h6.68L35.24,75.3c0,0,12.14-4.32,13.34-5.89c1.2-1.57,38.73,20.63,38.73,20.63  l3.73-3.93L27.48,51.02z M33.67,68.62v-7.86l7.27,3.93L33.67,68.62z"/>
                <polygon class="st2" points="86.91,91.61 89.47,96.92 106.37,86.31 108.13,123.05 116.19,123.05 112.65,81.79 123.07,75.89   120.31,67.45 "/>
                <path class="st2" d="M86.91,31.49c5.5,26.33,4.13,52.85,4.13,52.85c-0.98-28.1-12.18-51.08-12.18-51.08L86.91,31.49z"/>
                <path class="st2" d="M91.83,37.39c0,0,3.54,1.18,2.55,3.73c-0.98,2.55-15.33,14.54-15.33,14.54s-9.04,11.4,3.93,7.47  s18.08-4.72,18.08-4.72s14.93-1.18,7.07,11.59S93.2,84.93,93.2,84.93s-4.12-1.28,0-5.65c4.12-4.37,7.46-7.71,7.46-7.71  s7.07-10.41-5.7-7.86c-12.77,2.55-14.93,3.54-14.93,3.54s-19.45,3.14-6.29-12.77C73.75,54.48,79.45,48.19,91.83,37.39z"/>
            </svg>
        </a>
        <div class="navbar-title">
            <?php echo $navbartitle; ?>
        </div>
        <div class="navbar-icon-wrapper">
            <div id="menu-dropdown-wrapper">
                <i class="bi bi-grid"></i>
                <div id="menu-dropdown" class="border" style="display: none;">
                    <a href="../jungmo/apply">
                        <i class="bi bi-people-fill"></i>
                        정모
                    </a>
                    <a href="../concert/apply">
                        <i class="bi bi-disc-fill"></i>
                        연주회
                    </a>
                    <a href="../lesson/list">
                        <i class="bi bi-mortarboard-fill"></i>
                        레슨
                    </a>
                    <a href="../amity/rank">
                        <i class="bi bi-balloon-fill"></i>
                        친목조
                    </a>
                    <a href="../bbs/list?b=0">
                        <i class="bi bi-postcard-fill"></i>
                        게시판
                    </a>
                    <a href="../score/list">
                        <i class="bi bi-file-earmark-music-fill"></i>
                        악보
                    </a>
                    <a href="../phonebook">
                        <i class="bi bi-telephone-fill"></i>
                        주소록
                    </a>
                    <a href="../rule">
                        <i class="bi bi-book-fill"></i>
                        회칙
                    </a>
                </div>
            </div>
            <a href="../notify">
                <i class="bi bi-bell-fill"></i>
                <div id="notify-menu-cnt" style="display: none"></div>
            </a>
            <div id="account-dropdown-wrapper">
                <i class="bi bi-person-circle"></i>
                <div id="account-dropdown" style="display: none">
                    <a href="../myaccount">내 계정</a>
                    <a href="../myactivity">내 활동</a>
                    <?php
                        if(is_exec()) echo '<a href="../manage/account">관리</a>'
                    ?>
                    <a href="../logout">로그아웃</a>
                </div>
            </div>
        </div>
    </div>
</nav>
<?php script('../assets/js/nav.js'); ?>
<!-- End of navbar.html -->
