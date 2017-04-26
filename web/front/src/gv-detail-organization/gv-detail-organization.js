Polymer({
  is: 'gv-detail-organization',
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
    this.resourceNeeded = this.data.resourceNeeded;
    this.resourceProposed = this.data.resourceProposed;
    this.topicInterest = this.data.topicInterest;
    // Computed values.
    this.title = this.data.properties.foafName;
    this.buildingTitle = gvc.buildings[this.data.properties.building].title;
  }
});
