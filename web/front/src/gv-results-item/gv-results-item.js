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
    // Set first params.
    gvc.mainComponent.set('queryParams', {
      uri: window.encodeURIComponent(this.uri)
    });
    // Changing route fires an event.
    gvc.mainComponent.set('route.path', 'detail');
  },

  noImage() {
    "use strict";
    return !!this.image;
  },

  attached() {
    "use strict";
    $.extend(this, this.data);
    this.info = gvc.searchTypes[this.type];
    if (this.subject) {
      this.info += ' | ' + this.subject;
    }
    if (gvc.buildings[this.building]) {
      this.info += ' | ' + gvc.buildings[this.building].title;
    }
  }
});
