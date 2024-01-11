<div class="subnavbar-container">
    <div class="subnavbar">
        <div class="subnavbar-menu">
            <a href="apply">신청</a>
            <div></div>
        </div>
        <div class="subnavbar-menu">
            <a href="video">모아보기</a>
            <div></div>
        </div>
        <?php
            if(is_exec()){
                echo '<div class="subnavbar-menu">
                        <a href="check">출석 체크</a>
                        <div></div>
                    </div>';
            }
        ?>
    </div>
</div>
<div class="subnavbar-filler"></div>