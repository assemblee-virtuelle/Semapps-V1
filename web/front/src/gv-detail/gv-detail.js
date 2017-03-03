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
    // Don't know how to filter out this.
    /*if (data.prefix === '/organization') {
     window.GVCarto.ready(function () {
     this.refresh(data.path.slice(1));
     }.bind(this));
     }
     else {
     // Hide all maps on live route change.
     window.gvc && window.gvc.mapDeselectBuilding();
     }*/
  },

  attached: function () {
    "use strict";
    window.gvc.detailLoad(window.location.hash.slice(1));
  }
});
