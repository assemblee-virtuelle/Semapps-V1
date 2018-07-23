Polymer({
  is: 'semapps-results-organizationType',
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
    //log(this.data);
    if (this.start) {
        let eventBegin = new Date(this.start);
        this.info += c + "le " + eventBegin.getDate() + '/' + (eventBegin.getMonth() + 1) + '/' + eventBegin.getFullYear() + ' à ' + eventBegin.getHours() + ' H ' + eventBegin.getMinutes() + ' min ';
        c=' | ';
    }
    if (this.desc) {
        this.info += c + (this.desc.length > 150)?  this.desc.substr(0,150)+'...' : this.desc;
        c=' | ';
    }
    if (this.subject) {
      this.info += c + this.subject;
        c=' | ';
    }
  },
    haveTitle(value){
        return value != null;
    }
});
