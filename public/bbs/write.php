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

    if(isset($_GET['i']) && isset($_GET['resp'])){
        alert('인자가 잘못 설정되어 있습니다.');
        redirect('list?i='.$_GET['i']);
        return;
    }

    if(isset($_GET['i']) || isset($_GET['resp'])){
        $searchid = isset($_GET['i']) ? $_GET['i'] : $_GET['resp'];

        global $pdo;
        $sql = $pdo->prepare('SELECT * FROM bbs WHERE id = :searchid');
        $sql->bindParam(':searchid', $searchid);
        $sql->execute();
        $row = $sql->fetch();

        if(!$row){
            alert('존재하지 않는 글입니다.');
            redirect('list?i='.$_GET['i']);
            return;
        }

        if(isset($_GET['resp'])){
            if($row['id'] != $row['resp']){
                alert('답글에는 답글을 달 수 없습니다.');
                redirect('list?i='.$_GET['i']);    
                return;        
            }
        } else {
            if($row['email'] != $_SESSION['email']){
                alert('자기 자신의 글만 수정할 수 있습니다.');
                redirect('list?i='.$_GET['i']);
                return;
            }
        }
    }
    
    $navbartitle = '게시판';
?>
<!DOCTYPE html>
<head>
    <?php include '../parts/head.php'?>
    <?php css("../assets/css/bbs/write.css"); ?>
    <?php include '../parts/quilljs.php'?>
</head>
<body>
    <div id="overlay" class="overlay-shadow" style="display: none">
        <div class="overlay-wrapper">
            <i class="bi-x-lg bi" onclick="$('#overlay').hide();"></i>
            <div class="overlay-title">파일 업로드</div>
            <div class="file-table-header">
                <div>이름</div>
                <div>크기</div>
                <div><i class="bi bi-upload"></i></div>
            </div>
            <div id="file-table-wrapper">
            </div>
            <div class="file-bottom">
                <button class="btn-radius btn-white" onclick="$('#bbs-file').click()" id="bbs-file-add">파일 추가</button>
                <div class="file-limit">
                    <span id="file-limit-size">크기: 0 / 32MB</span>
                    <span id="file-limit-cnt">개수: 0 / 10개</span>
                </div>
            </div>
        </div>
    </div>
    <header>
        <?php include '../parts/navbar.php'?>
        <?php include 'subnavbar.php'?>
        <?php echo '<script>makeSubNavFocus('.$_GET['b'].');</script>'?>
    </header>
    <main>
        <div class="bbs-form">
            <div>
                <input id="bbs-title" placeholder="제목" maxlength="60" value="<?php if(isset($_GET['i'])) echo $row['title']; ?>">
            </div>
            <div>
                <select id="bbs-type" <?php if(isset($_GET['i']) or isset($_GET['resp'])) echo 'disabled'; ?>>
                    <option value="">게시판 선택</option>
                    <?php
                        $arr[$_GET['b']] = ' selected';
                        if(is_exec())
                            echo '<option'.$arr[1].' value="1">공지사항</option>';
                        
                        echo '<option'.$arr[2].' value="2">자유게시판</option>';
                        echo '<option'.$arr[3].' value="3">건의사항</option>';
                        echo '<option'.$arr[4].' value="4">갤러리</option>';
                    ?>
                </select>
                <div id="bbs-tag-fake">
                    <?php
                        if($row['tag']){
                            $taglist = explode(' ', $row['tag']);
                            foreach($taglist as $idx => $tag){
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
            <button class="btn-white btn-radius" onclick="$('#overlay').show();">첨부 파일</button>
            <span id="bbs-file-summary"></span>
        </div>
        <div class="submit-wrapper">
            <?php
                if(isset($_GET['i'])) echo '<button class="btn-radius btn-black">수정하기</button>';
                else echo '<button class="btn-radius btn-black" disabled>글쓰기</button>';
            ?>
        </div>
    </main>
    <footer>
        <?php include '../parts/footer.php'?>
    </footer>
    <?php
        $resp = isset($_GET['resp']) ? $_GET['resp'] : 0;
        $i = isset($_GET['i']) ? $_GET['i'] : 0;
        echo '<input value="'.$resp.'" id="resp" style="display:none;">';
        echo '<input value="'.$i.'" id="i" style="display:none;">';
        echo '<input type="hidden" id="tag" value="'.$row['tag'].'">';
    ?>
    <input type="file" class="form-control" id="bbs-file" style="display:none;" multiple>
    <input value="0" id="size" style="display:none;">
    <input value="0" id="filecnt" style="display:none;">
    <?php script("../assets/js/bbs/write.js"); ?>
</body>