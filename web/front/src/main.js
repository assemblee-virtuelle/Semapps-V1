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

    getValue(fields, key) {
      key = this.fieldsAliases[key] || key;
      if (fields[key]) {
        let data = typeof fields[key] === 'array' ? fields[key] : fields[key][0];
        return data.value;
      }
      return '';
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
      this.searchTypes = {
        "http://xmlns.com/foaf/0.1/Person": 'Personne',
        "http://xmlns.com/foaf/0.1/Organization": 'Organisation'
      };

      // Special class for dev env.
      if (window.location.hostname === '127.0.0.1') {
        window.document.body.classList.add('dev-env');
      }

      this.ajaxMultiple({
        parameters: '/webservice/parameters'
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
      this.buildings = data.parameters.buildings;
      this.entities = data.parameters.entities;
      // Save key for further usage.
      for (let key in this.buildings) {
        this.buildings[key].key = key;
      }
      // Shortcuts.
      this.domSearchTextInput = this.domId('searchText');
      this.stateSet('waiting');

      // TODO ? this.setSearchType(this.searchTypeCurrent);

      // Launch callbacks
      this.isReady = true;

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

    /*setSearchType(type) {


     // Reload render results.
     gvc.renderSearchResult();
     }*/

    /*loadingPageContentStart() {
     this.$loadingSpinner.show();
     }

     loadingPageContentStop() {
     this.$loadingSpinner.hide();
     }*/

    /* -- Waiting --*/
    stateWaitingInit() {
      //gvc.map.mapShowBuildingPinAll();
    }

    stateWaitingExit() {
      //gvc.map.mapHideBuildingPinAll();
    }

    /* -- Search -- */

    /*stateSearchInit() {
     if (!this.domSearchTextInput.value) {
     this.stateSet('waiting');
     // Block saving current state.
     return false;
     }
     }

     stateSearchExit() {

     }*/

    goSearch() {
      var term = this.domSearchTextInput.value;
      this.mainComponent.set('route.path', '/rechercher/' + (this.buildingSelected || 'partout') + '/' + term);
    }

    scrollToSearchResults(complete) {
      this.$window.scrollTo($('#searchTabs').offset().top - 150, {
        duration: 1000,
        easing: 'easeOutQuad',
        complete: complete
      });
    }

    /*
     searchRouteChange(term, building) {
     if (this.firstSearch) {
     // Set value to input (used at first page load)
     this.domSearchTextInput.value = term;
     this.firstSearch = false;
     }

     // There is no building with this name.
     if (!this.buildings[building]) {
     building = this.buildingSelectedAll;
     }

     // Launch search.
     this.search(term, building);
     }

     textRemove(term, char) {
     return term.replace(new RegExp(char, 'g'), '');
     }

     search(term, building) {
     this.stateSet('search');
     log(' SEARCH ');


     // Prevent recursions due to route event changes,
     // and also prevent to search two times the same values.
     if (this.lastSearchTerm === term && this.lastSearchBuilding === building) {
     return;
     }

     this.lastSearchTerm = term;
     this.lastSearchBuilding = building;





     // Hide counters.
     this.$tabs.find('li .counter').hide();

     // Hide all results.
     $('#gv-results-empty, #gv-results-error').hide();
     this.loadingPageContentStart();








     }

     renderSearchResult(response) {
     "use strict";


     gvc.map && gvc.map.mapHideBuildingPinAll();


     window.gvresults && window.gvresults.selectType(this.searchTypeCurrent);


     }*/

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
