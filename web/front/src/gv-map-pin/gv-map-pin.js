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
    text: {
      type: String,
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
    this.domWrapper = this.querySelector('.gv-map-pin-wrapper');
  },

  show(text) {
    "use strict";
    this.text = text;
    this.domWrapper.style.display = '';
    this.domWrapper.classList.remove('fadeOut');
    this.domWrapper.classList.add('fadeIn');
  },

  hide() {
    "use strict";
    this.domWrapper.classList.remove('fadeIn');
    this.domWrapper.classList.add('fadeOut');
  },

  handleClick() {
    "use strict";
    gvmap.mapSelectBuilding(this.building);
    gvc.searchEvent();
  }
});
