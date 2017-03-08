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
      this.detailLoad(data.__queryParams.uri);
    }
  },

  detailLoad: function (encodedUri) {
    "use strict";

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
    log(data);
  }
});
