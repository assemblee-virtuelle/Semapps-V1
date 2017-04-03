Polymer({
  is: 'gv-detail-person',
  properties: {},

  handleClickDetail(e) {
    e.preventDefault();
    gvc.goToPath('detail', {
      uri: window.encodeURIComponent(e.currentTarget.getAttribute('rel'))
    });
  },

  attached() {
    GVCarto.ready(() => {
      gvc.initElementGlobals(this);
    });
    log(this.data);
    // Raw values.
    $.extend(this, this.data.properties);
    this.memberOf = this.data.memberOf;
    this.topicInterest = this.data.topicInterest;
    this.expertize = this.data.expertize;
    this.knows = this.data.knows;
  }
});
