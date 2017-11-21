Polymer({
  is: 'semapps-carto',

  ready() {
    "use strict";
    new window.GVCarto(this);
  }
});
