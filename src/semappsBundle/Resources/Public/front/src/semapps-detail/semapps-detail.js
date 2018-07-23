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
    let nameType = semapps.entities[this.child.type].nameType;
    switch (nameType) {
      case 'organization':
        path += 'mon-compte/organization/form/' + encodeURI(encodeURIComponent(this.idOfGraph));
        break;
      case 'person':
        path += 'mon-compte/person/form';
        break;
      default:
        path += 'mon-compte/component/'+nameType+'/form?uri='+this.currentComponentUri;

    }
      semapps.ajax('webservice/context/change/' + encodeURI(encodeURIComponent(this.idOfGraph)), (data) => {
          "use strict";
          // Check that we are on the last callback expected.
          //log(data);
          window.location.replace(path);
      });

  },

  attached () {
    "use strict";
    SemAppsCarto.ready(() => {
      semapps.initElementGlobals(this);
    });
  },

  detailLoad (encodedUri) {
    "use strict";
    // if (window.location.pathname.indexOf("detail") !== -1)
    //     semapps.myRoute = "detail";
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
    data.properties.image = semapps.imageOrFallback(data.properties.image, data.properties.key);
    if(data.properties.address ){
      let addressLabel = data.properties.address[0];
      if(semapps.map.pins[data.uri]){
        semapps.map.pinShowOne(data.uri);
      }
      else{
        semapps.getAddressToCreatePoint(addressLabel,data.title,data.properties.type[0],data.uri)
      }
    }
    let inner = document.createElement('semapps-detail-' + semapps.entities[data.properties.key].nameType.toLowerCase());
    this.child = inner;
    this.id = data.id;
    this.isSameUri = (data.uri === semapps.user.uri);
    let arrayOfGraph  = data.properties.graph.split(",")
    this.idOfGraph = null;
    this.isInGraph = false;
    if(semapps.user.graphuri){
      for (let i = 0, len = arrayOfGraph.length ; i < len && !this.isInGraph; i++) {
          if(semapps.user.graphuri.hasOwnProperty(arrayOfGraph[i])){
              this.isInGraph =true;
              this.idOfGraph = semapps.user.graphuri[arrayOfGraph[i]]['contextId']
          }
      }
    }

    //this.isInGraph = (data.properties.graph.indexOf(semapps.userGraphUri) !== -1);
    //log(this.isInGraph);
    this.canEdit = ((this.isSameUri || this.isInGraph) || (semapps.isSuperAdmin() && semapps.entities[data.properties.key].nameType.toLowerCase() === "organization"));
    inner.data = data;
    inner.parent = this;
    semapps.detail = this;
    this.currentComponentUri = data.uri;
    //log(semapps.detail.canEdit);

      let domInner = document.getElementById('semapps-detail-inner');
    domInner.innerHTML = '';
    domInner.appendChild(inner);
    this.loading = false;
  }
});
