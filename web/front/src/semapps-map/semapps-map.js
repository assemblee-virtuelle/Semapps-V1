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
      this.OSM = L.map('semapps').setView([42.403681, 2.47986190000006], 13);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
      }).addTo(this.OSM );

  },

    pinShow(latitude,longitude, uri, text) {
        "use strict";
        let marker = L.marker([latitude,longitude])
            .bindPopup(text);
        marker.addTo(this.OSM);
        this.pins[uri] = marker;
    },

    pinShowOne(latitude,longitude, uri, text) {
        "use strict";
        this.pinHideAll();
        this.pinShow(latitude,longitude, uri, text);
    },

    pinHide(uri) {
        "use strict";
        if(this.pins[uri] !== undefined){
            let marker = this.pins[uri];
            this.OSM.removeLayer(marker);
        }
    },

    pinHideAll() {
        "use strict";
        for (let uri in this.pins){
            log(uri);
            if (this.pins.hasOwnProperty(uri)) {
                this.pinHide(uri);
            }
        }
    }



});
