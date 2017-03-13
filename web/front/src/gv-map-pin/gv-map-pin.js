Polymer({
  is: 'gv-map-pin',
  properties: {
    x: {
      type: Number,
      value: 0
    },
    y: {
      type: Number,
      value: 0
    },
    number: {
      type: Number,
      value: 0
    },
    building: {
      type: String,
      value: ''
    }
  },

  attached() {
    "use strict";
    this.x = gvc.buildings[this.building].x;
    this.y = gvc.buildings[this.building].y;
  }
});
