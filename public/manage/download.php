<?php
    require_once '../../api/util.php';
    require_once '../../api/SimpleXLSXGen.php';

    if(!logged_in() || !is_exec() || !update_querycnt()){
        exit;
    }

    if($_GET['w'] == 'all'){
        global $pdo;
        try{
            $sql = $pdo->prepare('SELECT * FROM scoreinfo ORDER BY id ASC');
            $sql->execute();
            $scorerows = $sql->fetchAll();

            $ascoreif = '';
            $pscoreif = '';
            $ascoredesc = array('이름', '친목조 참석', '레슨 출석', '친목조장', '레슨부장');
            $pscoredesc = array('이름', '정모', '테마연주회', '정기연주회');

            foreach($scorerows as $row){    
                if($row['type'] == 0) $ascoreif .= 'IF(u.email IN (SELECT email FROM scorelist WHERE scoreid = '.$row['id'].'), '.$row['quantity'].', 0),';
                else $pscoreif .= 'IF(u.email IN (SELECT email FROM scorelist WHERE scoreid = '.$row['id'].'), '.$row['quantity'].', 0),';

                if($row['info'] === '') $desc = $row['tmi'];
                else {
                    if($row['tmi'] === '') $desc = $row['info'];
                    else $desc = $row['info'].'('.$row['tmi'].')';
                }

                if($row['type'] == 0) array_push($ascoredesc, $desc);
                else array_push($pscoredesc, $desc);
            }

            array_push($ascoredesc, '활동점수 총계');
            array_push($pscoredesc, '피아노점수 총계');

            $sql = $pdo->prepare("SELECT
                                CONCAT(u.gen, '기 ', u.nm),
                                IF(u.amityatt >= :att2, 2, IF(u.amityatt >= :att1, 1, 0)),
                                IF(ls.att >= :lessonatt, 1, 0) AS lessonatt,
                                IF(u.email IN (SELECT email FROM amitygroup), 2, 0),
                                IF(u.email IN (SELECT email FROM lessonlist), 2, 0),
                                ".$ascoreif."
                                u.ascore
                                FROM userinfo u
                                LEFT JOIN lessonstudent ls ON u.email = ls.email
                                WHERE u.sem < 4 AND u.rest = 0 ORDER BY u.gen ASC, u.nm ASC");
            $sql->bindValue(':att1', REQ_AMITY_ATT1);
            $sql->bindValue(':att2', REQ_AMITY_ATT2);
            $sql->bindValue(':lessonatt', REQ_LESSON_ATT);
            $sql->execute();
            $arows = $sql->fetchAll();

            $sql = $pdo->prepare("SELECT
                                CONCAT(gen, '기 ', nm) AS gennm,
                                (SELECT COUNT(*) FROM jungmopost WHERE email = u.email),
                                (SELECT COUNT(*) * 2 FROM concertsong WHERE email = u.email AND ideaid = -1),
                                (SELECT COUNT(*) * 2 FROM concertsong WHERE email = u.email AND ideaid = 0),
                                ".$pscoreif."
                                pscore
                                FROM userinfo u WHERE sem < 4 AND rest = 0 ORDER BY gen ASC, nm ASC");
            $sql->execute();
            $prows = $sql->fetchAll();

            $sql = $pdo->prepare("SELECT info.*, chk.att, CONCAT(u.gen, '기 ', u.nm) AS gennm, u.jmscore FROM
                                jungmoinfo info
                                LEFT JOIN jungmochk chk ON info.id = chk.jungmoid
                                LEFT JOIN userinfo u ON chk.email = u.email
                                WHERE u.sem < 4 AND u.rest = 0 ORDER BY u.gen ASC, u.nm ASC, info.date ASC");
            $sql->execute();
            $jrows = $sql->fetchAll();

            $currenttime = Date('Y/m/d H:i:s');
            
            $array = array();
            array_push($array, $ascoredesc);
            foreach($arows as $row){
                $tmparr = array();
                foreach($row as $one){
                    array_push($tmparr, '<left>'.($one ? $one : '').'</left>');
                }
                array_push($array, $tmparr);
            }
            $xlsx = Shuchkin\SimpleXLSXGen::fromArray($array, '활동점수');

            $array = array();
            array_push($array, $pscoredesc);
            foreach($prows as $row){
                $tmparr = array();
                foreach($row as $one){
                    array_push($tmparr, '<left>'.($one ? $one : '').'</left>');
                }
                array_push($array, $tmparr);
            }
            $xlsx = $xlsx->addSheet($array, '피아노점수');

            $jungmodate = array_unique(array_column($jrows, 'date'));
            $jungmohead = array_merge(array('이름'), $jungmodate, array('정모 출석 총계'));

            $array = array();
            $iconlist = array('X', '△', 'O', '-');
            array_push($array, $jungmohead);
            
            $cnt = 0;
            $tmparr = array();
            foreach($jrows as $row){
                if(count($tmparr) == 0) array_push($tmparr, '<left>'.$row['gennm'].'</left>');
                
                array_push($tmparr, '<left>'.$iconlist[$row['att'] % 4].'</left>');
                $cnt++;

                if($cnt > 0 && $cnt % count($jungmodate) == 0){
                    array_push($tmparr, '<left>'.round($row['jmscore'] / 2, 1).'</left>');
                    array_push($array, $tmparr);
                    $tmparr = array();
                }
            }

            $xlsx = $xlsx->addSheet($array, '정모 출석');


            $xlsx->setDefaultFont('맑은 고딕');
            $xlsx->setDefaultFontSize(11);
            $xlsx->setColWidth(1, 15);
            $xlsx->downloadAs($currenttime.' - 전체 활동 내역.xlsx');
            exit;

        } catch(Exception $e){
            alert('요청 처리 도중 오류가 발생했습니다.');
            errlog($e, 'download(backup, all)');
            return;
        }
    }

    else if($_GET['w'] == 'm'){
        global $pdo;
        try{
            $sql = $pdo->prepare("SELECT CONCAT(u.gen, '기 ', u.nm) AS gennm, m.due, m.duepaid, m.duepaiddate, m.fine + m.bill AS finebill, m.finebillpaid FROM
                                    money m INNER JOIN userinfo u ON m.email = u.email WHERE u.rest = 0 ORDER BY u.gen ASC, u.nm ASC");
            $sql->execute();
            $rows = $sql->fetchAll();

            $array = array(array('<center><middle>이름</middle></center>', '<center>회비</center>', null, null, '<center>벌금 및 정산</center>', null), array(null, '필요', '납부', '날짜', '필요', '납부'));
            foreach($rows as $row){
                $tmparr = array();
                foreach($row as $one){
                    array_push($tmparr, '<left>'.$one.'</left>');
                }
                array_push($array, $tmparr);
            }
    
            $currenttime = Date('Y/m/d H:i:s');
            $xlsx = Shuchkin\SimpleXLSXGen::fromArray( $array );
            $xlsx->setDefaultFont('맑은 고딕');
            $xlsx->setDefaultFontSize(11);
            $xlsx->setColWidth(1, 15);
            $xlsx->mergeCells('B1:D1');
            $xlsx->mergeCells('E1:F1');
            $xlsx->mergeCells('A1:A2');
            $xlsx->downloadAs($currenttime.' - 회계장부.xlsx');
            exit;
        } catch(Exception $e){
            alert('요청 처리 도중 오류가 발생했습니다.');
            errlog($e, 'download(backup, money)');
            return;
        }
    }

?>