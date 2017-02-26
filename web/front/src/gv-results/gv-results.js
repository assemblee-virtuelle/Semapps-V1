Polymer({
  is: 'gv-results',

  properties: {
    route: {
      type: Object,
      observer: '_routeChanged'
    }
  },

  _routeChanged: function (data) {
    let split = data.path.split('/');
    // We are on the search mode.
    if (data.prefix === '/rechercher') {
      // Route change may be fired before init.
      window.GVCarto.ready(() => {
        window.gvc.searchRouteChange(split[2], split[1]);
      });
    }
  }
});
