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
    
    $navbartitle = '정모';
?>
<!DOCTYPE html>
<head>
    <?php include '../parts/head.php'?>
    <?php css("../assets/css/jungmo/video.css"); ?>
    <?php css("../assets/css/subnavbar.css"); ?>
</head>
<body>
    <header>
        <?php include '../parts/navbar.php'?>
        <?php include 'subnavbar.php'?>
        <script>makeSubNavFocus(1);</script>
    </header>
    <main>
        <!-- 아래부터 정모 영상 -->
        <div class="jungmo-list-wrapper">
            <div id="player-wrapper">
            </div>
            <?php
                $fp = fopen('../../data/text/jungmovideo.csv', 'r');
                $prevtitle = '';
                $flag = false;

                while(($data = fgetcsv($fp)) !== false){
                    $title = $data[0];
                    $thumbnail = $data[1];
                    $id = $data[2];
                    $videotitle = $data[3];
                    
                    if($title != $prevtitle){
                        if($flag){
                            echo '</div>'; // thumbnail-list
                            echo '</div>'; // thumbnail-list-wrapper
                            echo '</div>'; // video-row
                        }

                        echo '<div class="video-row border-top">';
                        echo '<div class="video-top">
                                <span class="mini-title">
                                    '.$title.'
                                </span>
                                <button class="seemore-btn" onclick="toggleList(this)">더 보기</button>
                            </div>';
                        echo '<div class="mt-2 video-list-wrapper">
                                <div class="video-list">';

                        $prevtitle = $title;
                        $flag = true;
                    }

                    echo '<div class="video">';
                    echo '<div class="thumbnail-img" style="background-image:url(\''.$thumbnail.'\');">
                            <div class="play-overlap" onclick="activateVideo(\''.$id.'\', this)">
                                <i class="bi bi-play-fill"></i>
                            </div>
                        </div>';
                    echo '<span class="video-info">'.$videotitle.'</span>';
                    echo '</div>';
                }

                echo '</div>'; // thumbnail-list
                echo '</div>'; // thumbnail-list-wrapper
                echo '</div>'; // video-row
            ?>
        </div>
    </main>
    <footer>
        <?php include '../parts/footer.php'?>
    </footer>
    <input id="np" style="display: none;">
    <?php script("../assets/js/jungmo/intro.js"); ?>
</body>