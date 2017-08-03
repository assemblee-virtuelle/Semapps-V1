Polymer({
  is: 'mm-detail-proposition',
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
      this.person_brainstormedBy = this.data.person_brainstormedBy;
      this.orga_brainstormedBy = this.data.orga_brainstormedBy;
      this.person_concretizedBy = this.data.person_concretizedBy;
      this.orga_concretizedBy = this.data.orga_concretizedBy;
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
