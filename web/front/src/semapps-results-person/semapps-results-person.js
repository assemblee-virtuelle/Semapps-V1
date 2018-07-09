Polymer({
  is: 'semapps-results-person',
  properties: {
    uri: String,
    label: String,
    type: String,
    description: String
  },

  handleClick(e) {
    e.preventDefault();
    semapps.scrollToContent();
    semapps.myRoute = "detail";
    semapps.goToPath('detail', {
      uri: window.encodeURIComponent(this.uri)
    });
  },

  attached() {
    "use strict";
    $.extend(this, this.data);
    this.image = semapps.imageOrFallback(this.image, this.type);
    this.info = '';
    let c = '';
    if (this.comment) {
        this.info += c + (this.comment.length > 150)?  this.comment.substr(0,150)+'...' : this.comment;
        c=' | ';
    }
  },
});
