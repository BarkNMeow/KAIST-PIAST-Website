<?php
    require_once '../../api/util.php';
    require_once '../../api/comquery.php';

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

    global $pdo;
    try{
        $sql = $pdo->prepare("SELECT bbs.*, CONCAT(userinfo.gen, '기 ', userinfo.nm) AS gennm FROM bbs LEFT JOIN userinfo ON bbs.email = userinfo.email WHERE bbs.id = :postid AND bbs.bbstype >= -1");
        $sql->bindParam(':postid', $_GET['i']);
        $sql->execute();
        $row = $sql->fetch();

        if(!$row or ($row['view'] == -1)){
            alert('존재하지 않거나 삭제된 게시물입니다.');
            redirect('list?b='.$_GET['b'].'&p='.$_GET['p'].'&pp='.$_GET['pp']);
            return;
        }

        $sql = $pdo->prepare('SELECT * FROM bbslike WHERE postid = :postid AND email = :email');
        $sql->bindParam(':postid', $_GET['i']);
        $sql->bindParam(':email', $_SESSION['email']);
        $sql->execute();
        $likerow = $sql->fetch();

        if(!$likerow){
            $sql = $pdo->prepare('INSERT INTO bbslike (postid, email) VALUES (:postid, :email)');
            $sql->bindParam(':postid', $_GET['i']);
            $sql->bindParam(':email', $_SESSION['email']);
            $sql->execute();

            $sql = $pdo->prepare('UPDATE bbs SET view = view + 1 WHERE id = :id');
            $sql->bindParam(':id', $_GET['i']);
            $sql->execute();

            $row['view'] += 1;
            $liked = false;
        } else {
            $liked = $likerow['lk'];
        }

        $sql = $pdo->prepare('SELECT * FROM file WHERE postid = :id');
        $sql->bindParam(':id', $_GET['i']);
        $sql->execute();
        $filelist = $sql->fetchAll();

    } catch(Exception $e){
        alert('유효하지 않은 접근입니다.');
    }

    if(!$_GET['b']) $_GET['b'] = 0;
    
    $navbartitle = '게시판';
?>
<!DOCTYPE html>
<head>
    <?php include '../parts/head.php'?>
    <link href="//cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <?php css("../assets/css/postview.css"); ?>
    <script src="//cdn.quilljs.com/1.3.6/quill.js"></script>
    <script src="https://unpkg.com/quill-image-compress@1.2.11/dist/quill.imageCompressor.min.js"></script>
