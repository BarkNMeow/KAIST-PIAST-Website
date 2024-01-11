const ctx = document.getElementById('canvas').getContext('2d');

ctx.fillStyle = 'rgb(224, 224, 224)';
ctx.fillRect(0, 0, canvas.width, canvas.height);

// console.log(canvas.width, canvas.height);
drawTitle();

function drawTitle(){
    ctx.font = 'bold 50px Noto Sans KR';
    ctx.fillStyle = 'black';
    ctx.textAlign = 'center';
    ctx.fillText('음표 게임', canvas.width / 2, 150);

    ctx.fillStyle = 'rgb(224, 224, 224)';
    ctx.fillRect(0, 0, canvas.width, canvas.height);
}


