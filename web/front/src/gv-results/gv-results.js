Polymer({
  is: 'gv-results',
  properties: {
    tabFirst: {
      type: Object,
      value: {
        type: 'all',
        plural: 'Tous',
        icon: 'list'
      }
    },
    typeSelected: {
      type: String
    },
    tabs: {
      type: Array,
      value: []
    },
    tabsRegistry: {
      type: Object,
      value: {}
    },
    results: {
      type: Array,
      value: []
    },
    searchLastTerm: {
      type: String,
      value: null
    },
    route: {
      type: Object,
      observer: '_routeChanged'
    }
  },

  attached() {
    "use strict";
    gvc.results = this;
    this.domSearchResults = gvc.domId('searchResults');
    this.domLoadingSpinner = gvc.domId('searchLoadingSpinner');
    // Wait global settings.
    GVCarto.ready(() => {
      let tabs = [
        this.tabFirst
      ];
      $.each(gvc.entities, (type, data) => {
        data.counter = 0;
        tabs.push(data);
      });
      this.tabs = tabs;
      // Activate first tab by default.
      this.selectType('all');
    });
  },

  tabRegister(type, component) {
    "use strict";
    this.tabsRegistry[type] = component;
    // Refresh selected tab.
    this.selectType(this.typeSelected);
  },

  selectType(tab)  {
    "use strict";
    // Deselect tab if current.
    if (this.typeSelected && this.tabsRegistry[tab]) {
      this.tabsRegistry[this.typeSelected].$$('li').classList.remove('active');
    }
    // Save.
    this.typeSelected = tab;
    // It may not be already created.
    if (this.tabsRegistry[tab]) {
      this.tabsRegistry[tab].$$('li').classList.add('active');
    }
    // Render results.
    this.searchRender();
  },

  _routeChanged: function (data) {
    //// We are on the search mode.
    if (data.prefix === '/rechercher') {
      // Route change may be fired before init.
      window.GVCarto.ready(() => {
        let split = data.path.split('/');
        gvc.buildingSelected =
          gvc.searchLastBuilding = (gvc.buildings[split[1]] ? split[1] : gvc.buildingSelectedAll);
        // Term has not changed (maybe building changed).
        if (gvc.searchLastTerm === split[2]) {
          this.searchRender();
        }
        else {
          this.search(split[2], split[1]);
        }
      });
    }
  },

  search(term) {
    "use strict";
    // Cleanup term to avoid search errors.
    gvc.searchLastTerm =
      term = (term || '').replace(/[`~!@#$%^&*()_|+\-=?;:'",.<>\{\}\[\]\\\/]/gi, '');
    this.searchError =
      this.noResult = false;
    // Empty page.
    this.results = [];
    // Show spinner.
    this.domLoadingSpinner.style.display = 'block';
    // Build callback function.
    let complete = (data) => {
      this.domLoadingSpinner.style.display = 'none';
      this.searchRender(data.responseJSON);
    };
    // Say that this function is the
    // only one we expect to be executed.
    // It prevent to parse multiple responses.
    this.searchQueryLastComplete = complete;
    gvc.ajax('webservice/search?t=' + encodeURIComponent(term), (data) => {
      "use strict";
      // Check that we are on the last callback expected.
      complete === this.searchQueryLastComplete
        // Continue.
      && complete(data);
    });
  },

  searchRender(response) {
    "use strict";
    let results = [];
    // Reset again if just rendering fired.
    this.searchError =
      this.noResult = false;
    this.results.length = 0;
    this.set('results', []);
    let totalCounter = 0;
    let typesCounter = {};
    let buildingsCounter = {};

    // Allow empty response.
    response = response || this.renderSearchResultResponse || {};
    // Save last data for potential reload.
    this.renderSearchResultResponse = response;

    if (response.error) {
      this.searchError = true;
    }
    else if (response.results) {

      for (let result of response.results) {
        // Data is allowed.
        if (gvc.searchTypes[result.type]) {

          // Count results by building.
          if (gvc.buildings[result.building]) {
            buildingsCounter[result.building] = buildingsCounter[result.building] || 0;
            buildingsCounter[result.building]++;
          }
          // This building is enabled.
          if (gvc.buildingSelected === gvc.buildingSelectedAll || result.building === gvc.buildingSelected) {
            // Count results.
            typesCounter[result.type] = typesCounter[result.type] || 0;
            typesCounter[result.type]++;
            totalCounter++;
            // This tab is enabled.
            if (this.typeSelected === 'all' || result.type === this.typeSelected) {
              results.push(result);
            }
          }
        }
      }

      this.noResultContext = ' ... ';
      if (results.length === 0) {
        this.noResult = true;
        if (gvc.buildingSelected != gvc.buildingSelectedAll) {
          this.noResultContext = ' dans le bâtiment ' + gvc.buildings[gvc.buildingSelected].title + '.';
        }
      }

      // Show pins with results only.
      gvc.map.pinHideAll();
      $.each(gvc.buildings, (building) => {
        if (buildingsCounter[building] || building === gvc.buildingSelected) {
          gvc.map.pinShow(building, buildingsCounter[building] || 0);
        }
      });
    }

    this.tabsRegistry.all && (this.tabsRegistry.all.counter = totalCounter);
    for (let type of Object.keys(gvc.searchTypes)) {
      this.tabsRegistry[type] && (this.tabsRegistry[type].counter = typesCounter[type] || 0);
    }

    setTimeout(() => {
      this.set('results', results);
    }, 100);
  }
});
