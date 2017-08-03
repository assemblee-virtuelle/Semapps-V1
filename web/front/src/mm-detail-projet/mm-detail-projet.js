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
    this.person_involves = this.data.person_involves;
    this.orga_involves = this.data.orga_involves;
    this.person_managedBy = this.data.person_managedBy;
    this.orga_managedBy = this.data.orga_managedBy;
    this.person_representedBy = this.data.person_representedBy;
    this.orga_representedBy = this.data.orga_representedBy;
    this.image = this.data.properties.image;
  },

    onClickThematic(e){
        e.preventDefault();
        let searchThemeFilter = document.getElementById('searchThemeFilter');
        searchThemeFilter.value = e.target.rel;
        gvc.goSearch();
    }

});