</head>
<body>
    <header>
        <?php include '../parts/navbar.php'?>
        <?php include 'subnavbar.php'?>
        <?php echo '<script>makeSubNavFocus('.$_GET['b'].');</script>'?>
    </header>
    <main>
        <div class="post-title border-bottom">
            <?php echo htmlspecialchars($row['title']) ?>
            <?php
                $delcond = (is_exec() || $row['email'] == $_SESSION['email']);
                $notcond = (is_exec() && $row['bbstype'] == 1);
                $repcond = ($row['id'] == $row['resp'] && $row['bbstype'] >= 2);
                $fixcond = ($row['email'] == $_SESSION['email']);

                if($delcond || $notcond || $repcond || $fixcond){
                    echo '<div class="post-menu">
                            <button id="post-dropdown-btn">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <div id="post-dropdown" class="post-menu-dropdown" style="display: none">';
                    
                    if($delcond) echo '<div id="post-delete-btn">삭제<i class="bi bi-trash-fill"></i></div>';
                    if($notcond) echo '<div id="post-notice-btn">공지 '.($row['notice'] ? '삭제' : '등록').'<i class="bi bi-megaphone-fill"></i></div>';
                    if($repcond) echo '<a href="write?b='.$row['bbstype'].'&resp='.$row['id'].'">답글 쓰기<i class="bi bi-reply-fill"></i></a>';
                    if($fixcond) echo '<a href="write?b='.$row['bbstype'].'&i='.$row['id'].'">수정<i class="bi bi-pencil-fill"></i></a>';

                    echo '</div></div>';
                }
            ?>
        </div>
        <div class="post-info">
            <?php 
                echo $row['gennm'].'('.$row['email'].') / '.$row['date'].' / 조회수: '.$row['view'];
                if($row['modcnt'] > 0) {
                    echo ' / 수정 ';
                    if($row['modcnt'] >= 100) echo '100회 이상';
                    else echo $row['modcnt'].'회';
                }
            ?>
        </div>
        <div class="main-post ql-editor">
            <?php echo purify_full($row['main']); ?>
        </div>
        <div class="post-option-wrapper">
            <?php
                if($liked) echo '<button id="post-like-btn" class="post-like-btn btn-radius liked"><i class="bi bi-heart-fill"></i> '.$row['lk'].'</button>';
                else echo '<button id="post-like-btn" class="post-like-btn btn-radius"><i class="bi bi-heart"></i> '.$row['lk'].'</button>';
            ?>

            <button class="btn-white btn-radius" id="post-com-btn"><i class="bi bi-chat-dots"></i> <?php echo $row['comcnt']; ?></button>

            <a href="<?php echo 'list?b='.(isset($_GET['b']) ? $_GET['b'] : $row['bbstype']).'&sop='.$_GET['sop'].'&s='.$_GET['s'].'&p='.$_GET['p'].'&pp='.$_GET['pp']; ?>">
                <button class="btn-black btn-radius">목록 <i class="bi bi-list"></i>
            </button></a>
        </div>
        <div class="tag-wrapper">
            <i class="bi bi-tags-fill"></i>
            <?php
                if($row['tag'] != ''){
                    $taglist = explode(' ', $row['tag']);
                    foreach($taglist as $tag){
                        echo '<a href="list?b='.$row['bbstype'].'&sop=3&s='.$tag.'&p='.$_GET['p'].'&pp='.$_GET['pp'].'">#'.htmlspecialchars($tag).'</a>&nbsp;&nbsp;';
                    }
                } else {
                    echo '게시물에 등록된 태그가 없습니다.';
                }
            ?>
        </div>
        <div class="tab-btn-wrapper com-file-tab border-bottom">
            <div class="tab-btn-menu-wrapper">
                <button class="tab-btn selected" onclick="changeTab(0, this);" id="comment-tab-btn">댓글 (<?php echo $row['comcnt'];?>)</button>
            </div>
            <div class="tab-btn-menu-wrapper">
                <button class="tab-btn" onclick="changeTab(1, this);">첨부파일 (<?php echo count($filelist)?>)</button>
            </div>
            <div id="tab-btn-border-bottom"></div>
        </div>
        <div class="tab-container" id="comment-tab">
            <?php echo load_comment($_GET['i'])['content']; ?>
            <div id="comment-input-container" class="border-bottom">
                <div id="quill">
                </div>
                <div class="ql-bottom">
                    <span id="comment-char-cnt">11 / 2000</span>
                    <button class="com-cancel-btn">
                        취소
                    </button>
                    <button class="com-btn">
                        게시
                    </button>
                </div>
            </div>
        </div>
        <div class="tab-container" style="display: none;">
            <?php
                if($filelist){
                    echo '<div class="file-wrapper border">';
                    foreach($filelist as $file){
                        $icon = getfileicon($file['filenm']);
                        echo '<a class="file-nm" href="download?id='.$file['id'].'&downloadhash='.$file['downloadhash'].'" download>'.$icon.'&nbsp;'.htmlspecialchars($file['filenm']).'</a><br>';
                    }
                    echo '</div>';
                } else {
                    echo '<div class="file-wrapper border text-grey">첨부파일이 없습니다!</div>';
                }
            ?>
        </div>
    </main>
    <footer>
        <?php include '../parts/footer.php'?>
    </footer>
    <?php
        echo '<input value="'.$_GET['i'].'" id="id" style="display:none;">';
    ?>
    <input value="0" id="comid" style="display:none;">
    <input value="0" id="respid" style="display:none;">
    <input value="999999" id="inputpos" style="display:none;">
    <input value="0" id="hiddenpos" style="display:none;">
    <?php script("../assets/js/bbs/view.js"); ?>
</body>