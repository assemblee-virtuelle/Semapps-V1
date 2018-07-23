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
            // semapps.map.zoomGlobal();
            // Route change may be fired before init.
            window.SemAppsCarto.ready(() => {
                let split = data.path.split('/');
                this.search(split[2],split[1]);
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
            'term=' + encodeURIComponent(term) +
            '&type=' + encodeURIComponent(this.typeSelected) +
            '&filter=' + encodeURIComponent(semapps.searchLastFilter), (data) => {
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
        let resultTemps = {};
        let buildingsCounter = {};
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
                if(semapps.buildingSelected === semapps.buildingSelectedAll || result.building === semapps.buildingSelected ){
                    // log(result.type);
                    typesCounter[this.typeSelected] = typesCounter[this.typeSelected] || 0;
                    typesCounter[this.typeSelected]++;
                    totalCounter++;
                    if (semapps.buildings[result.building]) {
                        buildingsCounter[result.building] = buildingsCounter[result.building] || 0;
                        buildingsCounter[result.building]++;
                    }
                    if (typeof resultTemps[this.typeSelected] === 'undefined')
                        resultTemps[this.typeSelected] = [];
                    resultTemps[this.typeSelected].push(result);
                    // log(resultTemps);
                    if(result["address"]){
                        if( semapps.map.pins[result["uri"]] === undefined){
                            semapps.getAddressToCreatePoint(result["address"],result["title"],result["type"],result["uri"]);
                        }
                        else{
                            semapps.map.pinShow(result["uri"]);
                        }
                    }
                }
            }
            //log(resultTemps[this.typeSelected]);
            results = (typeof resultTemps[this.typeSelected] !== 'undefined' )? resultTemps[this.typeSelected] : [];//resultTemps[this.typeSelected];

            // Create title.
            let resultsTitle = '';
            // Results number.
            resultsTitle += (results.length) ? results.length + ' résultats ' : 'Aucun résultat  ';
            // Building.
            // Display title.
            this.resultsTitle = resultsTitle;
            // Display no results section or not.
            this.noResult = results.length === 0;
            let domInner = document.getElementById('searchResults');
            domInner.innerHTML = '';
            // domInner.innerHTML = '';
            for(let result of results){
                let inner = document.createElement('semapps-results-'+semapps.entities[this.typeSelected].nameType.toLowerCase());
                inner.data = result;
                inner.parent = this;
                domInner.appendChild(inner);
            }
            // Show pins with results only.
            if(typeof semapps.schema !== 'undefined'){
                semapps.schema.pinHideAll();
                $.each(semapps.buildings, (building) => {
                    if (buildingsCounter[building] || building === semapps.buildingSelected) {
                        semapps.schema.pinShow(building, buildingsCounter[building] || 0);
                    }
                });
            }

        }

        this.tabsRegistry.all && (this.tabsRegistry.all.counter = totalCounter);
        for (let entity in semapps.entities){
            this.tabsRegistry[entity] && (this.tabsRegistry[entity].counter = typesCounter[entity] );
        }
        setTimeout(() => {
            this.set('results', results);
        }, 100);
    },

    selection(val){
        if (this.typeSelected && this.tabsRegistry[val]) {
            this.tabsRegistry[this.typeSelected].$$('li').classList.remove('active');
        }
        // It may not be already created.
        if (this.tabsRegistry[val]) {
            this.tabsRegistry[val].$$('li').classList.add('active');
        }
        if (val !== this.typeSelected ){
            // Save.
            this.typeSelected = val;
            this.search()
        }

    }
});
