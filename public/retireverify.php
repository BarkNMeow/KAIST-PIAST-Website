<?php
    require_once '../api/util.php';

    global $pdo;

    if(isset($_GET['code']) && isset($_GET['email'])){
        $sql = $pdo->prepare('SELECT * FROM userinfo WHERE email = :email AND usrhash = :usrhash');
        $sql->bindParam(':usrhash', $_GET['code']);
        $sql->bindParam(':email', $_GET['email']);
        $sql->execute();
        $row = $sql->fetch();

        if($row){
            $sql = $pdo->prepare('DELETE FROM userinfo WHERE email = :email');
            $sql->bindParam(':email', $_GET['email']);
            $sql->execute();
            alert("계정 삭제가 완료되었습니다.");            
        } else {
            alert("유효하지 않은 링크입니다.");
        }

        redirect("index.php");
    }
?>