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
    gvc.myRoute = "detail";
    gvc.goToPath('detail', {
      uri: window.encodeURIComponent(this.uri)
    });
  },

  attached() {
    "use strict";
    $.extend(this, this.data);
    this.image = gvc.imageOrFallback(this.image, this.type);
    this.info = '';
    let c = '';
    log(this.data);
    if (this.start) {
        let eventBegin = new Date(this.start);
        this.info += c + "le " + eventBegin.getDate() + '/' + (eventBegin.getMonth() + 1) + '/' + eventBegin.getFullYear() + ' à ' + eventBegin.getHours() + ' H ' + eventBegin.getMinutes() + ' min';
        c=' | ';
    }
    if (this.desc) {
        this.info += c + this.desc;
        c=' | ';
    }
    if (this.subject) {
      this.info += c + this.subject;
        c=' | ';
    }
    if (gvc.buildings[this.building]) {
      this.info += c + gvc.buildings[this.building].title;
        c=' | ';
    }
  },
    haveTitle(value){
        return value != null;
    }
});
