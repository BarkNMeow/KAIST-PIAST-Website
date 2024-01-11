<?php
    require_once '../../api/util.php';

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
    if(isset($_GET['i'])){
        global $pdo;

        $sql = $pdo->prepare('SELECT * FROM bbs b LEFT JOIN bbsscore s ON b.id = s.postid WHERE b.id = :postid');
        $sql->bindParam(':postid', $_GET['i']);
        $sql->execute();
        $row = $sql->fetch();

        if(!$row){
            alert('존재하지 않는 악보입니다.');
            redirect('list');
            return;
        }

        if($row['email'] != $_SESSION['email']){
            alert('자기 자신의 글만 수정할 수 있습니다.');
            redirect('list');
            return;
        }

        $sql = $pdo->prepare('SELECT filenm FROM file WHERE postid = :id');
        $sql->bindParam(':id', $_GET['i']);
        $sql->execute();
        $filenm = ($sql->fetch())['filenm'];
    }

    $navbartitle = '악보';
?>
<!DOCTYPE html>
<head>
    <?php include '../parts/head.php'?>
    <link href="../assets/css/bbs/write.css" rel="stylesheet">
    <link href="../assets/css/score/upload.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/1.10.100/pdf.min.js" integrity="sha512-yGqX3zcETFF6oOznup6VQ96a1R1NJOSl7EQXa7+a0InqhOiq1KjlmRrnkEifOfc4dxmcrbq4xeC+IxWU1MjrFw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/1.10.100/pdf.worker.min.js" integrity="sha512-Yf5+SCs+WGmAoU5LODJGlHIwGOVLlZ5C4S+TWnvfKRUzvm55WZDJjw1eJHxNpfQKBcKGEy+gIriHcaI8faCKnw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <?php include '../parts/quilljs.php'?>
</head>
<body>
    <header class="border-bottom">
        <?php include '../parts/navbar.php'?>
    </header>
    <main>
        <div class="bbs-form">
            <div>
                <input id="bbs-title" placeholder="곡 제목" maxlength="60" value="<?php if(isset($_GET['i'])) echo $row['title']; ?>">
            </div>
            <div>
                <input id="bbs-composer" placeholder="가수 또는 작곡가" maxlength="30" value="<?php if(isset($_GET['i'])) echo $row['tag']; ?>">
                <div id="bbs-tag-fake">
                    <?php
                        if($row['tag']){
                            $taglist = explode(' ', $row['tag']);
                            foreach($taglist as $id => $tag){
                                $tag = htmlspecialchars($tag);
                                echo '<button onclick="removeTag(this)" data-tag="'.$tag.'">'.$tag.'<i class="bi bi-x-lg"></i></button>';
                            }
                            
                            echo '<input id="bbs-tag" placeholder="태그 입력" maxlength="'.(40 - mb_strlen($row['tag'], 'utf-8')).'">';
                        } 
                        else echo '<input id="bbs-tag" placeholder="태그, 띄어쓰기로 구분해 입력" maxlength="40">';
                    ?>
                </div>
            </div>
        </div>
        
        <div id="quill">
            <?php if(isset($_GET['i'])) echo purify_full($row['main']); ?>
        </div>
        <div class="quill-bottom-wrapper">
            <button class="btn-white btn-radius" onclick="$('#overlay').show();">악보 첨부</button>
            <span id="bbs-file-summary"></span>
        </div>
        <table class="bbs-option-wrapper">
            <tr>
                <td>
                    종류
                </td>
                <td>
                    <?php
                        $nmlist = array('', '피아노', '연탄곡');
                        for($i = 1; $i < count($nmlist); $i++){
                            $checked = (isset($_GET['i']) && $row['scoretype'] == $i ? ' checked' : '');
                            echo '<span class="radio-wrapper">'.$nmlist[$i].' <input type="radio" name="score-type" value="'.$i.'"'.$checked.'></span> ';
                        }

                        echo '<span class="radio-wrapper">기타 <input type="radio" name="score-type" value="0" '.(isset($_GET['i']) && $row['scoretype'] == 0 ? ' checked' : '').'></span>';
                    ?>
                    
                </td>
            </tr>
            <tr>
                <td>
                    장르
                </td>
                <td>
                    <?php
                        $nmlist = array('', '클래식', 'K-POP', '해외 팝', 'OST', '재즈', '뉴에이지');
                        for($i = 1; $i < count($nmlist); $i++){
                            $checked = (isset($_GET['i']) && $row['genre'] == $i ? ' checked' : '');
                            echo '<span class="radio-wrapper">'.$nmlist[$i].' <input type="radio" name="score-genre" value="'.$i.'"'.$checked.'></span> ';
                        }

                        echo '<span class="radio-wrapper">기타 <input type="radio" name="score-genre" value="0" '.(isset($_GET['i']) && $row['genre'] == 0 ? ' checked' : '').'></span>';
                    ?>
                </td>
            </tr>
            <tr>
                <td>난이도</td>
                <td class="diff-cell">
                    <div class="diff-wrapper">
                        <?php
                            $diff = (isset($_GET['i']) ? $row['diff'] : 1);
                            echo '<input type="range" id="score-diff" min="1" max="10" value="'.$diff.'">';
                        ?>
                        <span id="score-star">
                            <?php
                                $diff_tmp = $diff;
                                for($i = 0; $i < 5; $i++){
                                    if($diff_tmp >= 2) echo '<i class="bi bi-star-fill"></i>';
                                    else if($diff_tmp == 1) echo '<i class="bi bi-star-half"></i>';
                                    else echo '<i class="bi bi-star"></i>';

                                    $diff_tmp -= 2;
                                }
                            ?>
                        </span>
                        <span id="score-diff-desc">
                            <?php
                                if(!isset($_GET['i'])) echo '드래그해서 입력';
                                else {
                                    if($diff <= 2) echo '나비야';
                                    else if($diff <= 4) echo '쉬움';
                                    else if($diff <= 6) echo '적절';
                                    else if($diff <= 8) echo '어려움';
                                    else if($diff == 9) echo 'Liszt에게 쉬움';
                                    else echo '겁.나.어.렵.습.니.다!';
                                }
                            ?>
                        <span>
                    </div>
                </td>
            </tr>
        </table>
        <div class="submit-wrapper">
            <?php
                if(isset($_GET['i'])) echo '<button class="btn-radius btn-black">수정하기</button>';
                else echo '<button class="btn-radius btn-black" disabled>업로드</button>';
            ?>
        </div>
    </main>
    <footer>
        <?php include '../parts/footer.php'?>
    </footer>
    <?php
        $i = isset($_GET['i']) ? $_GET['i'] : 0;
        echo '<input value="'.$i.'" id="i" style="display:none;">';
    ?>
    <input type="file" class="form-control" id="bbs-file" style="display:none;" accept=".pdf">
    <canvas id="page1" style="display: none"></canvas>
    <canvas id="page2" style="display: none"></canvas>
    <?php script('../assets/js/score/upload.js'); ?>
</body>