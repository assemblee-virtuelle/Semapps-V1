Polymer({
  is: 'gv-avatar',
  properties: {
    image: String,
    uri: String,
    label: String
  },

  handleClickAvatar(e) {
    e.preventDefault();
    gvc.goToPath('detail', {
      uri: window.encodeURIComponent(e.currentTarget.getAttribute('rel'))
    });
  },
    showName(e){
      log(e.target);
  }
  });

