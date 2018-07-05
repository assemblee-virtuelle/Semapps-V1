Polymer({
  is: 'semapps-detail-project',
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
    this.concretizes = this.data.concretizes;
    this.needs = this.data.needs;
    this.hasInterest = this.data.hasInterest;
    this.involves = this.data.involves;
    this.managedBy = this.data.managedBy;
    this.representedBy = this.data.representedBy;
    this.offers = this.data.offers;
    this.image = this.data.properties.image;
    this.hasSubject = this.data.hasSubject;
    this.allowUri = semapps.detail.canEdit;

  },

    onClickThematic(e){
        e.preventDefault();
        let searchThemeFilter = document.getElementById('searchThemeFilter');
        searchThemeFilter.value = e.target.rel;
        semapps.goSearch();
    },
    handleClickRessource(e) {
        e.preventDefault();
        semapps.goToPath('ressource', {
            uri: window.encodeURIComponent(e.currentTarget.getAttribute('rel')),
            person: window.encodeURIComponent(this.uri)
        });
    },

});
