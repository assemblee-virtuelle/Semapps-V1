Polymer({
  is: 'semapps-prez',


  attached() {
    "use strict";
      SemAppsCarto.ready(this.start.bind(this));

  },

  start() {
    "use strict";
      $('#semapps-prez').css({ 'height': $(window).height() });
      $(window).on('resize', function() {
          $('#semapps-prez').css({ 'height': $(window).height() });
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
      this.isAnonymous = semapps.isAnonymous();
  },
});
