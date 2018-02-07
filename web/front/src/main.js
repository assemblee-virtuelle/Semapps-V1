(function () {
    'use strict';

    // Devel
    window.log = (m) => {
        console.debug(m);
    };

    var readyCallbacks = [];


    window.SemAppsCarto = class {

        constructor(mainComponent) {
            log(mainComponent);
            window.semapps = this;
            this.baseUrl = '/';
            this.myRoute = window.location.pathname.replace(/\//g, '');
            this.mainComponent = mainComponent;
            this.$window = $(window);
            this.detailAddress= [];
            this.entities = {
                "http://virtual-assembly.org/pair#Person":{
                    name: 'Personne',
                    plural: 'Personnes',
                    icon: 'user',
                    markerColor: '#28ccfb',
                }
                ,
                "http://virtual-assembly.org/pair#Organization":{
                    name: 'Organisation',
                    plural: 'Organisations',
                    icon: 'tower',
                    markerColor: '#01acdd',
                }
                ,
                "http://virtual-assembly.org/pair#Project":{
                    name: 'Projet',
                    plural: 'Projets',
                    icon: 'screenshot',
                    markerColor: '#FF4351',
                }
                ,
                "http://virtual-assembly.org/pair#Event":{
                    name: 'Evènement',
                    plural: 'Evènements',
                    icon: 'calendar',
                    markerColor: '#FF667a',
                }
                ,
                "http://virtual-assembly.org/pair#Proposal":{
                    name: 'Proposition',
                    plural: 'Propositions',
                    icon: 'info-sign',
                    markerColor: '#7b72e9',
                }
                ,
                "http://virtual-assembly.org/pair#Document":{
                    name: 'Document',
                    plural: 'Documents',
                    icon: 'folder-open',
                    markerColor: '#a4de37',
                }
                ,
                "urn://semapps/thesaurus/documenttype":{
                    name: 'Type de document',
                    plural: 'Types de document',
                    icon: 'pushpin',
                    markerColor: '#a4de37',
                }
                ,
                "urn://semapps/thesaurus/projecttype":{
                    name: 'Type de projet',
                    plural: 'Types de projets',
                    icon: 'pushpin',
                    markerColor: '#FF4351',
                }
                ,
                "urn://semapps/thesaurus/eventtype":{
                    name: 'Type d\'événement',
                    plural: 'Types d\'événements',
                    icon: 'pushpin',
                    markerColor: '#FF667a' ,
                }
                ,
                "urn://semapps/thesaurus/proposaltype":{
                    name: 'Type de proposition',
                    plural: 'Types de propositions',
                    icon: 'pushpin',
                    markerColor: '#7b72e9',
                }
                ,
                "urn://semapps/thesaurus/organizationtype":{
                    name: 'Type d\'organisation',
                    plural: 'Types d\'organisations',
                    icon: 'pushpin',
                    markerColor: '#01acdd',
                }
                ,
                "http://assemblee-virtuelle.github.io/grands-voisins-v2/thesaurus.ttl":{
                    name: 'Type d\'organisation',
                    plural: 'Types d\'organisations',
                    icon: 'pushpin',
                    markerColor: '#838e91',
                }
                ,
            };

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
            $.each(this.entities, function(key, value) {
                if(semapps.typeToName.hasOwnProperty(key)){
                    semapps.entities[key].nameType = semapps.typeToName[key];
                    semapps.entities[key].type = key;
                }else{
                    semapps.entities[key].nameType = semapps.graphToName[key];
                    semapps.entities[key].type = 'http://www.w3.org/2004/02/skos/core#Concept';
                }
            });
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
            // let building = this.buildingSelected.split('/')[this.buildingSelected.split('/').length-1] || 'partout';
            let path = '/rechercher'; ///' + building ;//+ '/' + term;
            path += (term)? '/' + term : '';
            if (document.location.pathname === path) {
                // Reload search manually.
                this.results.search(term, '');
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
            semapps.mainComponent.set('queryParams', params);
            // Changing route fires an event.
            semapps.mainComponent.set('route.path', path);
        }

        imageOrFallback(path, key) {
            "use strict";
            if (!path) {
                if(semapps.entities.hasOwnProperty(key))
                    return '/common/images/result-no_picture-' + semapps.entities[key].nameType + '.png';
            }
            return path;
        }
        haveName(){
            return this.user.name !== '';
        }
        isSuperAdmin() {
            return this.user.access === 'super_admin';
        }

        isAdmin() {
            return (this.user.access === 'admin') || this.isSuperAdmin();
        }

        isMember() {
            return (this.user.access === 'member') || this.isAdmin();
        }

        isAnonymous() {
            return !this.isMember();
        }

        capitalize(string,lower) {
            return (lower ? string.toLowerCase() : string).replace(/(?:^|\s)\S/g, function(a) { return a.toUpperCase(); });
        };
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

        getAddressToCreatePoint(address,title,type,uri){
            $.ajax({
                url : 'http://api-adresse.data.gouv.fr/search/', // on appelle le script JSON
                data: 'q=' + address,
                success : function(donnee){
                    semapps.map.addPin(donnee.features[0].geometry.coordinates[1],donnee.features[0].geometry.coordinates[0], uri,title,type);
                    semapps.detailAddress[address] = donnee;
                },
            });
        }
    };

    window.SemAppsCarto.ready = function (callback) {
        if (!window.semapps || !window.semapps.isReady) {
            readyCallbacks.push(callback);
        }
        else {
            callback();
        }
    };
}());
