Polymer({
  is: 'gv-carto',
  ready() {
    "use strict";

    // Devel
    window.log = (m) => {
      console.log(m);
    };

    window.gvc = this;

    // Special class for dev env.
    if (window.location.hostname === '127.0.0.1') {
      window.document.body.classList.add('dev-env');
    }

    this.ajaxMultiple({
      buildings: 'webservice/buildings'
    }, this.start);
  },

  ajaxMultiple(sources, callback) {
    "use strict";
    var ajaxCounter = 0;
    var allData = {};
    var self = this;
    for (var key in sources) {
      ajaxCounter++;
      $.ajax({
        url: sources[key],
        complete: function (key) {
          return function (e) {
            ajaxCounter--;
            allData[key] = JSON.parse(e.responseText);
            // Final callback.
            if (ajaxCounter === 0) {
              callback.call(self, allData);
            }
          }
        }(key)
      });
    }
  },

  start() {
    "use strict";

    log('ok');
  }
});
