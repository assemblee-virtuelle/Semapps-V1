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
    $.extend(true, this, {
      title: this.data.properties.foafName,
      image: this.data.properties.image,
      arrivalDate: this.data.properties.arrivalDate,
      state: this.data.properties.gvStatus,
      convention: this.data.properties.conventionType,
      themes: this.data.properties.foafStatus,
      proposedContribution: this.data.properties.proposedContribution,
      realisedContribution: this.data.properties.realisedContribution,
      room: this.data.properties.room,
      subject: this.data.properties.subject,
      building: gvc.buildings[this.data.properties.building].title
    });
  }
});
