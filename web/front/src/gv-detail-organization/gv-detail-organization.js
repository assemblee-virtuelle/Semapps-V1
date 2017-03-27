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

    // Computed values.
    this.buildingTitle = gvc.buildings[this.data.properties.building].title;
  }
});
