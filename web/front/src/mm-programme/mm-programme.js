Polymer({
    is: 'mm-programme',
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
            this.programmeLoad();
        });

    },
    programmeLoad () {
        "use strict";
        log( gvc.myRoute)
        if( gvc.myRoute === "programme"){
            // Show spinner.
            this.loading = true;
            // Request server.
        }
    },

});
