Polymer({
  is: 'semapps-map',

  properties: {
    route: {
      type: Object,
      observer: '_routeChanged'
    },
    pins: {
      type: Array,
      value: []
    },
    pinsRegistry: {
      type: Object,
      value: {}
    }
  },

  _routeChanged: function () {
    this.updateVisibility();
  },

  // Wait all HTML to be loaded.
  attached() {
    this.ready = false;
    // Global ref.
    semapps.map = this;

    // Wait for buildings to be loaded.
    SemAppsCarto.ready(this.start.bind(this));
  },

  start() {
    "use strict";
      this.OSM = L.map('semapps').setView([48.862725, 2.287592], 12);
      let OpenStreetMap_France = L.tileLayer('https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png', {
          attribution: '&copy; Openstreetmap France | &copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
      });
  },

});
