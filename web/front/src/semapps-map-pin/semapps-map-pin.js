Polymer({
  is: 'semapps-map-pin',
  properties: {
    display: {
      type: String,
      value: 'none'
    },
    x: {
      type: Number,
      value: 0
    },
    y: {
      type: Number,
      value: 0
    },
    text: {
      type: String,
      value: 0
    },
    building: {
      type: String,
      value: ''
    },
    fixedDisplay: {
      type: String,
      value: 'none'
    }
  },

  attached() {
    "use strict";

    if (typeof( semapps.buildings[this.building].x ) !== "undefined" || semapps.buildings[this.building].x != null) {
      semapps.schema.pinsRegistry[this.building] = this;
      this.x = semapps.buildings[this.building].x;
      this.y = semapps.buildings[this.building].y;
      this.domWrapper = this.querySelector('.semapps-map-pin-wrapper');
      this.$wrapper = this.$$('.semapps-map-pin-wrapper');
      if (this.building === semapps.buildingSelected) {
        this.select();
      }
    }else{
      semapps.schema.pinsRegistry[this.building] = null;
    }
  },

  show(text) {
    "use strict";
    this.text = text;
    this.display = '';
    this.domWrapper.classList.remove('fadeOut');
    this.domWrapper.classList.add('fadeIn');
  },

  hide() {
    "use strict";
    this.display = 'none';
    this.domWrapper.classList.remove('fadeIn');
    this.domWrapper.classList.add('fadeOut');
  },

  select() {
    "use strict";
    this.$wrapper.classList.add('selected');
  },

  deselect() {
    "use strict";
    this.$wrapper.classList.remove('selected');
  },

  handleClick() {
    "use strict";
    if (this.display !== 'none') {
      // Select building or deselect if already selected.
      semapps.schema.buildingClick(this.building !== semapps.buildingSelected && this.building);
    }
  },

  handleMouseOver() {
    "use strict";
    semapps.schema.buildingHighlight(this.building);
  },

  handleStopFixedSelection() {
    "use strict";
    semapps.schema.buildingSelect();
  }
});
