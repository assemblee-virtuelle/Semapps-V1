Polymer({
    is: 'semapps-infos',
    properties: {
        queryParams: {
            observer: '_queryChanged'
        }
    },
    _queryChanged: function (data) {
        // We are on the search mode.
        "use strict";
        log("programme")
        // Wait main object to be ready.
        GVCarto.ready(() => {
            this.infosLoad();
        });

    },
    infosLoad () {
        "use strict";
        log( gvc.myRoute)
        if( gvc.myRoute === "infos"){
            // Show spinner.
            this.loading = true;
            // Request server.
        }
    },

});
