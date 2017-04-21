Polymer({
  is: 'gv-detail-projet',
  properties: {},

  attached() {
    GVCarto.ready(() => {
      gvc.initElementGlobals(this);
    });
    // Raw values.
    $.extend(this, this.data.properties);
    this.buildingTitle = gvc.buildings[this.data.properties.building].title;
    this.resourceNeeded = this.data.resourceNeeded;
    this.resourceProposed = this.data.resourceProposed;
    if (this.projectStart) {
      let projectStart = new Date(this.projectStart);
      this.projectStart = projectStart.getDate() + '/' + (projectStart.getMonth() + 1) + '/' + projectStart.getFullYear() + ' ' + projectStart.getHours() + ' H ' + projectStart.getMinutes() + ' min';
    }
  }
});
