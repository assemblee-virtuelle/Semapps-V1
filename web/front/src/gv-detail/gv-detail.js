Polymer({
  is: 'gv-detail',
  properties: {
    id: String,
    title: String,
    description: String,
    queryParams: {
      observer: '_queryChanged'
    }
  },

  _queryChanged (data) {
    "use strict";
    if (data.uri) {
      // Wait main object to be ready.
      GVCarto.ready(() => {
        this.detailLoad(data.uri);
      });
    }
  },

  handleBack (e) {
    "use strict";
    e.preventDefault();
    gvc.goSearch();
  },

  attached () {
    "use strict";
    this.domLoadingSpinner = gvc.domId('detailSpinner');
  },

  detailLoad (encodedUri) {
    "use strict";
    // Show spinner.
    this.domLoadingSpinner.style.display = 'block';
    // Hide content.
    this.$.detail.style.display = 'none';
    // Request server.
    gvc.ajax('webservice/detail?uri=' + encodedUri, (data) => {
      "use strict";
      // Check that we are on the last callback expected.
      this.detailLoadComplete(data)
    });
  },

  detailLoadComplete (data) {
    "use strict";
    // Show detail content.
    this.$.detail.style.display = '';
    // Hide spin.
    this.domLoadingSpinner.style.display = 'none';
    data = data.responseJSON.detail || {};
    data.properties.image = gvc.imageOrFallback(data.properties.image, data.properties.type);
    if (data.properties.building) {
      // Display building on the map.
      gvc.map.pinShowOne(data.properties.building, 'ICI');
    }
    // Create inner depending of type.
    let inner = document.createElement('gv-detail-' + gvc.searchTypes[data.properties.type].type.toLowerCase());
    inner.data = data;
    let domInner = document.getElementById('gv-detail-inner');
    domInner.innerHTML = '';
    domInner.appendChild(inner);
  }
});
