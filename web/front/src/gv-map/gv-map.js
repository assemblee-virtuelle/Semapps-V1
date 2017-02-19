Polymer({
  is: 'gv-map',
  // Wait all HTML to be loaded.
  attached() {
    this.$mapZones = $('#gv-map .mapZone');

    // Bind events.
    this.$mapZones
      .on('mouseover', (e) => {
        this.mapSelectBuilding(e.currentTarget.getAttribute('id').split('-')[1]);
      })
      .on('mouseout', (e) => {
        this.mapDeselectBuilding(e.currentTarget.getAttribute('id').split('-')[1]);
      })
      // Click.
      .on('click', (e) => {
        // Set value to current select.
        window.gvc.buildingSelected = e.currentTarget.getAttribute('id').split('-')[1];
        // Search.
        window.gvc.searchEvent();
        // Scroll.
        window.gvc.scrollToSearch();
      });
  },

  mapSelectBuilding(key) {
    this.mapIsOver = true;
    this.mapSelectCurrent = key;
    let zone = this.mapGetZone(this.mapSelectCurrent);
    if (zone) {
      zone.classList.add('strong');
      zone.classList.remove('discreet');
      this.mapSelectBuildingToggle(true);
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
