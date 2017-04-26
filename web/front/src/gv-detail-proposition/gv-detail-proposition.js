Polymer({
  is: 'gv-detail-proposition',
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
    // Raw values.
    $.extend(this, this.data.properties);
      this.topicInterest = this.data.topicInterest;
      this.resourceNeeded = this.data.resourceNeeded;
      this.resourceProposed = this.data.resourceProposed;
      this.buildingTitle = gvc.buildings[this.data.properties.building].title;
  }
});
