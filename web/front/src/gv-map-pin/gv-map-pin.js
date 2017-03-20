Polymer({
  is: 'gv-map-pin',
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
    gvc.map.pinsRegistry[this.building] = this;
    this.x = gvc.buildings[this.building].x;
    this.y = gvc.buildings[this.building].y;
    this.domWrapper = this.querySelector('.gv-map-pin-wrapper');
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

  handleClick() {
    "use strict";
    if (this.display !== 'none') {
      // Select building or deselect if already selected.
      gvc.map.buildingSelect(this.building !== gvc.buildingSelected && this.building);
    }
  },

  handleMouseOver() {
    "use strict";
    gvc.map.buildingHighlight(this.building);
  }
});
