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
        this.globalX = 48.862725;
        this.globalY = 2.287592000000018;
        this.globalZoom = 6;
        let maxZoom = (semapps.isAnonymous())? 12:18;
        let minZoom = 0;
        this.entities = semapps.entities;

        this.OSM = L.map('semapps',{maxZoom: maxZoom, minZoom:minZoom}).setView([this.globalX,this.globalY], this.globalZoom);
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
        this.awesome = this.getMarkers();
    },

    getMarkers() {
        "use strict";
        let ret = {};
        for (let entity in this.entities){
            ret[entity] = L.AwesomeMarkers.icon({
                icon:this.entities[entity].icon,
                makerColor:this.entities[entity].markerColor
            })
        }
        return ret;
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
        this.OSM.setView(this.pins[key].getLatLng(),15,{animate: true});
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
        //log(this.pins);
        for (let key in this.pins){
            if (this.pins.hasOwnProperty(key)) {
                this.pinHide(key);
            }
        }

    },

    zoomGlobal(){
        this.OSM.setView([this.globalX,this.globalY], this.globalZoom);
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
    if (semapps.map.OSM.scrollWheelZoom.enabled()) {
        semapps.map.OSM.scrollWheelZoom.disable();
    }
    else {
        semapps.map.OSM.scrollWheelZoom.enable();
    }
    mouseOver();
}
function mouseOver(e) {

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

    $('#semapps-map-message').hide();
    $('#semapps-map-black').animate({ height: "0px"},'fast','linear')
}
function getDetail(elem) {
    semapps.goToPath('detail', {
        uri: window.encodeURIComponent(elem.rel)
    });
}
