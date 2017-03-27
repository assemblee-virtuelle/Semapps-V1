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
      this.baseUrl = '/';
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
        "http://xmlns.com/foaf/0.1/Person": {
          label: 'Personne',
          type: 'person'
        },
        "http://xmlns.com/foaf/0.1/Organization": {
          label: 'Organisation',
          type: 'organization'
        }
      };

      // Special class for dev env.
      if (window.location.hostname === '127.0.0.1') {
        window.document.body.classList.add('dev-env');
      }

      var loadParameters = () => {
        this.ajax('webservice/parameters', (response) => {
          if (response && response.responseJSON && response.responseJSON.no_internet) {
            // Enter in debug mode.
            this.baseUrl = '/fake_service/';
            // Reload fake parameters.
            loadParameters();
          }
          else {
            this.start(response.responseJSON);
          }
        });
      };
      // Load.
      loadParameters();
    }

    ajax(path, complete) {
      $.ajax({
        url: this.baseUrl + path,
        complete: complete
      });
    }

    ajaxMultiple(sources, callback) {
      "use strict";
      var ajaxCounter = 0;
      var allData = {};
      var self = this;
      for (var key in sources) {
        ajaxCounter++;
        this.ajax(sources[key], function (key) {
          return function (e) {
            ajaxCounter--;
            allData[key] = JSON.parse(e.responseText);
            // Final callback.
            if (ajaxCounter === 0) {
              callback.call(self, allData);
            }
          }
        }(key));
      }
    }

    start(parameters) {
      "use strict";
      this.buildings = parameters.buildings;
      this.entities = parameters.entities;
      // Save key for further usage.
      for (let key in this.buildings) {
        this.buildings[key].key = key;
      }
      // Shortcuts.
      this.domSearchTextInput = this.domId('searchText');

      // Launch callbacks
      this.isReady = true;

      let split = this.mainComponent.get('route.path').split('/');
      let isSearchPage = split.length >= 2 && (split[1] === 'rechercher' || split[1] === '');
      // We started on a search page.
      if (isSearchPage && split[3]) {
        this.domSearchTextInput.value = split[3];
      }

      // Ready callbacks.
      for (let i in readyCallbacks) {
        readyCallbacks[i]();
      }

      // We started on the arrival page or on the search page.
      isSearchPage && this.goSearch();
    }

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

    goToPath(path, params) {
      // Set first params.
      gvc.mainComponent.set('queryParams', params);
      // Changing route fires an event.
      gvc.mainComponent.set('route.path', path);
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
