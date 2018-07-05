Polymer({
  is: 'semapps-detail-organization',
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
    //log(this.data);
    // Raw values.
    $.extend(this, this.data.properties);
    this.hasResponsible = this.data.hasResponsible;
    this.hasMember = this.data.hasMember;
    this.employs = this.data.employs;
    this.partnerOf = this.data.partnerOf;
    this.involvedIn = this.data.involvedIn;
    this.manages = this.data.manages;
    this.organizes = this.data.organizes;
    this.participantOf = this.data.participantOf;
    this.offers = this.data.offers;
    this.needs = this.data.needs;
    this.brainstorms = this.data.brainstorms;
    this.affiliates = this.data.affiliates;
    this.hasInterest = this.data.hasInterest;
    this.hasSubject = this.data.hasSubject;
    this.internal_author = this.data.internal_author;
    this.internal_contributor = this.data.internal_contributor;
    this.internal_publisher = this.data.internal_publisher;
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
