Polymer({
  is: 'gv-map',

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
    this.$gvMap = $(document.getElementById('gv-map'));
    this.$gvMapPins = $(document.getElementById('gv-map-pins'));
    this.$mapZones = this.$gvMap.find('.mapZone');
    this.hoverActive = true;
    this.domPins = {};

    // Global ref.
    gvc.map = this;

    // Wait for buildings to be loaded.
    GVCarto.ready(this.start.bind(this));

    GVCarto.ready(() => {
      //  "use strict";

      //this.updateVisibility();
    });
  },

  start() {
    "use strict";
    // TODO  this.ready = true;

    // Create pins.
    let pins = [];
    $.each(gvc.buildings, (building) => {
      pins.push(building);
    });
    this.pins = pins;

    // Bind events.
    this.$mapZones
      .on('mouseover', (e) => {
        this.hoverActive && this.buildingHighlight(e.currentTarget.getAttribute('id').split('-')[1]);
      })
      .on('mouseout', (e) => {
        this.hoverActive && this.buildingHighlightOff(e.currentTarget.getAttribute('id').split('-')[1]);
      })
      // Click.
      .on('click', (e) => {
        let key = e.currentTarget.getAttribute('id').split('-')[1];
        // Launch search.
        this.buildingSelect(key);
        // Disable hover temporally.
        this.hoverActive = false;
        // Scroll.
        window.gvc.scrollToSearchResults(() => {
          "use strict";
          this.hoverActive = true;
        });
      });
  },

  buildingHighlight(key) {
    // Deselect if already selected.
    this.buildingHighlightOff();
    this.mapIsOver = true;
    this.buildingHighlighted = key;
    let zone = this.zoneGet(this.buildingHighlighted);
    if (zone) {
      zone.classList.add('strong');
      zone.classList.remove('discreet');
      // Hide all.
      this.buildingHideAll(true);
    }
    else {
      // Display all.
      this.buildingHideAll(false);
    }
  },

  buildingHighlightOff() {
    if (this.buildingHighlighted) {
      this.zoneGet(this.buildingHighlighted).classList.remove('strong');
      delete this.buildingHighlighted;
    }
    if (this.mapTimeout) {
      clearTimeout(this.mapTimeout);
    }
    this.mapIsOver = false;
    this.mapTimeout = setTimeout(() => {
      // Mouse is still not over.
      if (!this.mapIsOver) {
        this.buildingHighlightReset();
      }
    }, 500);
  },

  buildingHighlightReset() {
    this.mapTimeout = false;
    this.buildingHideAll(false);
  },

  buildingHideAll(activate) {
    // Define add or remove class.
    var method = activate ? 'add' : 'remove';
    this.$mapZones.each((index, zone) => {
      // On all paths.
      zone.classList[method]('discreet');
    });
  },

  buildingSelect(building) {
    "use strict";
    if (gvc.buildingSelected && gvc.buildingSelected != gvc.buildingSelectedAll) {
      // Hid pin.
      this.pinsRegistry[gvc.buildingSelected].$$('.gv-map-pin-wrapper').classList.remove('selected');
    }
    gvc.buildingSelected = building || gvc.buildingSelectedAll;
    if (building) {
      // Show cross.
      this.pinsRegistry[building].$$('.gv-map-pin-wrapper').classList.add('selected');
    }
    // We don't need to reload all results.
    gvc.results.searchRender();
  },

  zoneGet(key) {
    return document.getElementById('mapZone-' + key);
  },

  pinShow(building, text) {
    "use strict";
    this.pinsRegistry[building].show(text);
  },

  pinHide(building) {
    "use strict";
    this.pinsRegistry[building].hide();
  }

  //mapShowBuildingPinAll() {
  //  "use strict";
  //  if (this.ready) {
  //    for (let buildingKey of Object.keys(gvc.buildings)) {
  //      if (gvc.buildings[buildingKey].organizationCount > 0) {
  //        this.domPins[buildingKey].show(gvc.buildings[buildingKey].organizationCount);
  //      }
  //    }
  //  }
  //},
  //
  //mapHideBuildingPinAll() {
  //  "use strict";
  //  if (this.ready) {
  //    for (let buildingKey of Object.keys(gvc.buildings)) {
  //      this.domPins[buildingKey].hide();
  //    }
  //  }
  //},
  //
  //mapHideBuildingPin(buildingKey) {
  //  if (this.domPins[buildingKey]) {
  //    this.domPins[buildingKey].hide();
  //  }
  //},
  //
  //updateVisibility() {
  //  "use strict";
  //  switch (gvc.mainComponent.get('route.path').split('/')[1]) {
  //    case 'detail':
  //      // Hide.
  //      this.$gvMap.addClass('fadeOut').removeClass('fadeIn');
  //      break;
  //    default:
  //      // Show.
  //      this.$gvMap.addClass('fadeIn').removeClass('fadeOut');
  //      break;
  //  }
  //}
});
