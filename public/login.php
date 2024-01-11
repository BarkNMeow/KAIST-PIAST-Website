<?php
    require_once '../api/util.php';
    
    if(logged_in()) fast_redirect('dashboard');
?>

<!DOCTYPE html>
<head>
    <?php include 'parts/head.php'?>
    <link href="assets/css/login.css" rel="stylesheet">
</head>
<body>
    <div id="overlay" class="overlay-shadow" style="display: none">
        <div class="overlay-wrapper">
            <span class="overlay-title">개인정보 처리 동의</span>
            PIAST 회원 계정 약관
            <div class="contract-view border">
                <?php echo file_get_contents("../data/text/license.txt"); ?>
            </div>
            개인정보 수집 및 이용 동의
            <div class="contract-view border">
                <?php echo file_get_contents("../data/text/private.txt"); ?>
            </div>
            <div class="contract-radio">
                <div>위 약관에 모두 동의하십니까?</div> 
                <div>
                    <button class="btn-radius btn-black" id="contract-agree-yes">예</button>
                    <button class="btn-radius btn-white" id="contract-agree-no">아니요</button>
                </div>
            </div>
        </div>
    </div>
    <main>
        <div class="content-wrapper">
            <div class="box-wrapper">
                <div class="form-title border-bottom">
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 140 140" style="enable-background:new 0 0 140 140;" xml:space="preserve" fill="currentColor">
                        <style type="text/css">
                            .st0{stroke:#000000;stroke-miterlimit:5;}
                            .st1{fill:none;stroke:#000000;stroke-miterlimit:5;}
                        </style>
                        <path class="st0" d="M-389.26,41.51c0-9.63-18.27-22.59-18.27-22.59l-71.21,40.47c0,0-0.9,6.09,3.03,3.93s24.95-14.54,24.95-14.54  c6.88,3.73,22.59,5.3,43.62,5.89C-386.12,55.26-389.26,41.51-389.26,41.51z M-396.2,45.66c-10.56,8.03-48.1-0.74-48.1-0.74  l34.97-20.24C-409.32,24.68-386.38,38.19-396.2,45.66z"/>
                        <path class="st1" d="M-455.28,108.31"/>
                        <path class="st0" d="M-465.39,52.98l3.44,55.33h6.68l-2.36-31.04c0,0,12.14-4.32,13.34-5.89C-443.1,69.8-405.57,92-405.57,92  l3.73-3.93L-465.39,52.98z M-459.21,70.59v-7.86l7.27,3.93L-459.21,70.59z"/>
                        <polygon class="st0" points="-405.96,93.57 -403.41,98.88 -386.51,88.27 -384.74,125.01 -376.69,125.01 -380.22,83.75   -369.81,77.85 -372.56,69.41 "/>
                        <path class="st0" d="M-405.96,33.45c5.5,26.33,4.13,52.85,4.13,52.85c-0.98-28.1-12.18-51.08-12.18-51.08L-405.96,33.45z"/>
                        <path class="st0" d="M-401.05,39.35c0,0,3.54,1.18,2.55,3.73c-0.98,2.55-15.33,14.54-15.33,14.54s-9.04,11.4,3.93,7.47  c12.97-3.93,18.08-4.72,18.08-4.72s14.93-1.18,7.07,11.59s-14.93,14.93-14.93,14.93s-4.12-1.28,0-5.65  c4.12-4.37,7.46-7.71,7.46-7.71s7.07-10.41-5.7-7.86c-12.77,2.55-14.93,3.54-14.93,3.54s-19.45,3.14-6.29-12.77  C-419.13,56.44-413.43,50.15-401.05,39.35z"/>
                        <path class="st0" d="M103.61,39.55c0-9.63-18.27-22.59-18.27-22.59L14.13,57.43c0,0-0.9,6.09,3.03,3.93  c3.93-2.16,24.95-14.54,24.95-14.54c6.88,3.73,22.59,5.3,43.62,5.89C106.76,53.3,103.61,39.55,103.61,39.55z M96.68,43.7  c-10.56,8.03-48.1-0.74-48.1-0.74l34.97-20.24C83.55,22.72,106.5,36.23,96.68,43.7z"/>
                        <path class="st1" d="M37.6,106.35"/>
                        <path class="st0" d="M27.48,51.02l3.44,55.33h6.68L35.24,75.3c0,0,12.14-4.32,13.34-5.89c1.2-1.57,38.73,20.63,38.73,20.63  l3.73-3.93L27.48,51.02z M33.67,68.62v-7.86l7.27,3.93L33.67,68.62z"/>
                        <polygon class="st0" points="86.91,91.61 89.47,96.92 106.37,86.31 108.13,123.05 116.19,123.05 112.65,81.79 123.07,75.89   120.31,67.45 "/>
                        <path class="st0" d="M86.91,31.49c5.5,26.33,4.13,52.85,4.13,52.85c-0.98-28.1-12.18-51.08-12.18-51.08L86.91,31.49z"/>
                        <path class="st0" d="M91.83,37.39c0,0,3.54,1.18,2.55,3.73c-0.98,2.55-15.33,14.54-15.33,14.54s-9.04,11.4,3.93,7.47  s18.08-4.72,18.08-4.72s14.93-1.18,7.07,11.59S93.2,84.93,93.2,84.93s-4.12-1.28,0-5.65c4.12-4.37,7.46-7.71,7.46-7.71  s7.07-10.41-5.7-7.86c-12.77,2.55-14.93,3.54-14.93,3.54s-19.45,3.14-6.29-12.77C73.75,54.48,79.45,48.19,91.83,37.39z"/>
                    </svg>
                </div>
                <div id="form-wrapper" class="state0">
                    <div id="login-wrapper" style="visibility: visible">
                        <div>
                            카이스트 아이디(이메일)
                            <input type="text" class="form-control" id="login-email" autocomplete="username">
                        </div>
                        <div>
                            비밀번호
                            <input type="password" class="form-control" id="login-passwd" autocomplete="current-password">
                        </div>
                        <div>
                            <input type="checkbox" id="login-stay">
                            로그인 상태 유지
                        </div>
                        <div>
                            <button class="btn-radius login-btn" id="login-confirm">로그인</button>
                        </div>
                        <div class="login-bottom">
                            <button type="button" onclick="changeFormState(1);">회원가입</button>
                            /
                            <button type="button" onclick="changeFormState(2);">비밀번호 찾기</button>
                        </div>
                    </div>
                    <div id="signup-wrapper" style="visibility: hidden">
                        <div>
                            <input id="signup-email" placeholder="KAIST 이메일(ID)*">
                        </div>
                        <div>
                            <input type="password" placeholder="비밀번호(최소 8자)*" id="signup-passwd" autocomplete="new-password">
                        </div>
                        <div class="border-bottom">
                            <meter id="signup-passwd-strength"></meter>
                            <span>짧음</span>
                        </div>
                        <div>
                            <input type="number" min="1" placeholder="기수*" id="signup-gen" maxlength="3">기
                            <input placeholder="이름*" id="signup-nm">
                            <select id="signup-sex">
                                <option value="">성별*</option>
                                <option value="M">남자</option>
                                <option value="F">여자</option>
                                <option value="O">기타</option>
                            </select>
                        </div>
                        <div>
                            <input type="number" id="signup-yr" placeholder="학번" min="0" max="99">
                            <select id="signup-maj">
                                <option value="0">전공 선택</option>
                                <?php
                                    $fp = fopen('../data/text/majlist.csv', 'r');
                                    $i = 1;
                                    while(($data = fgetcsv($fp)) !== false){
                                        echo '<option value="'.$i.'">'.$data[0].'</option>';
                                        $i += 1;
                                    }
                                ?>
                            </select>
                        </div>
                        <div>
                            <input placeholder="생년(YYYY)" id="signup-by" type="number">
                            <input placeholder="월" id="signup-bm" type="number">
                            <input placeholder="일" id="signup-bd" type="number">
                        </div>
                        <div><input type="number" id="signup-phonenum" maxlength="11" placeholder="전화번호(- 없이)"></div>
                        <div>개인정보 처리 약관 <button class="btn-radius btn-white" id="signup-viewcontract">보기*</button></div>
                        <div>
                            <button class="btn-radius btn-black" id="signup-confirm" disabled>회원가입</button>
                            <button class="btn-radius btn-grey" onclick="changeFormState(0);">뒤로</button>
                        </div>
                    </div>
                    <div id="passwdlost-wrapper" style="visibility: hidden">
                        <div>
                            비밀번호를 잊어버리셨다면, 아이디(카이스트 ID)를 입력해주세요.
                        </div>
                        <div>
                            <input placeholder="카이스트 아이디(이메일)" id="passwdlost-input"></input>
                        </div>
                        <div>
                            <button class="btn-radius btn-black" id="passwdlost-confirm" disabled>확인</button>
                            <button class="btn-radius btn-grey" onclick="changeFormState(0);">뒤로</button>
                        </div>
                    </div>
                    <div id="signupdone-wrapper" style="visibility: hidden">
                        <p>가입이 완료되었습니다!</p>
                        <p><a href="https://mail.kaist.ac.kr">카이스트 메일</a>로 온 인증 메일을 확인해주세요.</p>
                        <p class="signupdone-text-small">(메일이 최대 2시간 후에 도착할 수도 있습니다. 인내심을 갖고 기다려주세요 ㅜㅜ)</p>
                        <button class="btn-radius btn-black" onclick="changeFormState(0);">확인</button>
                    </div>
                    <div id="passwddone-wrapper" style="visibility: hidden">
                        <p>임시 비밀번호가 전송되었습니다.</p>
                        <p><a href="https://mail.kaist.ac.kr">카이스트 메일</a>을 확인해주세요.</p>
                        <p class="signupdone-text-small">(메일이 최대 2시간 후에 도착할 수도 있습니다. 인내심을 갖고 기다려주세요 ㅜㅜ)</p>
                        <button class="btn-radius btn-black" onclick="changeFormState(0);">확인</button>
                    </div>
                </div>
            </div>
        </div>
        <?php script('assets/js/login.js'); ?>
    </main>
    <input id="login-redirect-href" type="hidden" value="<?php echo $_GET['r']; ?>">
</body>