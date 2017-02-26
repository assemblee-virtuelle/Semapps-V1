Polymer({
  is: 'gv-map',

  // Wait all HTML to be loaded.
  attached() {
    this.$mapZones = $('#gv-map .mapZone');
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
        key = key !== window.gvc.buildingSelected ? key : 'partout';
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
  },

  mapSelectBuilding(key) {
    // Deselect if already selected.
    this.mapDeselectBuilding();
    this.mapIsOver = true;
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
    if (this.mapSelectCurrent) {
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
  }
});
