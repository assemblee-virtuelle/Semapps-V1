Polymer({
  is: 'semapps-detail-projet',
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
      semapps.map.pinShowOne(this.address[0]);

  },

    onClickThematic(e){
        e.preventDefault();
        let searchThemeFilter = document.getElementById('searchThemeFilter');
        searchThemeFilter.value = e.target.rel;
        semapps.goSearch();
    }

});
