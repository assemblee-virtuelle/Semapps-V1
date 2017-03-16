Polymer({
  is: 'gv-results-item',
  properties: {
    uri: String,
    label: String,
    type: String,
    description: String
  },

  handleClick(e) {
    e.preventDefault();
    gvc.mainComponent.set('route.path', 'detail');
    gvc.mainComponent.set('queryParams', {
      uri: window.encodeURIComponent(this.uri)
    });
  },

  attached() {
    "use strict";
    this.info = gvc.searchTypes[this.type];
    if (this.subject) {
      this.info += ' | ' + this.subject;
    }
    if (gvc.buildings[this.building]) {
      this.info += ' | ' + gvc.buildings[this.building].title;
    }
  }
});
