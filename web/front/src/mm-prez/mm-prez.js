Polymer({
  is: 'mm-prez',


  attached() {
    "use strict";
    GVCarto.ready(this.start.bind(this));
  },

  start() {
    "use strict";
      $('#mm-prez').css({ 'height': $(window).height() });
      $(window).on('resize', function() {
          $('#mm-prez').css({ 'height': $(window).height() });
          $('body').css({ 'width': $(window).width() })
      });
      $('.smoothscroll').on('click',function (e) {
          e.preventDefault();

          var target = this.hash,
              $target = $(target);

          $('html, body').stop().animate({
              'scrollTop': $target.offset().top
          }, 800, 'swing', function () {
              window.location.hash = target;
          });
      });
  },

});
/*$(document).scroll(function (event) {
    var alto_total = $("#mm-map").offset();
    var loader_business = $("#mm-header").offset();
    var mm_prez = $("#mm-prez");
    console.log(alto_total.top + " <= " + loader_business.top);
    if (alto_total.top <= loader_business.top && mm_prez.is(":visible") ){
        mm_prez.hide();
    }
});*/