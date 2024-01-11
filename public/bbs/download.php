<?php
    require_once '../../api/util.php';

    if(!logged_in() || !is_auth()){
        alert('인증받은 PIAST 부원만 다운로드 가능합니다!');
        return;
    }

    if(!isset($_GET['id']) || !isset($_GET['downloadhash'])){
        alert('인자 설정이 잘못되었습니다!');
        return;
    }

    if(update_querycnt()){
        global $pdo;
        try{
            $sql = $pdo->prepare('SELECT filehash, filenm FROM file WHERE id = :id AND downloadhash = :downloadhash');
            $sql->bindParam(':id', $_GET['id']);
            $sql->bindParam(':downloadhash', $_GET['downloadhash']);
            $sql->execute();
            $row = $sql->fetch();
    
        } catch(Exception $e){
            alert('요청 처리 중 오류가 발생했습니다.');
            errlog($e);
            return;
        }
    
        if(!$row){
            alert('존재하지 않는 파일입니다!');
            return;
        }
    
        $file_url = '../../download/'.$row['filehash'].$_GET['id'];
    
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.$row['filenm'].'"');
    
        ob_clean();
        flush();
    
        readfile($file_url);
        exit;
    } else {
        alert('너무 많이 요청했습니다 >:(\n잠시 후 다시 시도해주세요.');
        return;
    }    
?>