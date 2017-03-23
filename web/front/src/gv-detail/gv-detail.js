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

  handleBack: function (e) {
    "use strict";
    e.preventDefault();
    history.back();
    gvc.scrollToSearchResults();
  },

  attached: function () {
    "use strict";
    this.domLoadingSpinner = gvc.domId('detailSpinner');
  },

  detailLoad: function (encodedUri) {
    "use strict";
    // Show spinner.
    this.domLoadingSpinner.style.display = 'block';
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
    this.domLoadingSpinner.style.display = 'none';
    data = data.responseJSON.detail || {};
    // Create inner depending of type.
    let inner = document.createElement('gv-detail-' + gvc.searchTypes[data.properties.type].type.toLowerCase());
    inner.data = data;
    let domInner = document.getElementById('gv-detail-inner');
    domInner.innerHTML = '';
    domInner.appendChild(inner);
  }
});
