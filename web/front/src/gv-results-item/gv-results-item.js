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
    gvc.scrollToContent();
    gvc.goToPath('detail', {
      uri: window.encodeURIComponent(this.uri)
    });
  },

  attached() {
    "use strict";
    $.extend(this, this.data);
    this.image = gvc.imageOrFallback(this.image, this.type);

    this.info = gvc.searchTypes[this.type].label;
    if (this.subject) {
      this.info += ' | ' + this.subject;
    }
    if (gvc.buildings[this.building]) {
      this.info += ' | ' + gvc.buildings[this.building].title;
    }
  },
    haveTitle(value){
        return value != null;
    }
});
