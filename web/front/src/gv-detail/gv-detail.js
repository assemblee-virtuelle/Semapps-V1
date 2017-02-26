Polymer({
  is: 'gv-detail',
  properties: {
    id: String,
    title: String,
    description: String
  },

  attached: function () {
    "use strict";
    $('#detailBack').click(() => {
      history.back();
    });
  }
});
