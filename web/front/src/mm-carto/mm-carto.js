Polymer({
  is: 'mm-carto',

  ready() {
    "use strict";
    new window.GVCarto(this);
  }
});
