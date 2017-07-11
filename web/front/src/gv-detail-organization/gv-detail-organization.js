Polymer({
  is: 'gv-detail-organization',
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
    this.resourceNeeded = this.data.resourceNeeded;
    this.resourceProposed = this.data.resourceProposed;
    this.topicInterest = this.data.topicInterest;
    this.person_hasMember = this.data.person_hasMember;
    this.orga_hasMember = this.data.orga_hasMember;
    this.OrganizationalCollaboration = this.data.OrganizationalCollaboration;
    this.thesaurus = this.data.thesaurus;
    this.projet = this.data.projet;
    this.event = this.data.event;
    this.proposition = this.data.proposition;
    this.responsible = this.data.responsible;
    this.memberOf = this.data.memberOf;
    // Computed values.
    this.title = this.data.properties.foafName;
    this.buildingTitle = gvc.buildings[this.data.properties.building].title;
  },

    onClickThematic(e){
        e.preventDefault();
        let searchThemeFilter = document.getElementById('searchThemeFilter');
        searchThemeFilter.value = e.target.rel;
        gvc.goSearch();
    }
});
