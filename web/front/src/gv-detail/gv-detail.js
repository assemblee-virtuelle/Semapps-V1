Polymer({
  is: 'gv-detail',
  properties: {
    id: String,
    title: String,
    description: String,
    route: {
      type: Object,
      observer: '_routeChanged'
    }
  },

  attached: function () {
    "use strict";
    $('#detailBack').click(() => {
      history.back();
    });
  }
});
