

var swiper3 = new Swiper(".history-years-main-swiper", {
  freeMode: true,
  watchSlidesProgress: true,
  rewind: true,
  spaceBetween:0,
  slidesPerView:6,
  breakpoints: {
    990: {
      slidesPerView:6,
    },
    900: {
      slidesPerView:5,
    },
    100: {
      slidesPerView:4,
    }
  }
});
var swiper2 = new Swiper(".history-years-main-swiper2", {
  loop: true,
  autoHeight: true,
  rewind: true,
  thumbs: {
    swiper: swiper3,
  },
  navigation: {
    nextEl: ".histo-next",
    prevEl: ".histo-prev",
  },
});

//  $('.history-years-main').slick({
//     slidesToShow:6,
//     slidesToScroll: 1,
//     arrows: false,
//     asNavFor: '.history-about-years-slider',
//     infinite: true,
//     variableWidth: true,
//     adaptiveHeight: true,
//     responsive: [
//       {
//         breakpoint: 991,
//         settings: {
//           centerMode: true,
//         }
//       },{
//         breakpoint: 720,
//         settings: {
//           centerMode: false,
//         }
//       },
//     ]

// });
// $('.history-about-years-slider').slick({
//     slidesToShow: 1,
//     slidesToScroll: 1,
//     asNavFor: '.history-years-main',
//     fade:true,
//     arrows:true,
//     infinite: true,
//     adaptiveHeight: true,

// });


$('.history-video-play').click(function(){
  $('.history-videos').toggleClass('active');
})

$('.history-video-poster').click(function(){
  $(this).toggleClass('active');
})

$('.history-photo-slid').slick({
    slidesToShow:1,
    slidesToScroll: 1,
    arrows: false,
    asNavFor: '.history-medium-photo-slid',
    infinite: true,
    fade:true,

});
$('.history-medium-photo-slid').slick({
    slidesToShow:5,
    slidesToScroll: 1,
    asNavFor: '.history-photo-slid',
    arrows:false,

});


$( document ).ready(function() {
  Fancybox.bind('[data-fancybox="year-1857"]', {});
  Fancybox.bind('[data-fancybox="year-1939"]', {});
  Fancybox.bind('[data-fancybox="year-1945-1960"]', {});
  Fancybox.bind('[data-fancybox="year-small-1945-1960"]', {});
  Fancybox.bind('[data-fancybox="year-2005-2013"]', {});
  Fancybox.bind('[data-fancybox="year-small-2005-2013"]', {});
});


