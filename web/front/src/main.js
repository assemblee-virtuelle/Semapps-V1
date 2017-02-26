(function () {
  'use strict';

  // Devel
  window.log = (m) => {
    console.log(m);
  };

  var readyCallbacks = [];

  window.GVCarto = class {

    constructor(mainComponent) {
      window.gvc = this;
      this.mainComponent = mainComponent;
      this.firstSearch = true;
      this.$window = $(window);
      this.buildingSelected = 'partout';

      // Special class for dev env.
      if (window.location.hostname === '127.0.0.1') {
        window.document.body.classList.add('dev-env');
      }

      this.ajaxMultiple({
        buildings: '/webservice/building'
      }, this.start);
    }

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
    }

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
        this.scrollToSearchResults();
        callbackSearchEvent(e);
      });
      // Launch callbacks
      this.isReady = true;
      var timeout;
      // Type in search field.
      this.listen('searchText', 'keyup', () => {
        if (timeout) {
          window.clearTimeout(timeout);
        }
        // Avoid to make too much requests when typing.
        timeout = window.setTimeout(callbackSearchEvent, 500);
      });

      // Ready callbacks.
      for (let i in readyCallbacks) {
        readyCallbacks[i]();
      }
    }

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
    }

    /* -- Waiting --*/
    stateWaitingInit() {

    }

    stateWaitingExit() {

    }

    /* -- Search -- */

    stateSearchInit() {
      if (!this.domSearchTextInput.value) {
        this.stateSet('waiting');
        // Block saving current state.
        return false;
      }
    }

    stateSearchExit() {

    }

    scrollToSearchResults(complete) {
      this.$window.scrollTo($('#searchTabs').offset().top - 150, {
        duration: 1000,
        easing: 'easeOutQuad',
        complete: complete
      });
    }

    searchEvent(e) {
      // Event may be missing.
      e && e.preventDefault();
      var term = this.domSearchTextInput.value;
      this.search(term, this.buildingSelected);
    }

    searchRouteChange(term, building) {
      if (this.firstSearch) {
        // Set value to input (used at first page load)
        this.domSearchTextInput.value = term;
        this.buildingSelected = 'building';
        this.firstSearch = false;
      }

      // Launch search.
      this.search(term, building);
    }

    search(term, building) {
      this.stateSet('search');

      // Prevent recursions due to route event changes,
      // and also prevent to search two times the same values.
      if (this.lastSearchTerm === term && this.lastSearchBuilding === building) {
        return;
      }

      this.lastSearchTerm = term;
      this.lastSearchBuilding = building;
      this.mainComponent.set('route.path', '/rechercher/' + (building || 'partout') + '/' + term);

      // Empty content.
      this.domId('searchResults');
      this.domSearchResults.innerHTML = '';

      // Hide all results.
      $('#gv-results-empty, #gv-results-error').hide();
      let $loadingSpinner = $('#gv-spinner');

      // Build callback function.
      let complete = (data) => {
        $loadingSpinner.hide();
        this.renderSearchResult(data.responseJSON);
      };

      // Say that this function is the
      // only one we expect to be executed.
      // It prevent to parse multiple responses.
      this.searchQueryLastComplete = complete;

      $loadingSpinner.show();

      $.ajax({
        url: '/webservice/search?b=' + encodeURIComponent(building) + '&t=' + encodeURIComponent(term),
        dataType: 'json',
        complete: (data) => {
          "use strict";
          // Check that we are on the last callback expected.
          complete === this.searchQueryLastComplete
            // Continue.
          && complete(data);
        }
      });
    }

    renderSearchResult(response) {
      "use strict";
      if (response.error) {
        $('#gv-results-error').show();
      }
      else if (!response.results.length) {
        $('#gv-results-empty').show();
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
    }

    listen(id, event, callback) {
      // Support list of events names.
      if (Array.isArray(event)) {
        for (let i in event) {
          this.listen(id, event[i], callback);
        }
        return;
      }
      return this.domId(id).addEventListener(event, callback);
    }

    domId(id) {
      return document.getElementById(id);
    }

    dom(selector) {
      return document.querySelectorAll(selector);
    }
  };

  window.GVCarto.ready = function (callback) {
    if (!window.gvc || !window.gvc.isReady) {
      readyCallbacks.push(callback);
    }
    else {
      callback();
    }
  };
}());
