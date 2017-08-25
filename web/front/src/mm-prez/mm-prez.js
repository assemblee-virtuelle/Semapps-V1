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
    handleClickPrez(e) {
        e.preventDefault();
        log(e.target.rel);
        gvc.scrollToContent();
        gvc.myRoute = e.target.rel;
        gvc.goToPath(e.target.rel,{});

    },
});
