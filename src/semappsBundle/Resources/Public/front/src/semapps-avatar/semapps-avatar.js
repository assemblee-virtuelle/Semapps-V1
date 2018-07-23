Polymer({
  is: 'semapps-avatar',
  properties: {
    image: String,
    uri: String,
    label: String
  },
    attached() {
        this.cutlabel =(this.label.length > 15)? semapps.capitalize(this.label,true).substr(0,10)+'...' : semapps.capitalize(this.label,true);
    },
  handleClickAvatar(e) {
    e.preventDefault();
    semapps.goToPath('detail', {
      uri: window.encodeURIComponent(e.currentTarget.getAttribute('rel'))
    });
  },
    showName(e){
      log(e.target);
  }
  });

