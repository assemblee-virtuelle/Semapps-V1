Polymer({
    is: 'semapps-codeSocial',
    properties: {
        queryParams: {
            observer: '_queryChanged'
        }
    },
    _queryChanged: function (data) {
        // We are on the search mode.
        "use strict";
        // Wait main object to be ready.
        GVCarto.ready(() => {
            this.codeSocialLoad();
        });

    },
    codeSocialLoad () {
        "use strict";
        if( gvc.myRoute === "codeSocial"){
            // Show spinner.
            this.loading = true;
            // Request server.
        }
    },
    handleAccountClick(e) {
        "use strict";
        gvc.realLink(e);
    },
});
