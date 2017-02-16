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
    this.buildingSelected = 'all';

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
    this.stateSet('waiting');

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

  stateSet(stateName) {
    if (this.stateCurrent !== stateName) {
      let nameCapitalized = stateName.charAt(0).toUpperCase() + stateName.slice(1);
      if (this.stateCurrent) {
        let nameCurrentCapitalized = this.stateCurrent.charAt(0).toUpperCase() + this.stateCurrent.slice(1);
        this['state' + nameCurrentCapitalized + 'Exit']();
        this.stateCurrent = null;
      }
      // Callback should not return false if success.
      if (this['state' + nameCapitalized + 'Init']() !== false) {
        this.stateCurrent = stateName;
      }
    }
  },

  /* -- Waiting --*/
  stateWaitingInit() {

  },

  stateWaitingExit() {

  },

  /* -- Search -- */

  stateSearchInit() {
    if (!this.domSearchTextInput.value) {
      this.stateSet('waiting');
      // Block saving current state.
      return false;
    }
  },

  stateSearchExit() {

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
    this.search(term, {
      building: this.buildingSelected
    });
  },

  search(term, options = {}) {
    this.stateSet('search');
    $.ajax({
      url: 'webservice/search?t=' + encodeURIComponent(term),
      dataType: 'json',
      complete: function (response) {
        log(response.responseJSON);
      }
    });
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
