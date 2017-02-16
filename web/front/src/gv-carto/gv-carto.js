Polymer({
  is: 'gv-carto',
  ready() {
    // Devel
    window.log = (m) => {
      console.log(m);
    };

    window.gvc = this;

    // Special class for dev env.
    if (window.location.hostname === '127.0.0.1') {
      window.document.body.classList.add('dev-env');
    }
  }
});
