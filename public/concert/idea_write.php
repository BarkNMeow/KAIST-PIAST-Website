<?php
    require_once '../../api/util.php';
    require_once '../../api/concertquery.php';

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

    $config = updateIdeaConfig();
    if(!$config['active']){
        alert('테마 투표 기간이 아닙니다!');
        redirect('idea');
        return;
    }

    global $pdo;

    try{
        if(isset($_GET['i'])){
            $sql = $pdo->prepare('SELECT * FROM concertidea WHERE id = :searchid');
            $sql->bindParam(':searchid', $_GET['i']);
            $sql->execute();
            
            $row = $sql->fetch();
    
            if(!$row){
                alert('존재하지 않는 글입니다.');
                redirect('idea');
                return;
            }
    
            if($row['email'] != $_SESSION['email']){
                alert('자기 자신의 글만 수정할 수 있습니다.');
                redirect('idea');
                return;
            }
            
        } else {
            $sql = $pdo->prepare('SELECT sem from userinfo WHERE email = :email');
            $sql->bindParam(':email', $_SESSION['email']);
            $sql->execute();
            
            $sem = ($sql->fetch())['sem'];
        
            if($sem >= 4){
                alert('OB는 아이디어를 제안할 수 없습니다!');
                redirect('idea');
                return;
            }
        }

    } catch(Exception $e){
        alert('유효하지 않은 접근입니다.');
        errlog($e);
        redirect('idea');
        return;
    }

    $navbartitle = '연주회';
?>
<!DOCTYPE html>
<head>
    <?php include '../parts/head.php'?>
    <?php css("../assets/css/concert/idea_write.css"); ?>
    <?php include '../parts/quilljs.php'?>
</head>
<body>
    <header>
        <?php include '../parts/navbar.php'?>
        <?php include 'subnavbar.php'?>
        <script>makeSubNavFocus(1);</script>
    </header>
    <main>
        <table class="idea-table">
            <tr>
                <td>
                    글 제목
                </td>
                <td>
                    <input class="form-control idea-title" placeholder="글 제목" maxlength="60" value="<?php if(isset($_GET['i'])) echo $row['title']; ?>">
                </td>
            </tr>
            <tr>
                <td>
                    테마 제목
                </td>
                <td>
                    <input class="form-control idea-theme-title" placeholder="테마 제목" maxlength="30"value="<?php if(isset($_GET['i'])) echo $row['theme']; ?>">
                </td>
            </tr>
        </table>
        <div class="idea-title-info mt-1">
            * 글 제목은 "테마 제안" 창에 보여지는 제목이며, 테마 제목은 추후 "연주회 신청" 창에 드러납니다.
        </div>
        <div id="quill">
            <?php if(isset($_GET['i'])) echo purify_full($row['main']); ?>
        </div>
        <table class="chklist-table">
            <colgroup>
                <col style="width: calc(100% - 8rem)">
                <col style="width: 4rem">
                <col style="width: 4rem">
            </colgroup>
            <tr>
                <td colspan="3">
                    <i class="bi bi-music-note-list"></i> 셀프 체크리스트
                </td>
            </tr>
            <tr>
                <td>
                    최근에 비슷하거나 동일한 테마가 있었나요? (<a href="view_theme" target="listofconcerts">리스트 보기</a>)
                </td>
                <td>
                    Y
                    <input type="radio" name="chk1" id="chk1y">
                </td>
                <td>
                    N
                    <input type="radio" name="chk1" id="chk1n">
                </td>
            </tr>
            <tr>
                <td>
                    테마가 연주회가 열리는 상황과 안어울리나요?
                </td>
                <td>
                    Y
                    <input type="radio" name="chk2" id="chk2y">
                </td>
                <td>
                    N
                    <input type="radio" name="chk2" id="chk2n">
                </td>
            </tr>
            <tr>
                <td>
                    다양한 곡들이 나올 수 있는 테마인가요?
                </td>
                <td>
                    Y
                    <input type="radio" name="chk3" id="chk3y">
                </td>
                <td>
                    N
                    <input type="radio" name="chk3" id="chk3n">
                </td>
            </tr>
            <tr>
                <td>
                    (정기연주회에 비해) 대중적인 공감을 얻을 수 있는 테마인가요?
                </td>
                <td>
                    Y
                    <input type="radio" name="chk4" id="chk4y">
                </td>
                <td>
                    N
                    <input type="radio" name="chk4" id="chk4n">
                </td>
            </tr>
        </table>
        <div class="submit-wrapper">
            <?php
                if(isset($_GET['i'])) echo '<button>수정하기</button>';
                else echo '<button disabled>제안하기</button>';
            ?>
        </div>
    </main>
    <footer>
        <?php include '../parts/footer.php'?>
    </footer>
    <input value="<?php echo $_GET['i']?>" id="id" style="display:none;">
    <?php script("../assets/js/concert/idea_write.js"); ?>
</body>