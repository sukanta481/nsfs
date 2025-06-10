	$('.carousel').carousel({
	  interval: 5000,
   	  pause: "false"
	});
	$(document).ready(function(){
$('.navbar-toggler').click(function(){
$('.navbar-toggler').toggleClass('showtoggle');
});	
});	
$(window).scroll(function() {    
    var scroll = $(window).scrollTop();

    if (scroll >= 30) {
        $(".header_sec").addClass("fixed");
    } else {
        $(".header_sec").removeClass("fixed");
    }
});





$(document).ready(function() {
var owl = $('.tmRgt .owl-carousel');
  owl.owlCarousel({
    margin:24,
    dots: false,
    autoplay: true,
    nav: false,
    dotsEach: false,
    loop: true,
    stagePadding: 50,
    responsive: {
      0: {	
      	stagePadding: 10,
      	margin:10,
        items: 1
      },
      481: {
      	stagePadding: 15,	
      	margin:10,	
        items: 1
      },
      576: {	
      	stagePadding: 15,
      	margin:10,	
        items: 1
      },
      768: {
      	stagePadding: 20,
      	margin:10,
        items: 1
      },         
      992: {
      	stagePadding: 20,
      	margin:20,
        items: 2
      },
      1200: {
        items: 2
      }
    }
  });
});




$(document).ready(function() {
var owl = $('.testiCaro .owl-carousel');
  owl.owlCarousel({
    margin:24,
    dots: false,
    autoplay: true,
    nav: false,
    dotsEach: false,
    loop: true,
    stagePadding: 50,
    responsive: {
      0: {	
      	stagePadding: 10,
      	margin:10,
        items: 1
      },
      481: {
      	stagePadding: 15,	
      	margin:10,	
        items: 1
      },
      576: {	
      	stagePadding: 15,
      	margin:10,	
        items: 2
      },
      768: {
      	stagePadding: 20,
      	margin:10,
        items: 2
      },         
      992: {
      	stagePadding: 20,
      	margin:20,
        items: 2
      },
      1200: {
        items: 2
      },
      1400: {
        items: 3
      }
    }
  });
});




$(document).ready(function() {
var owl = $('.whyRea .owl-carousel');
  owl.owlCarousel({
    margin:24,
    dots: false,
    autoplay: false,
    nav: true,
    dotsEach: false,
    loop: false,
    responsive: {
      0: {	
      	margin:10,
        items: 1
      },
      481: {	
      	margin:10,	
        items: 2
      },
      576: {	
      	margin:10,	
        items: 2
      },
      768: {
      	margin:10,
        items: 3
      },         
      992: {
      	margin:20,
        items: 4
      },
      1200: {
        items: 4
      }
    }
  });
});


$(document).ready(function() {
var owl = $('.tmKey .owl-carousel');
  owl.owlCarousel({
    margin:24,
    dots: false,
    autoplay: true,
    nav: true,
    dotsEach: false,
    loop: true,
    responsive: {
      0: {	
      	margin:10,
        items: 1
      },
      481: {	
      	margin:10,	
        items: 2
      },
      576: {	
      	margin:10,	
        items: 3
      },
      768: {
      	margin:10,
        items: 3
      },         
      992: {
      	margin:20,
        items: 4
      },
      1200: {
        items: 4
      }
    }
  });
});





$(document).ready(function() {
var owl = $('.whtSec .owl-carousel');
  owl.owlCarousel({
    margin:24,
    dots: false,
    autoplay: false,
    nav: true,
    dotsEach: false,
    loop: false,
    responsive: {
      0: {	
      	margin:10,
        items: 1
      },
      481: {	
      	margin:10,	
        items: 2
      },
      576: {	
      	margin:10,	
        items: 2
      },
      768: {
      	margin:10,
        items: 3
      },         
      992: {
      	margin:20,
        items: 4
      },
      1200: {
        items: 4
      }
    }
  });
});




$(document).ready(function() {    
$(".gallTab a").click(function(event) {        
	event.preventDefault();        
	$(this).parent().addClass("actv");        
	$(this).parent().siblings().removeClass("actv");        
	var tab = $(this).attr("href");        
	$(".tabDes").not(tab).css("display", "none");    
	$(tab).fadeIn();    
	});
});





