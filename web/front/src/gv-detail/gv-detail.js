Polymer({
  is: 'gv-detail',
  properties: {
    id: String,
    title: String,
    description: String,
    route: {
      type: Object,
      observer: '_routeChanged'
    }
  },

  _routeChanged: function (data) {
    if (data.prefix === '/detail' &&
      data.__queryParams &&
      data.__queryParams.uri) {
      // Wait main object to be ready.
      GVCarto.ready(() => {
        this.detailLoad(data.__queryParams.uri);
      });
    }
  },

  detailLoad: function (encodedUri) {
    "use strict";
    // Show spin.
    gvc.loadingPageContentStart();
    // Hide content.
    this.$.detail.style.display = 'none';
    // Request server.
    $.ajax({
      url: '/webservice/detail?uri=' + encodedUri,
      dataType: 'json',
      complete: (data) => {
        "use strict";
        // Check that we are on the last callback expected.
        this.detailLoadComplete(data)
      }
    });
  },

  detailLoadComplete: function (data) {
    "use strict";
    // Show detail content.
    this.$.detail.style.display = '';
    // Hide spin.
    gvc.loadingPageContentStop();
    log(data.responseJSON.detail);
    let fields = gvc.sfClient.sortFormFields(data.responseJSON.detail);
    log(gvc.sfClient.getFirstFieldValue('http://xmlns.com/foaf/0.1/givenName', fields));
  }
});
