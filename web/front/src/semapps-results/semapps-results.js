Polymer({
  is: 'semapps-results',
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
    semapps.results = this;
    this.domSearchResults = semapps.domId('searchResults');
    this.domLoadingSpinner = semapps.domId('searchLoadingSpinner');
    this.$searchThemeFilter = $('#searchThemeFilter');
    // Wait global settings.
    SemAppsCarto.ready(() => {
      let tabs = [];
      let typeSel = '';
      $.each(semapps.entities, (type, data) => {
        data.counter = 0;
        typeSel = (typeSel == '') ? type : typeSel;
        tabs.push(data);
      });
      this.tabs = tabs;
      // Activate first tab by default.
      this.selectType(typeSel);
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
      this.selection(tab);
    // Render results.
    this.searchRender();
  },

  _routeChanged: function (data) {
    // We are on the search mode.
    if (data.prefix === '/rechercher') {
      // Route change may be fired before init.
      window.SemAppsCarto.ready(() => {
        let split = data.path.split('/');
        this.search(split[2], split[1]);
      });
    }
  },

  search(term, building) {
    "use strict";
    let filterUri = this.$searchThemeFilter.val();

    // Term and has not changed.
    if (semapps.searchLastTerm === term &&
        // Filter has not changed.
      semapps.searchLastFilter === filterUri) {
      // (maybe building changed).
      this.searchRender();
      return;
    }
    // Cleanup term to avoid search errors.
    semapps.searchLastTerm =
      term = (term || '').replace(/[`~!@#$%^&*()_|+\-=?;:'",.<>\{\}\[\]\\\/]/gi, '');
    // Save filter.
    semapps.searchLastFilter = filterUri;
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
    semapps.ajax('webservice/search?' +
      't=' + encodeURIComponent(term) +
      '&f=' + encodeURIComponent(semapps.searchLastFilter), (data) => {
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
    let dataPins = [];
    this.set('results', []);
    let totalCounter = 0;
    let typesCounter = {};
    let buildingsCounter = {};
    let resultTemps = {};
    // Allow empty response.
    response = response || this.renderSearchResultResponse || {};
    // Save last data for potential reload.
    this.renderSearchResultResponse = response;

    if (response.error) {
      this.searchError = true;
    }
    else if (response.results) {
      semapps.map.pinHideAll();

      for (let result of response.results) {
        // Data is allowed.

          if($.inArray(result.type,semapps.allowedType) !== -1){
            // // Count results by building.
            // if (semapps.buildings[result.building]) {
            //   buildingsCounter[result.building] = buildingsCounter[result.building] || 0;
            //   buildingsCounter[result.building]++;
            // }
            // // This building is enabled.
              // Count results.
            typesCounter[result.type] = typesCounter[result.type] || 0;
            typesCounter[result.type]++;
            totalCounter++;

              if (typeof resultTemps[result.type] === 'undefined')
                  resultTemps[result.type] = [];
              resultTemps[result.type].push(result);

            if(result["address"]){
              if(dataPins[result.address] === undefined && semapps.map.pins[result.address] === undefined){
                $.ajax({
                    url : 'http://api-adresse.data.gouv.fr/search/', // on appelle le script JSON
                    data: 'q=' + result["address"],
                    success : function(donnee){
                      semapps.map.addPin(donnee.features[0].geometry.coordinates[1],donnee.features[0].geometry.coordinates[0], result["address"],result["title"],result["type"]);
                      },
                });
              }
            }
          }
      }
      semapps.map.pinShowAll();
      if(typeof resultTemps[this.typeSelected] === 'undefined' ){
          // Deselect tab if current.
          let key = Object.keys(resultTemps)[0];
          this.selection(key);
          results =(typeof resultTemps[this.typeSelected] !== 'undefined' )? resultTemps[Object.keys(resultTemps)[0]] : [];
      }
      else{
          results = resultTemps[this.typeSelected];
      }
      // Create title.
      let resultsTitle = '';
      // Results number.
      resultsTitle += (results.length) ? results.length + ' résultats dans ' : 'Aucun résultat dans ';
      // Building.
      resultsTitle += (semapps.buildingSelected === semapps.buildingSelectedAll) ? 'tous les bâtiments' : 'le bâtiment ' + semapps.buildings[semapps.buildingSelected].title;
      // Display title.
      this.resultsTitle = resultsTitle;

      // Display no results section or not.
      this.noResult = results.length === 0;


    }

    this.tabsRegistry.all && (this.tabsRegistry.all.counter = totalCounter);
      /*
      semapps.allowedType.forEach(function(type) {
          this.tabsRegistry[type] && (this.tabsRegistry[type].counter = typesCounter[type] || 0);
      });*/
      for (var i = 0; i <  semapps.allowedType.length; i++) {
          this.tabsRegistry[semapps.allowedType[i]] && (this.tabsRegistry[semapps.allowedType[i]].counter = typesCounter[semapps.allowedType[i]] || 0);
      }

    setTimeout(() => {
      this.set('results', results);
    }, 100);
  },

    selection(val){
        if (this.typeSelected && this.tabsRegistry[val]) {
            this.tabsRegistry[this.typeSelected].$$('li').classList.remove('active');
        }
        // Save.
        this.typeSelected = val;
        log(this.typeSelected);
        // It may not be already created.
        if (this.tabsRegistry[val]) {
            this.tabsRegistry[val].$$('li').classList.add('active');
        }
    }
});
