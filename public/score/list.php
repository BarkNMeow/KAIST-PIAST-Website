<?php
    require_once '../../api/util.php';
    require_once '../../api/bbsquery.php';

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

    if(!$_GET['p']) $_GET['p'] = 0;
    if(!$_GET['pp']) $_GET['pp'] = 10;

    $navbartitle = '악보';
?>
<!DOCTYPE html>
<head>
    <?php include '../parts/head.php'?>
    <link href="../assets/css/bbs/list.css" rel="stylesheet">
    <link href="../assets/css/board.css" rel="stylesheet">
    <link href="../assets/css/score/list.css" rel="stylesheet">
</head>
<body>
    <header class="border-bottom">
        <?php include '../parts/navbar.php'?>
    </header>
    <main>
        <!-- <table class="bbs-option-wrapper">
            <tr>
                <td>검색어</td>
                <td>
                    <div class="search-wrapper">
                        <div class="search-window">
                            <select class="search-option form-control">
                                <?php
                                    // $option_sel[$_GET['sop']] = 'selected';
                                    // echo '<option value="1" '.$option_sel[1].'>제목</option>';
                                    // echo '<option value="3" '.$option_sel[3].'>아티스트</option>';
                                    // echo '<option value="2" '.$option_sel[2].'>본문</option>';
                                    // echo '<option value="4" '.$option_sel[4].'>이름</option>';
                                ?>
                            </select>
                            <span>|</span>
                            <input class="search-input form-control" value="<?php echo $_GET['s']?>" placeholder="빈칸으로 놔둘 시 무시됩니다.">
                        </div>
                        <button class="btn-grey" id="btn-search-score">검색</button>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    종류
                </td>
                <td>
                    <?php
                        // $nmlist = array('', '피아노', '연탄곡');
                        // $chklist = _one_bit($_GET['type']);
                        // for($i = 1; $i < count($nmlist); $i++){
                        //     $checked = (in_array($i, $chklist) ? ' checked' : '');
                        //     echo '<span class="radio-wrapper">'.$nmlist[$i].' <input type="checkbox" name="score-type" value="'.$i.'"'.$checked.'></span> ';
                        // }

                        // echo '<span class="radio-wrapper">기타 <input type="checkbox" name="score-type" value="0" '.(in_array(0, $chklist) ? ' checked' : '').'></span>';
                    ?>
                    
                </td>
            </tr>
            <tr>
                <td>
                    장르
                </td>
                <td>
                    <?php
                        // $nmlist = array('', '클래식', 'K-POP', '해외 팝', 'OST', '재즈', '뉴에이지');
                        // $chklist = _one_bit($_GET['genre']);
                        // for($i = 1; $i < count($nmlist); $i++){
                        //     $checked = (in_array($i, $chklist) ? ' checked' : '');
                        //     echo '<span class="radio-wrapper">'.$nmlist[$i].' <input type="checkbox" name="score-genre" value="'.$i.'"'.$checked.'></span> ';
                        // }

                        // echo '<span class="radio-wrapper">기타 <input type="checkbox" name="score-genre" value="0" '.(in_array(0, $chklist) ? ' checked' : '').'></span>';
                    ?>
                </td>
            </tr>
            <tr>
                <td>난이도</td>
                <td>
                    <div class="score-diff-wrapper">
                        최소 <input id="score-diff-min" class="form-control" placeholder="1" value="<?php echo (isset($_GET['mind']) ? $_GET['mind'] : '')?>">
                        최대 <input id="score-diff-max" class="form-control" placeholder="10" value="<?php echo (isset($_GET['maxd']) ? $_GET['maxd'] : '')?>">
                        <span>(1 = <i class="bi bi-star-half"></i>)</span>
                    </div>
                </td>
            </tr>
        </table> -->
        <div class="gallery">
            <?php
                $result = load_gallery(BBS_SCORE, $_GET['p'], $_GET['pp'], $_GET['sop'], $_GET['s']);
                echo $result['content'];
            ?>
        </div>
        <div class="option-wrapper">
            페이지 당
            <select class="form-control" id="page-select">
            <?php
                $page_sel[$_GET['pp']] = 'selected';
                echo '<option value="15"'.$page_sel[15].'>15개</option>';
                echo '<option value="30"'.$page_sel[30].'>30개</option>';
                echo '<option value="50"'.$page_sel[50].'>50개</option>';
            ?>
            </select>
            <?php echo '<a href="write"><button class="btn-white btn-radius">악보 업로드</button></a>'; ?>
        </div>
        <div class="bbs-nav-wrapper">
            <div>
                <div class="selector-wrapper">
                <?php
                    echo $result['selector'];
                ?>
                </div>
            </div>
            <div>
                <div class="search-window">
                    <select class="search-option">
                        <?php
                            $option_sel[$_GET['sop']] = 'selected';
                            echo '<option value="1" '.$option_sel[1].'>제목</option>';
                            echo '<option value="2" '.$option_sel[2].'>본문</option>';
                            echo '<option value="3" '.$option_sel[3].'>태그</option>';
                            echo '<option value="4" '.$option_sel[4].'>이름</option>';
                        ?>
                    </select>
                    <span>|</span>
                    <input class="search-input" id="input-bbs-search" value="<?php echo $_GET['s']?>">
                </div>
                <button class="btn-white btn-radius" id="btn-bbs-search"><i class="bi bi-search"></i></button>
            </div>
        </div>
    </main>
    <footer>
        <?php include '../parts/footer.php'?>
    </footer>
    <?php
        echo '<input value="'.$_GET['p'].'" id="page" style="display:none;">';
        echo '<input value="'.(($_GET['s'] || $_GET['type'] || $_GET['genre'] || $_GET['mind'] || $_GET['maxd']) ? 1 : 0).'" id="issearch" style="display:none;">';
    ?>
    <script src="../assets/js/score/list.js"></script>
</body>