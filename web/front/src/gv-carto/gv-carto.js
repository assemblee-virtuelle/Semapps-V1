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
    this.domSearchResults = this.domId('searchResults');
    this.stateSet('waiting');

    // Listeners.
    var callbackSearchEvent = this.searchEvent.bind(this);
    // Click on submit button.
    this.listen('searchForm', 'submit', (e) => {
      this.domSearchTextInput.blur();
      this.scrollToSearch();
      callbackSearchEvent(e);
    });
    var timeout;
    // Type in search field.
    this.listen('searchText', 'keyup', () => {
      if (timeout) {
        window.clearTimeout(timeout);
      }
      // Avoid to make too much requests when typing.
      timeout = window.setTimeout(callbackSearchEvent, 500);
    });
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
    this.$window.scrollTo($('#searchTabs').offset().top - 150, {
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

    // Empty content.
    this.domSearchResults.innerHTML = '';
    $('#gv-results-error').hide();

    if (!term) {
      return;
    }

    var $loadingSpinner = $('#gv-spinner');
    $loadingSpinner.show();

    $.ajax({
      url: 'webservice/search?t=' + encodeURIComponent(term),
      dataType: 'json',
      complete: (data) => {
        $loadingSpinner.hide();
        this.renderSearchResult(data.responseJSON);
      }
    });
  },

  renderSearchResult(response) {
    "use strict";
    if (response.error) {
      $('#gv-results-error').show();
    }
    else {
      for (let i in response.results) {
        let data = response.results[i];
        let result = document.createElement('gv-results-item');
        result.label = data.label;
        result.uri = data.uri;
        this.domSearchResults.appendChild(result);
      }
    }
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
