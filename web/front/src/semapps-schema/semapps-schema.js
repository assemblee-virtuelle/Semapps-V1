Polymer({
  is: 'semapps-schema',

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
    this.$mapZones = this.$gvMap.find('.mapZone');
    this.hoverActive = true;

    // Global ref.
      semapps.schema = this;

    // Wait for buildings to be loaded.
      SemAppsCarto.ready(this.start.bind(this));
  },

  start() {
    "use strict";

    // Create pins.
    let pins = [];
    $.each(semapps.buildings, (building) => {
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
        this.buildingClick(key);
        // Disable hover temporally.
        this.hoverActive = false;
        // Scroll.
          semapps.scrollToContent(() => {
          "use strict";
          this.hoverActive = true;
        });
      });

    this.buildingSelect(semapps.buildingSelected, false);
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

  buildingClick(building) {
    "use strict";
    // Do not allow building selection and search term in the same time.
      semapps.domSearchTextInput.value = '';
      semapps.schema.buildingSelect(building);
  },

  buildingSelect(building, reloadSearch) {
    "use strict";
    // Deselect current.
    this.pinsRegistry[semapps.buildingSelected] && this.pinsRegistry[semapps.buildingSelected].deselect();
    let selected =
        semapps.buildingSelected = building || semapps.buildingSelectedAll;
    // Select new one.
    this.pinsRegistry[selected] && this.pinsRegistry[selected].select();
    // Reload by default.
    (reloadSearch !== false) && semapps.goSearch();
  },

  zoneGet(key) {
    return document.getElementById('mapZone-' + key);
  },

  pinShow(building, text) {
    "use strict";
    if(this.pinsRegistry[building] != null)
      this.pinsRegistry[building].show(text);
  },

  pinShowOne(building, text) {
    "use strict";
    this.pinHideAll();
    this.pinShow(building, text);
  },

  pinHide(building) {
    "use strict";
    if(this.pinsRegistry[building] != null)
     this.pinsRegistry[building].hide();
  },

  pinHideAll() {
    "use strict";
    $.each(semapps.buildings, (building) => {
        semapps.schema.pinHide(building);
    });
  }
});
