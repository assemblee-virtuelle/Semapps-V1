Polymer({
  is: 'semapps-detail-proposal',
  properties: {},

    handleClickDetail(e) {
        e.preventDefault();
        semapps.goToPath('detail', {
            uri: window.encodeURIComponent(e.currentTarget.getAttribute('rel'))
        });
    },

  attached() {
    SemAppsCarto.ready(() => {
      semapps.initElementGlobals(this);
    });
    // Raw values.
    $.extend(this, this.data.properties);
      this.brainstormedBy = this.data.brainstormedBy;
      this.concretizedBy = this.data.concretizedBy;
      this.representedBy = this.data.representedBy;
      this.hasInterest = this.data.hasInterest;
      this.image = this.data.properties.image;
      this.hasSubject = this.data.hasSubject;
  },

    onClickThematic(e){
        e.preventDefault();
        let searchThemeFilter = document.getElementById('searchThemeFilter');
        searchThemeFilter.value = e.target.rel;
        semapps.goSearch();
    },
    handleClickRessource(e) {
        e.preventDefault();
        log('test');
        semapps.goToPath('ressource', {
            uri: window.encodeURIComponent(e.currentTarget.getAttribute('rel')),
            person: window.encodeURIComponent(this.uri)
        });
    },
});
