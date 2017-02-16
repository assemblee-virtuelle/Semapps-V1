Polymer({
  is: 'gv-logo-animated',

  ready: function () {
    this.pathCounter = 0;
    this.styleSheet = document.createElement('style');
    document.head.appendChild(this.styleSheet);
    this.animatePaths('#TEXT path', 'displayPath 1s cubic-bezier(.36,.34,.29,1) forwards', 0.08);
    this.animatePaths('#SHADOW path', this.animatePathShadowItem.bind(this), 0.05);
  },

  animatePaths: function (selector, animation, delay) {
    var paths = this.querySelectorAll(selector);
    var animationDelay = 0;
    var animationIsFunction = typeof animation === 'function';
    for (var path of paths) {
      var length = path.getTotalLength();
      path.style.strokeDasharray = length;
      path.style.strokeDashoffset = length;
      if (animationIsFunction) {
        animation(path, length);
      }
      else {
        path.style.animation = animation;
      }
      path.style.animationDelay = animationDelay + 's';
      animationDelay += delay;
    }
  },

  animatePathShadowItem: function (path, length) {
    let sheet = this.styleSheet.sheet;
    sheet.insertRule('\
            @keyframes displayPathShadow' + this.pathCounter + ' {\
                0% {\
                  stroke-dashoffset: ' + length + ';\
                }\
                50% {\
                  stroke-dashoffset: 0;\
                }\
                100% {\
                  stroke-dashoffset: ' + -length + ';\
                }\
            }\
            ', (sheet.cssRules || sheet.rules).length);
    path.style.animation = 'displayPathShadow' + this.pathCounter + ' 1s cubic-bezier(.36,.34,.29,1) forwards';
    this.pathCounter++;
  }
});
