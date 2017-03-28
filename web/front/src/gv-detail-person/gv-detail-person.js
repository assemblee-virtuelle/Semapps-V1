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

    // Temp
    if (this.data.properties.memberOf) {
      this.data.properties.memberOf = [this.data.properties.memberOf];
    }
    if (this.data.properties.expertize) {
      this.data.properties.expertize = [this.data.properties.expertize];
    }
    if (this.data.properties.topicInterest) {
      this.data.properties.topicInterest = [this.data.properties.topicInterest];
    }
    if (this.data.properties.knows) {
      this.data.properties.knows = [this.data.properties.knows];
    }

    // Raw values.
    $.extend(this, this.data.properties);


  }
});
