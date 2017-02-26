Polymer({
  is: 'gv-detail',
  properties: {
    id: String,
    title: String,
    description: String
  },

  attached: function () {
    "use strict";
    window.gvc.detailLoad(window.location.hash.slice(1));
  }
});
