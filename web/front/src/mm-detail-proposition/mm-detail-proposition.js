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
      this.brainstormedBy = this.data.brainstormedBy;
      this.concretizedBy = this.data.concretizedBy;
      this.representedBy = this.data.representedBy;
      this.hasInterest = this.data.hasInterest;
      this.image = this.data.properties.image;
  },

    onClickThematic(e){
        e.preventDefault();
        let searchThemeFilter = document.getElementById('searchThemeFilter');
        searchThemeFilter.value = e.target.rel;
        gvc.goSearch();
    }

});
