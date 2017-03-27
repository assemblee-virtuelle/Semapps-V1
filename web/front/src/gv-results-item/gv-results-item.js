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
    gvc.goToPath('detail', {
      uri: window.encodeURIComponent(this.uri)
    });
  },

  attached() {
    "use strict";
    $.extend(this, this.data);
    if (!this.image) {
      this.image = '/common/images/result-no_picture-' + gvc.searchTypes[this.type].type + '.png';
    }
    this.info = gvc.searchTypes[this.type].label;
    if (this.subject) {
      this.info += ' | ' + this.subject;
    }
    if (gvc.buildings[this.building]) {
      this.info += ' | ' + gvc.buildings[this.building].title;
    }
  }
});
