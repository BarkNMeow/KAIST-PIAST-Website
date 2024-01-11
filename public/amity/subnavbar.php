<div class="subnavbar-container">
    <div class="subnavbar">
        <div class="subnavbar-menu">
            <a href="rank">순위</a>
            <div></div>
        </div>
        <div class="subnavbar-menu">
            <a href="activity">점수 내역</a>
            <div></div>
        </div>
        <div class="subnavbar-menu">
            <a href="mygroup">조 정보</a>
            <div></div>
        </div>
        <?php
            if(exec_auth(EXEC_VICE)){
                echo '<div class="subnavbar-menu">
                        <a href="modify">관리</a>
                        <div></div>
                    </div>';
            }
        ?>
    </div>
</div>
<div class="subnavbar-filler"></div>