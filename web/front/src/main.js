(function () {
  'use strict';

  // Devel
  window.log = (m) => {
    console.log(m);
  };

  var readyCallbacks = [];

  // A custom client for Semantic Forms specific treatments.
  class SFClient {
    sortFormFields(fields) {
      let sorted = {};
      for (let index in Object.keys(fields)) {
        let key = fields[index].property;
        let field = fields[index];
        if (field.cardinality === '0 Or More') {
          sorted[key] = sorted[key] || [];
          sorted[key].push(field);
        }
        else {
          sorted[key] = field;
        }
      }
      return sorted;
    }

    getFirstFieldValue(key, fields) {
      return typeof fields[key] === 'array' ? fields[key] : fields[key][0];
    }
  }

  window.GVCarto = class {

    constructor(mainComponent) {
      window.gvc = this;
      this.mainComponent = mainComponent;
      this.firstSearch = true;
      this.$window = $(window);
      this.buildingSelected = 'partout';
      this.$loadingSpinner = $('#gv-spinner');
      this.sfClient = new SFClient();
      this.$gvMap = $(document.getElementById('gv-map'));

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
      // Save key for furthe usage.
      for (let key in this.buildings) {
        this.buildings[key].key = key;
      }
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

    loadingPageContentStart() {
      this.$loadingSpinner.show();
    }

    loadingPageContentStop() {
      this.$loadingSpinner.hide();
    }

    /* -- Waiting --*/
    stateWaitingInit() {
      gvmap.mapShowBuildingPinAll();
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

    textRemove(term, char) {
      return term.replace(new RegExp(char, 'g'), '');
    }

    search(term, building) {
      this.stateSet('search');

      term = term.replace(/[`~!@#$%^&*()_|+\-=?;:'",.<>\{\}\[\]\\\/]/gi, '');

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
      this.loadingPageContentStart();

      // Build callback function.
      let complete = (data) => {
        this.loadingPageContentStop();
        this.renderSearchResult(data.responseJSON);
      };

      // Say that this function is the
      // only one we expect to be executed.
      // It prevent to parse multiple responses.
      this.searchQueryLastComplete = complete;

      this.loadingPageContentStart();

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
      // Allow empty response.
      response = response || this.renderSearchResultResponse || {};
      // Save last data for potential reload.
      this.renderSearchResultResponse = response;

      if (response.error) {
        $('#gv-results-error').show();
      }
      else if (!response.results || !response.results.length) {
        $('#gv-results-empty').show();
      }
      else {
        gvmap.mapShowBuildingPin(this.buildingSelected);
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
