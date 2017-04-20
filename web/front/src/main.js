(function () {
  'use strict';

  // Devel
  window.log = (m) => {
    console.debug(m);
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
          type: 'person',
          plural: 'Personnes'
        },
        "http://xmlns.com/foaf/0.1/Organization": {
          label: 'Organisation',
          type: 'organization',
          plural: 'Organisations'
        },
        "http://xmlns.com/foaf/0.1/Project": {
          label: 'Projet',
          type: 'projet',
          plural: 'Projets'
        },
        "http://purl.org/NET/c4dm/event.owl#Event": {
            label: 'Evénement',
            type: 'event',
            plural: 'Evénements'
        },
        "http://www.fipa.org/schemas#Proposition": {
            label: 'Proposition',
            type: 'proposition',
            plural: 'Propositions'
        },
      };

      // Play intro only once.
      if (cookie.get('introAnimation')) {
        window.document.body.classList.add('skip-intro');
      }
      else {
        cookie.set('introAnimation', true, {
          expires: 1 // Days
        });
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
      $.extend(this, parameters);
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
      this.goToPath('/rechercher/' + (this.buildingSelected || 'partout') + '/' + term);
    }

    scrollToContent(complete) {
      this.$window.scrollTo($('#pageContent').offset().top - 150, {
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

    imageOrFallback(path, typeUri) {
      "use strict";
      if (!path) {
        return '/common/images/result-no_picture-' + gvc.searchTypes[typeUri].type + '.png';
      }
      return path;
    }

    isSuperAdmin() {
      return this.access === 'super_admin';
    }

    isAdmin() {
      return (this.access === 'admin') || this.isSuperAdmin();
    }

    isMember() {
      return (this.access === 'super_admin') || this.isAdmin();
    }

    isAnonymous() {
      return !this.isMember();
    }

    /**
     * Set parameters from global object,
     * which are user into template as dynamic variables.
     */
    initElementGlobals(element) {
      $.extend(element, {
        isAnonymous: this.isAnonymous(),
        isMember: this.isMember(),
        isAdmin: this.isAdmin(),
        isSuperAdmin: this.isSuperAdmin()
      });
    }

    realLink(e) {
      e.preventDefault();
      // Force links to reload the hole page.
      window.location.replace(e.currentTarget.getAttribute('href'));
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
