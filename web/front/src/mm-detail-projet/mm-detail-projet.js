Polymer({
  is: 'mm-detail-projet',
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
    this.buildingTitle = gvc.buildings[this.data.properties.building].title;
    this.concretizes = this.data.concretizes;
    this.needs = this.data.needs;
    this.involves = this.data.involves;
    this.managedBy = this.data.managedBy;
    this.representedBy = this.data.representedBy;
    this.image = this.data.properties.image;
  },

    onClickThematic(e){
        e.preventDefault();
        let searchThemeFilter = document.getElementById('searchThemeFilter');
        searchThemeFilter.value = e.target.rel;
        gvc.goSearch();
    }

});
