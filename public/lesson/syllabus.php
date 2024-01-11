<?php
    require_once '../../api/util.php';
    require_once '../../api/lessonquery.php';

    if(!logged_in() || !is_auth()){
        echo '<script>window.close();</script>';
    } else {
        global $pdo;
        try{
            $sql = $pdo->prepare("SELECT l.*, CONCAT(u.gen, '기 ', u.nm) AS gennm, bbs.* FROM 
                                lessonlist l LEFT JOIN userinfo u ON l.email = u.email LEFT JOIN bbs ON l.postid = bbs.id WHERE l.id = :id");
            $sql->bindParam(':id', $_GET['i']);
            $sql->execute();
            $one = $sql->fetch();
            
        } catch(Exception $e){
            alert('무언가가 잘못되었습니다...');
            errlog($e);
            return;
        }
    }
?>
<!DOCTYPE html>
<head>
    <?php include '../parts/head.php'?>
    <?php css("../assets/css/board.css"); ?>
    <?php css("../assets/css/lesson/syllabus.css"); ?>
</head>
<body>
    <main>
        <div class="top-banner">
            Syllabus
        </div>
        <div class="table-title">
            Information of Lesson
        </div>
        <table>
            <colgroup>
                <col style="width: 6rem">
                <col style="width: calc(50% - 6rem)">
                <col style="width: 4rem">
                <col style="width: calc(50% - 4rem)">
            </colgroup>
            <tr>
                <td>레슨 제목</td>
                <td colspan="3"><?php echo htmlspecialchars($one['title']); ?></td>
            </tr>
            <tr>
                <td>레슨 담당</td>
                <td><?php echo $one['gennm']; ?></td>
                <td>정원</td>
                <td><?php echo $one['maxstudent'].'명'; ?></td>
            </tr>
        </table>
        <div class="table-title mt-3">
            Plan of Lesson
        </div>
        <div class="main mt-2">
            <?php echo purify_full($one['main']); ?>
        </div>
    </main>
</body>