Polymer({
  is: 'gv-map',

  properties: {
    route: {
      type: Object,
      observer: '_routeChanged'
    }
  },

  _routeChanged: function () {
    this.updateVisibility();
  },

  // Wait all HTML to be loaded.
  attached() {
    this.$gvMap = $(document.getElementById('gv-map'));
    this.$gvMapPins = $(document.getElementById('gv-map-pins'));
    this.$mapZones = this.$gvMap.find('.mapZone');
    this.hoverActive = true;
    // Global ref.
    window.gvmap = this;
    // Bind events.
    this.$mapZones
      .on('mouseover', (e) => {
        this.hoverActive && this.mapSelectBuilding(e.currentTarget.getAttribute('id').split('-')[1]);
      })
      .on('mouseout', (e) => {
        this.hoverActive && this.mapDeselectBuilding(e.currentTarget.getAttribute('id').split('-')[1]);
      })
      // Click.
      .on('click', (e) => {
        let key = e.currentTarget.getAttribute('id').split('-')[1];
        key = key !== window.gvc.buildingSelected ? key : gvc.buildingSelectedAll;
        // Set value to current select.
        window.gvc.buildingSelected = key;
        // Display.
        this.mapSelectBuilding(key);
        // Search.
        window.gvc.searchEvent();
        this.hoverActive = false;
        // Scroll.
        window.gvc.scrollToSearchResults(() => {
          "use strict";
          this.hoverActive = true;
        });
      });

    this.updateVisibility();
  },

  mapSelectBuilding(key) {
    // Deselect if already selected.
    this.mapDeselectBuilding();
    this.mapIsOver = true;
    gvc.buildingSelected =
      this.mapSelectCurrent = key;
    let zone = this.mapGetZone(this.mapSelectCurrent);
    if (zone) {
      zone.classList.add('strong');
      zone.classList.remove('discreet');
      // Hide all.
      this.mapSelectBuildingToggle(true);
    }
    else {
      // Display all.
      this.mapSelectBuildingToggle(false);
    }
  },

  mapDeselectBuilding() {
    if (this.mapSelectCurrent) { log(this.mapSelectCurrent);
      this.mapGetZone(this.mapSelectCurrent).classList.remove('strong');
      delete this.mapSelectCurrent;
    }
    if (this.mapTimeout) {
      clearTimeout(this.mapTimeout);
    }
    this.mapIsOver = false;
    this.mapTimeout = setTimeout(() => {
      // Mouse is still not over.
      if (!this.mapIsOver) {
        this.mapDeselectBuildingReset();
      }
    }, 500);
  },

  mapDeselectBuildingReset() {
    this.mapTimeout = false;
    this.mapSelectBuildingToggle(false);
  },

  mapSelectBuildingToggle(add) {
    // Define add or remove class.
    var method = add ? 'add' : 'remove';
    this.$mapZones.each((index, zone) => {
      // On all paths.
      zone.classList[method]('discreet');
    });
  },

  mapGetZone(key) {
    return document.getElementById('mapZone-' + key);
  },

  mapShowBuildingPinAll() {
    "use strict";
    this.$gvMapPins.empty();
    for (let i of Object.keys(gvc.buildings)) {
      this.mapShowBuildingPin(gvc.buildings[i].key);
    }
  },

  mapShowBuildingPin(buildingKey) {
    if (buildingKey !== gvc.buildingSelectedAll) {
      let pin = document.createElement('gv-map-pin');
      pin.building = buildingKey;
      this.$gvMapPins.append(pin);
    }
  },

  updateVisibility() {
    "use strict";
    switch (gvc.mainComponent.get('route.path').split('/')[1]) {
      case 'detail':
        // Hide.
        this.$gvMap.addClass('fadeOut').removeClass('fadeIn');
        break;
      default:
        // Show.
        this.$gvMap.addClass('fadeIn').removeClass('fadeOut');
        break;
    }
  }
});
