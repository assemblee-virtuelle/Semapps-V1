Polymer({
  is: 'semapps-detail',
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
      SemAppsCarto.ready(() => {
        this.detailLoad(data.uri);
      });
    }
  },

  handleBack (e) {
    "use strict";
    e.preventDefault();
    semapps.goSearch();
  },

  handleEdit(e) {
    "use strict";
    e.preventDefault();
    let path = '/';
    switch (semapps.entities[this.child.type].nameType) {
      case 'organization':
        path += 'mon-compte/organisation/form/' + this.id;
        break;
    }
    window.location.replace(path);
  },

  attached () {
    "use strict";
    SemAppsCarto.ready(() => {
      semapps.initElementGlobals(this);
    });
  },

  detailLoad (encodedUri) {
    "use strict";
    if (window.location.pathname.indexOf("detail") !== -1)
        semapps.myRoute = "detail";

    if( semapps.myRoute === "detail") {
      // Show spinner.
      this.loading = true;
      // Hide content.
      this.$.detail.style.display = 'none';
      // Request server.
      semapps.ajax('webservice/detail?uri=' + encodedUri, (data) => {
          "use strict";
          // Check that we are on the last callback expected.
          this.detailLoadComplete(data)
      });
    }
  },

  detailLoadComplete (data) {
    "use strict";
    // Show detail content.
    semapps.scrollToContent();
    semapps.map.pinHideAll();
    data = data.responseJSON.detail || {};
    this.$.detail.style.display = '';
    data.properties.image = semapps.imageOrFallback(data.properties.image, data.properties.type);
    if(data.properties.address ){
      let addressLabel = data.properties.address[0];
      if(semapps.map.pins[data.uri]){
        semapps.map.pinShowOne(data.uri);
      }
      else{
        semapps.getAddressToCreatePoint(addressLabel,data.title,data.properties.type[0],data.uri)
      }
    }

    let inner = document.createElement('semapps-detail-' + semapps.entities[data.properties.type].nameType.toLowerCase());
    this.child = inner;
    this.id = data.id;
    inner.data = data;
    inner.parent = this;
    let domInner = document.getElementById('semapps-detail-inner');
    domInner.innerHTML = '';
    domInner.appendChild(inner);
    this.loading = false;
  }
});
