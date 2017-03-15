(function () {
  'use strict';

  // Devel
  window.log = (m) => {
    console.log(m);
  };

  var readyCallbacks = [];

  // A custom client for Semantic Forms specific treatments.
  class SFClient {
    constructor(options) {
      $.extend(true, this, {
        formFields: {},
        fieldsAliases: {
          'http://www.w3.org/1999/02/22-rdf-syntax-ns#type': 'rdfType',
        }
      }, options);
    }

    sortFormFields(fields) {
      let sorted = {};
      for (let index in Object.keys(fields)) {
        let key = this.fieldsAliases[fields[index].property] || fields[index].property;
        let field = fields[index];
        // 0:1 or 1:1
        if (field.cardinality[2] === '1') {
          sorted[key] = field;

        }
        // 0:* or 1:*
        else {
          sorted[key] = sorted[key] || [];
          sorted[key].push(field);
        }
      }
      return sorted;
    }

    loadFormFields(fieldsData) {
      this.formFields = fieldsData;
      this.formFieldsSorted = this.sortFormFields(fieldsData);

      if (this.formFieldsSorted.rdfType) {
        for (let i in Object.keys(this.formFieldsSorted.rdfType)) {
          // Delete unwanted type.
          if (this.formFieldsSorted.rdfType[i].value === 'http://vocab.sindice.net/csv/Row') {
            delete this.formFieldsSorted.rdfType[i];
          }
        }
      }
    }

    getValue(key) {
      let fields = this.formFieldsSorted;
      key = this.fieldsAliases[key] || key;
      let data = typeof fields[key] === 'array' ? fields[key] : fields[key][0];
      return data.value;
    }
  }

  window.GVCarto = class {

    constructor(mainComponent) {
      window.gvc = this;
      this.mainComponent = mainComponent;
      this.firstSearch = true;
      this.$window = $(window);
      this.buildingSelectedAll = 'partout';
      this.buildingSelected = this.buildingSelectedAll;
      this.$loadingSpinner = $('#gv-spinner');
      this.sfClient = new SFClient({
        fieldsAliases: {
          'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#status': 'gvStatus',
          'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#realisedContribution': 'realisedContribution',
          'http://xmlns.com/foaf/0.1/status': 'foafStatus',
          'http://xmlns.com/foaf/0.1/name': 'foafName',
          'http://vocab.sindice.net/csv/rowPosition': 'rowPosition',
          'urn:gv/contactsPrint': 'contactsPrint',
          'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#conventionType': 'conventionType',
          'http://virtual-assembly.org/pair_v2#hasResponsible': 'hasResponsible',
          'urn:displayLabel': 'displayLabel',
          'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#administrativeName': 'administrativeName',
          'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#building': 'building',
          'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#proposedContribution': 'proposedContribution',
          'http://purl.org/dc/elements/1.1/subject': 'subject',
          'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#arrivalDate': 'arrivalDate',
          'http://assemblee-virtuelle.github.io/grands-voisins-v2/gv.owl.ttl#room': 'room'
        }
      });
      this.$gvMap = $(document.getElementById('gv-map'));
      this.$tabs = $('.nav-tabs');
      this.searchTypeCurrent = 'all';
      this.searchTypes = {
        // TODO use URI for type keys (change should be made in sf lookup results).
        Personne: 'Personne',
        Organisation: 'Organisation'
      };

      // Special class for dev env.
      if (window.location.hostname === '127.0.0.1') {
        window.document.body.classList.add('dev-env');
      }

      this.setSearchType(this.searchTypeCurrent);

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
      // Save key for further usage.
      for (let key in this.buildings) {
        this.buildings[key].key = key;
      }
      // Shortcuts.
      this.domSearchTextInput = this.domId('searchText');
      this.domSearchResults = this.domId('searchResults');
      this.stateSet('waiting');

      // Listeners.
      var timeout;
      var callbackSearchEvent = this.searchEvent.bind(this);
      // Click on submit button.
      this.listen('searchForm', 'submit', (e) => {
        this.domSearchTextInput.blur();
        this.scrollToSearchResults();
        callbackSearchEvent(e);
      });
      // Launch callbacks
      this.isReady = true;
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

    setSearchType(type) {
      if (type !== this.searchTypeCurrent) {
        this.$tabs
          .find('li[rel=' + this.searchTypeCurrent + ']')
          .removeClass('active');
      }

      this.searchTypeCurrent = type;

      this.$tabs
        .find('li[rel=' + type + ']')
        .addClass('active');

      // Reload render results.
      gvc.renderSearchResult();
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
        this.buildingSelected = gvc.buildingSelectedAll;
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
      // Hide counters.
      this.$tabs.find('li .counter').hide();

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
        // Empty if not already.
        this.domSearchResults.innerHTML = '';
        gvmap.mapShowBuildingPin(this.buildingSelected);
        let typesCounter = {};
        for (let i in response.results) {
          let data = response.results[i];
          // Count results event there are not displayed.
          typesCounter[data.type] = typesCounter[data.type] || 0;
          typesCounter[data.type]++;
          // Data is allowed.
          if (this.searchTypes[data.type] && (this.searchTypeCurrent === 'all' || data.type === this.searchTypeCurrent)) {
            let result = document.createElement('gv-results-item');
            // Apply all parameters (type / desc / etc... ).
            $.extend(result, data);
            this.domSearchResults.appendChild(result);
          }
        }

        // Set counters.
        this.$tabs.find('li .counter').html(0);
        let keys = Object.keys(typesCounter);
        let total = 0;
        for (let i in keys) {
          let type = keys[i]
          let value = typesCounter[type] || 0;
          this.$tabs.find('li[rel="' + type + '"] .counter').html(value);
          total += value;
        }
        this.$tabs.find('li[rel="all"] .counter').html(total);
        // Show counters.
        this.$tabs.find('li .counter').show();
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
