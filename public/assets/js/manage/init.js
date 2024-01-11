$('#init-confirm-btn').click(function(){
    const password = $('#password').val();
    $.ajax({
        url: '../api/managequery.php',
        type: 'post',
        data: {
            why: 'init',
            password: password,
        },
        dataType: 'json',
    }).done(function(res){  
        if(res.success){
            alert(res.message);
            window.location.reload();
        } else {
            alert_float(res.message)
        }
    });
});