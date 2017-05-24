Polymer({
  is: 'gv-detail-projet',
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
    this.resourceNeeded = this.data.resourceNeeded;
    this.resourceProposed = this.data.resourceProposed;
    this.topicInterest = this.data.topicInterest;
    if (this.projectStart) {
      let projectStart = new Date(this.projectStart);
      this.projectStart = projectStart.getDate() + '/' + (projectStart.getMonth() + 1) + '/' + projectStart.getFullYear();
    }
  },

    onClickThematic(e){
        e.preventDefault();
        let searchThemeFilter = document.getElementById('searchThemeFilter');
        searchThemeFilter.value = e.target.rel;
        gvc.goSearch();
    }

});
