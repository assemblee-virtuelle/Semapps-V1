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
    if (data && data.uri) {
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

  handleEdit(e) {
    "use strict";
    e.preventDefault();
    let path = '/';
    switch (gvc.searchTypes[this.child.type].type) {
      case 'organization':
        path += 'orga/detail/' + this.id;
        break;
    }
    window.location.replace(path);
  },

  attached () {
    "use strict";
    GVCarto.ready(() => {
      gvc.initElementGlobals(this);
    });
  },

  detailLoad (encodedUri) {
    "use strict";
    if( gvc.myRoute === "detail") {
      // Show spinner.
      this.loading = true;
      // Hide content.
      this.$.detail.style.display = 'none';
      // Request server.
      gvc.ajax('webservice/detail?uri=' + encodedUri, (data) => {
          "use strict";
          // Check that we are on the last callback expected.
          this.detailLoadComplete(data)
      });
    }
  },

  detailLoadComplete (data) {
    "use strict";
    // Show detail content.
    data = data.responseJSON.detail || {};
    this.$.detail.style.display = '';
    data.properties.image = gvc.imageOrFallback(data.properties.image, data.properties.type);
    if (data.properties.building) {
      // Display building on the map.
      gvc.map.pinShowOne(data.properties.building, 'ICI');
    }else if (data.building){
      gvc.map.pinShowOne(data.building, 'ICI');
    }

    // Create inner depending of type.
    let inner = document.createElement('gv-detail-' + gvc.searchTypes[data.properties.type].type.toLowerCase());
    this.child = inner;
    this.id = data.id;
    inner.data = data;
    inner.parent = this;
    let domInner = document.getElementById('gv-detail-inner');
    domInner.innerHTML = '';
    domInner.appendChild(inner);
    this.loading = false;
  }
});
