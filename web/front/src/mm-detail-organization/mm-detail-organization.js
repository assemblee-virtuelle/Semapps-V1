Polymer({
  is: 'mm-detail-organization',
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
    this.responsible = this.data.responsible;
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

  },

    onClickThematic(e){
        e.preventDefault();
        let searchThemeFilter = document.getElementById('searchThemeFilter');
        searchThemeFilter.value = e.target.rel;
        gvc.goSearch();
    }
});
