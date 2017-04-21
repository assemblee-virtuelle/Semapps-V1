Polymer({
  is: 'gv-detail-organization',
  properties: {},

  attached() {
    GVCarto.ready(() => {
      gvc.initElementGlobals(this);
    });

    // Raw values.
    $.extend(this, this.data.properties);

    // Computed values.
    this.title = this.data.properties.foafName;
    this.buildingTitle = gvc.buildings[this.data.properties.building].title;
  }
});
