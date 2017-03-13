Polymer({
  is: 'gv-results',
  tabsTypes: {
    'All': true,
    'Person': 'Personne',
    'Organization': 'Organisation'
  },
  properties: {
    route: {
      type: Object,
      observer: '_routeChanged'
    },
    isActiveAll: '',
    isActiveOrganization: '',
    isActivePerson: ''
  },

  attached() {
    "use strict";

    GVCarto.ready(() => {
      // First time we activate tab,
      // but we don't need to reload results.
      this.setSearchType('All', false);
    });
  },

  setSearchType(tab, reload) {
    "use strict";
    // True by default.
    reload = reload !== undefined ? reload : true;
    // Configure gvc.
    gvc.searchType = this.tabsTypes[tab];
    // Enable tab.
    if (this.activeCurrent) {
      this['isActive' + this.activeCurrent] = '';
    }
    this.activeCurrent = tab;
    this['isActive' + this.activeCurrent] = 'active';
    // Reload render results.
    reload && gvc.renderSearchResult();
  },

  _routeChanged: function (data) {
    let split = data.path.split('/');
    // We are on the search mode.
    if (data.prefix === '/rechercher') {
      // Route change may be fired before init.
      window.GVCarto.ready(() => {
        window.gvc.searchRouteChange(split[2], split[2]);
      });
    }
  }
});
