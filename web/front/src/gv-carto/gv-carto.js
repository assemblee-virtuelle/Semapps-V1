Polymer({
  is: 'gv-carto',
  ready() {
    "use strict";

    // Devel
    window.log = (m) => {
      console.log(m);
    };

    window.gvc = this;

    this.$window = $(window);

    // Special class for dev env.
    if (window.location.hostname === '127.0.0.1') {
      window.document.body.classList.add('dev-env');
    }

    this.ajaxMultiple({
      buildings: 'webservice/building'
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

  start(data) {
    "use strict";
    this.buildings = data.buildings;
    // Shortcuts.
    this.domSearchTextInput = this.domId('searchText');
    // Listeners.
    var callbackSearchEvent = this.searchEvent.bind(this);
    // Click on submit button.
    this.listen('searchForm', 'submit', (e) => {
      this.scrollToSearch();
      callbackSearchEvent(e);
    });
    // Type in search field.
    this.listen('searchText', 'keyup', callbackSearchEvent);
  },

  scrollToSearch() {
    this.$window.scrollTo('#searchTabs', {
      duration: 1000,
      easing: 'easeOutQuad'
    });
  },
  searchEvent(e) {
    // Event may be missing.
    e && e.preventDefault();
    var term = this.domSearchTextInput.value;
    log(term);
  },

  listen(id, event, callback) {
    // Support list of events names.
    if (Array.isArray(event)) {
      for (let i in event) {
        this.listen(id, event[i], callback);
      }
      return;
    }
    return this.domId(id).addEventListener(event, callback);
  },

  domId(id) {
    return document.getElementById(id);
  },

  dom(selector) {
    return document.querySelectorAll(selector);
  }

});
