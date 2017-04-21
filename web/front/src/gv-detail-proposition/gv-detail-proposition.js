Polymer({
  is: 'gv-detail-proposition',
  properties: {},

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
