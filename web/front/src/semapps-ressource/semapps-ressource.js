Polymer({
  is: 'semapps-ressource',
  properties: {
      person: String,
      queryParams: {
          observer: '_queryChanged'
      }
  },
    _queryChanged: function (data) {
        // We are on the search mode.
        "use strict";
        if (data && data.uri && data.person) {
            this.person = data.person;
            // Wait main object to be ready.
            SemAppsCarto.ready(() => {
                this.ressourceLoad(data.uri,data.person);
            });
        }
    },
  attached() {
    "use strict";
    SemAppsCarto.ready(() => {
          semapps.initElementGlobals(this);
      });
  },
    handleBack (e) {
        "use strict";
        e.preventDefault();
        semapps.goToPath('detail', {
            uri: this.person,
        });
    },
   ressourceLoad (encodedUri,encodedUriPerson) {
        "use strict";
       if( semapps.myRoute === "ressource"){
           // Show spinner.
           this.loading = true;
           // Hide content.
           this.$.ressource.style.display = 'none';
            // Request server.
            semapps.ajax('webservice/ressource?uri=' + encodedUri+'&person='+encodedUriPerson, (data) => {
                "use strict";
                // Check that we are on the last callback expected.
                this.ressourceLoadComplete(data)
            });
        }
    },
   ressourceLoadComplete (data) {
       "use strict";
        // Show detail content.
        this.$.ressource.style.display = '';
        data = data.responseJSON.ressource || {};
        log(data.ressourcesNeeded);
        log(this.ressourcesNeeded);
        this.ressourcesNeeded = data.ressourcesNeeded;
        this.ressourcesProposed = data.ressourcesProposed;
        this.title = data.name;
        this.loading = false;
    }

});
