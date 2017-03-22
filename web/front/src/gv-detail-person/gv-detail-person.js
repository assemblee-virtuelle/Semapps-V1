Polymer({
  is: 'gv-detail-person',
  properties: {},

  handleClickDetail(e) {
    e.preventDefault();
    gvc.goToPath('detail', {
      uri: window.encodeURIComponent(e.currentTarget.getAttribute('rel'))
    });
  },

  attached() {
    log(this.data.properties.mbox.indexOf('mailto'));
    if (this.data.properties.mbox.indexOf('mailto') === 0) {
      this.mbox = 'OKOK';
    }
    else {
      this.mbox = this.data.properties.mbox;
    }
  }
});
