// document.documentElement.classList.toggle("dark");

/*  
    Source: https://www.w3schools.com/howto/howto_js_slideshow.asp
*/

var slideIndex = 1;
var slides = $('.slide');
var i;
let slideTime = 6000;

for (i = 0; i < slides.length; i++) {
  slides.eq(i).css('opacity', (i == 0) ? 1 : 0);
  slides.eq(i).css('zIndex', (i == 0) ? 1 : 0);
  slides.eq(i).show();
}

var timer = setInterval(plusSlides, slideTime, 1);

// Next/previous controls
function plusSlides(n) {
  showSlides(slideIndex += n);
}

// Thumbnail image controls
function currentSlide(n) {
  showSlides(slideIndex = n);
}

function showSlides(n) {
  let dots = $('.bi-circle-fill');
  if (n > slides.length) { slideIndex = 1 }
  if (n < 1) { slideIndex = slides.length }
  for (i = 0; i < slides.length; i++) {
    const target = slides.eq(i);
    if (i != slideIndex - 1) {
      target.css('opacity', 0);
      setTimeout(function () { target.css('zIndex', 0); });
    }
    else {
      target.css('opacity', 1);
      setTimeout(function () { target.css('zIndex', 1); });
    }

  }

  for (i = 0; i < dots.length; i++) {
    dots.eq(i).removeClass('dot-active');
  }

  dots.eq(slideIndex - 1).addClass('dot-active');
  clearInterval(timer);
  timer = setInterval(plusSlides, slideTime, 1);
}