let prevcnt = -1;
let notifytimer = null;
let lastloadtime = 0;

let accountdropdown = false;
let menudropdown = false;

loadNotification();
notifytimer = setInterval(loadNotification, 30000);

$(document).bind('visibilitychange', function(){
    if(document.visibilityState === 'visible'){
        loadNotification();
        notifytimer = setInterval(loadNotification, 30000);
    } else {
        clearTimeout(notifytimer);
    }
});

function loadNotification(){
    if(Date.now() - lastloadtime < 25000){
        return;
    }

    $.ajax({
        url: '../api/notification.php',
        type: 'post',
        data: {
            why: 'update',
            prevcnt: prevcnt,
        },
        dataType: 'json',
    }).done(function(res){
        if(res.success){
            lastloadtime = Date.now();
            if(prevcnt == res.cnt){
                return;
            }

            $('#notify-menu-cnt').html((res.cnt >= 9 ? '9+' : res.cnt));
            if(res.cnt > 0){
                $('#notify-menu-cnt').show();
            } else {
                $('#notify-menu-cnt').hide();
            }

        } else {
            alert_float(res.message);
        }
    });
}

$('#menu-dropdown-wrapper').click(function(e){
    e.stopPropagation();
    setMenuVisibility(!menudropdown);
    setAccountVisibility(false);

})

$('#account-dropdown-wrapper').click(function(e){
    e.stopPropagation();
    setAccountVisibility(!accountdropdown);
    setMenuVisibility(false);
})

$(document).click(function(){
    setMenuVisibility(false);
    setAccountVisibility(false);
})

function setAccountVisibility(visible){
    if(visible){
        $('#account-dropdown-wrapper').addClass('selected');
        $('#account-dropdown').show();
    } else {
        $('#account-dropdown-wrapper').removeClass('selected');
        $('#account-dropdown').hide();
    }

    accountdropdown = visible;
}

function setMenuVisibility(visible){
    if(visible){
        $('#menu-dropdown-wrapper').addClass('selected');
        $('#menu-dropdown').show();
    } else {
        $('#menu-dropdown-wrapper').removeClass('selected');
        $('#menu-dropdown').hide();
    }

    menudropdown = visible;
}

function makeSubNavFocus(n){
    const target = $('.subnavbar-menu').eq(n);
    target.removeClass('sidebar-btn-normal').addClass('selected');
}

function changeTab(n, dom){
    $('.tab-container').hide();
    $('.tab-container').eq(n).show();

    $('.tab-btn').removeClass('selected');
    $(dom).addClass('selected');

    $('#tab-btn-border-bottom').css('transform', 'translateX(' + (7.75 * n) + 'rem)');
}

