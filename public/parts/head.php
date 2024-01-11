<title>PIAST</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=0.7">
<link rel="shortcut icon" href="../assets/icons/favicon.ico">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@100;300;400;500;700;900&display=swap" rel="stylesheet">
<?php
    css('../assets/css/main.css');
    css('../assets/css/navbar.css');
    css('../assets/css/subnavbar.css');
    css('../assets/css/tab.css');
?>
<script src="../assets/js/jquery-3.6.3.min.js"></script>
<script>
    let alert_timer = null;
    let fadeout_before = false;

    function calcVh(){
        let vh = window.innerHeight * 0.01;
        document.documentElement.style.setProperty('--vh', `${vh}px`);
    }

    function alert_float(msg, success = false){
        const wrapper = $('#alert-window-wrapper');
        wrapper.show();
        wrapper.removeClass('fail success').addClass(success ? 'success' : 'fail');

        if(!fadeout_before){
            wrapper.addClass('fade-out1');
            fadeout_before = true;
        } else {
            wrapper.toggleClass('fade-out1 fade-out2');
        }

        $('#alert-msg').html(msg);
        $('#alert-icon').html(success ? '<i class="bi bi-check-lg"></i>' : '<i class="bi bi-x-lg"></i>');
        
        if(alert_timer !== null) clearTimeout(alert_timer);
        alert_timer = setTimeout(function(){
            wrapper.hide();
        }, 3000);

        wrapper.one('click', function(){
            wrapper.hide();
        });
    }

    calcVh();
    window.addEventListener('resize', () => calcVh());
</script>
<div id="alert-window-wrapper" style="display: none">
    <div id="alert-window">
        <div id="alert-icon" id="alert-icon">
        </div>
        <div id="alert-msg">
        </div>
    </div>
</div>