<div class="subnavbar-container">
    <div class="subnavbar">
        <div class="subnavbar-menu">
            <a href="list">신청</a>
            <div></div>
        </div>
        <?php
            if(is_lesson_student()){
                echo '<div class="subnavbar-menu">
                        <a href="report_write">레슨일지</a>
                        <div></div>
                    </div>';
            }

            if(is_lesson_teacher() || exec_auth(EXEC_CONCERT)){
                echo '<div class="subnavbar-menu">
                    <a href="report_accept">출석 확인</a>
                    <div></div>
                </div>';
            }

            if(exec_auth(EXEC_CONCERT)){
                echo '<div class="subnavbar-menu">
                    <a href="modify">관리</a>
                    <div></div>
                </div>';
            }    
        ?>
    </div>
</div>
<div class="subnavbar-filler"></div>