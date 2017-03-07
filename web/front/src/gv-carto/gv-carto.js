Polymer({
  is: 'gv-carto',
  properties: {
    route: {
      type: Object,
      observer: '_routeChanged'
    }
  },

  _routeChanged(data) {
    "use strict";
    if (data.path === '/mon-compte') {
      window.location.replace(data.path);
    }
  },

  ready() {
    "use strict";
    new window.GVCarto(this);
  }
});
