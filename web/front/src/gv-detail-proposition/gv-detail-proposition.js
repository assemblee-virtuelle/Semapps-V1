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
      // dump(this.data.properties.building);
      // dump(gvc.buildings);
      this.buildingTitle = gvc.buildings[this.data.properties.building].title;
  }
});
