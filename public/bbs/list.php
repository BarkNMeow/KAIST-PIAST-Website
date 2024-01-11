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

    if(!$_GET['b']) $_GET['b'] = 0;
    if(!$_GET['p']) $_GET['p'] = 0;
    if(!$_GET['pp']) $_GET['pp'] = 10;

    $navbartitle = '게시판';
?>
<!DOCTYPE html>
<head>
    <?php include '../parts/head.php'?>
    <?php css("../assets/css/bbs/list.css"); ?>
    <?php css("../assets/css/board.css"); ?>
</head>
<body>
    <header>
        <?php include '../parts/navbar.php'?>
        <?php include 'subnavbar.php'?>
        <?php echo '<script>makeSubNavFocus('.$_GET['b'].');</script>'?>
    </header>
    <main>
        <?php
            if($_GET['b'] >= 0){
                echo '<table class="board">
                        <colgroup>
                            <col style="width: 7.5%; min-width: 4rem">
                            <col style="width: 10%; min-width: 4.5rem">
                            <col class="board-name">
                            <col style="width: auto">
                            <col style="width: 6%; min-width: 3.75rem">
                            <col style="width: 6%; min-width: 3.75rem">
                        </colgrouop>
                        <tr>
                            <th>
                                '.($_GET['b'] == 0 ? '종류' : '#').'
                            </th>
                            <th>
                                작성
                            </th>
                            <th>
                                이름
                            </th>
                            <th>
                                제목
                            </th>
                            <th>
                                조회
                            </th>
                            <th>
                                <i class="bi bi-heart-fill"></i>
                            </th>
                        </tr>';
                
                $result = load_board($_GET['b'], $_GET['p'], $_GET['pp'], $_GET['sop'], $_GET['s']);
                echo $result['content'];
                echo '</table>';

            } else {
                echo '<div class="gallery">';
                $result = load_gallery($_GET['b'], $_GET['p'], $_GET['pp'], $_GET['sop'], $_GET['s']);
                echo $result['content'];
                echo '</div>';
            }
        ?>
        </table>
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
            <?php echo '<a href="write?b='.$_GET['b'].'"><button class="btn-white btn-radius">글쓰기</button></a>'; ?>
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
        echo '<input value="'.$_GET['b'].'" id="bbstype" style="display:none;">';
        echo '<input value="'.($_GET['s'] ? 1 : 0).'" id="issearch" style="display:none;">';
    ?>
    <?php script("../assets/js/bbs/list.js"); ?>
</body>