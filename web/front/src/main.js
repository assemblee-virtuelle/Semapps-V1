(function () {
  'use strict';

  // Devel
  window.log = (m) => {
    console.debug(m);
  };

  var readyCallbacks = [];


  window.GVCarto = class {

    constructor(mainComponent) {
      log(mainComponent);
      window.gvc = this;
      this.baseUrl = '/';
      this.myRoute = 'detail';
      this.mainComponent = mainComponent;
      this.firstSearch = true;
      this.$window = $(window);
      this.buildingSelectedAll = 'partout';
      this.buildingSelected = this.buildingSelectedAll;
      this.$gvMap = $(document.getElementById('mm-map'));
      this.searchTypes = {
        "http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#Person": {
          label: 'Personne',
          type: 'person',
          plural: 'Personnes'
        },
        "http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#Organization": {
          label: 'Organisation',
          type: 'organization',
          plural: 'Organisations'
        },
        "http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#Project": {
          label: 'Projet',
          type: 'projet',
          plural: 'Projets'
        },
        "http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#Event": {
          label: 'Evénement',
          type: 'event',
          plural: 'Evénements'
        },
        "http://assemblee-virtuelle.github.io/mmmfest/PAIR_temp.owl#Proposal": {
          label: 'Proposition',
          type: 'proposition',
          plural: 'Propositions'
        }
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
      let term = this.domSearchTextInput.value;
      let building = this.buildingSelected || 'partout';
      let path = '/rechercher/' + building + '/' + term;
      if (document.location.pathname === path) {
        // Reload search manually.
        this.results.search(term, building);
      }
      else {
        // Changing path will execute search action.
        this.goToPath(path);
      }
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
      this.myRoute = path;
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
    haveName(){
      return this.name !== '';
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
        isSuperAdmin: this.isSuperAdmin(),
        haveName: this.haveName()
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
