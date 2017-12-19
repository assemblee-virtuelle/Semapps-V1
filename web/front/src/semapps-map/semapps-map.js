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
      this.OSM = L.map('semapps').setView([48.862725, 2.287592000000018], 6);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
      }).addTo(this.OSM );
      this.markers = L.markerClusterGroup();
      this.OSM.addLayer(this.markers) ;
      this.OSM.scrollWheelZoom.disable();
      this.OSM.off('dblclick');
      this.OSM.on('dblclick', onMapClick);
      this.OSM.on('mouseover',mouseOver);
      this.OSM.on('mouseout',mouseOut);
      this.pinAvailaible = [];
      this.awesome= {
          "http://virtual-assembly.org/pair#Person":L.AwesomeMarkers.icon({
              icon: 'user',
              markerColor: 'blue'
          }),
          "http://virtual-assembly.org/pair#Organization":L.AwesomeMarkers.icon({
              icon: 'tower',
              markerColor: 'blue'
          }),
          "http://virtual-assembly.org/pair#Project":L.AwesomeMarkers.icon({
              icon: 'screenshot',
              markerColor: 'red'
          }),
          "http://virtual-assembly.org/pair#Event":L.AwesomeMarkers.icon({
              icon: 'calendar',
              markerColor: 'orange'
          }),
          "http://virtual-assembly.org/pair#Proposal":L.AwesomeMarkers.icon({
              icon: 'info-sign',
              markerColor: 'green'
          }),
          "http://virtual-assembly.org/pair#Document":L.AwesomeMarkers.icon({
              icon: 'folder-open',
              markerColor: 'black'
          }),
          "http://virtual-assembly.org/pair#DocumentType":L.AwesomeMarkers.icon({
              icon: 'pushpin',
              markerColor: 'black'
          }),
      }

  },

    /**
     * affiche tous les points enregistré
     */
    pinShowAll() {
        "use strict";
        for (let key in this.pins) {
            if (this.pins.hasOwnProperty(key)) {
                this.pinShow(key);
            }
        }
    },

    pinShow(key){
        "use strict";
        if (this.pins.hasOwnProperty(key) && this.pinAvailaible[key]) {
            this.markers.addLayer(this.pins[key]);
            this.pinAvailaible[key] = false;

        }else{
            log("error: " + key + " is not a marker")
            log(this.pins)
        }

        //this.pins[key].addTo(this.OSM);
    },


    addPin(latitude,longitude, key, text,type) {
        "use strict";
        this.pinAvailaible[key] = true;
        if (this.awesome[type] !== undefined) {
            this.pins[key] = L.marker([latitude,longitude],  {icon: this.awesome[type]})
                .bindPopup( '<a href="#" onclick="getDetail(this)" rel="'+key+'"><h5>'+text+'</h5> </a>');
        }else{
            this.pins[key] = L.marker([latitude,longitude])
                .bindPopup('<a href="#" onclick="getDetail(this)" rel="'+key+'"><h5>'+text+' </h5></a>');
        }

        this.pinShow(key);
    },
    /**
     * affiche un point et efface les autres
     * @param key -- la clé qui va être la seule affiché
     */
    pinShowOne(key) {
        "use strict";
        this.pinHideAll();
        this.pinShow(key);
    },

    /**
     * efface un point
     * @param key -- la clé du point a ne plus afficher
     */
    pinHide(key) {
        "use strict";
        if(this.pins[key] !== undefined && !this.pinAvailaible[key]){
            let marker = this.pins[key];
            this.markers.removeLayer(marker);
            this.pinAvailaible[key] = true;
        }

    },

    /**
     * efface tous les points
     */
    pinHideAll() {
        "use strict";
        log('start pinHideAll');
        log(this.pins);
        for (let key in this.pins){
            if (this.pins.hasOwnProperty(key)) {
                this.pinHide(key);
            }
        }
        log('stop pinHideAll');

    },

    handleClick(e) {
        e.preventDefault();
        semapps.scrollToContent();
        semapps.myRoute = "detail";
        semapps.goToPath('detail', {
            uri: window.encodeURIComponent(this.uri)
        });
    },


});

function onMapClick(e) {
    log('onMapClick');

    if (semapps.map.OSM.scrollWheelZoom.enabled()) {
        semapps.map.OSM.scrollWheelZoom.disable();
    }
    else {
        semapps.map.OSM.scrollWheelZoom.enable();
    }
    mouseOver();
}
function mouseOver(e) {
    log('mouseOver');
    let element = document.getElementById('semapps-map-black');
    if(!semapps.map.OSM.scrollWheelZoom.enabled()){
        $('#semapps-map-black').animate({ height: "100%"},'fast','linear');
        element.style.display = 'block';
        $('#semapps-map-message').show()
    }
    else{
        $('#semapps-map-black').animate({ height: "0px"},'fast','linear');
        $('#semapps-map-message').hide()
    }
}
function mouseOut(e){
    log('mouseOut');

    $('#semapps-map-message').hide();
    $('#semapps-map-black').animate({ height: "0px"},'fast','linear')
}
function getDetail(elem) {
    semapps.goToPath('detail', {
        uri: window.encodeURIComponent(elem.rel)
    });
}
