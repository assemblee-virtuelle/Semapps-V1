Polymer({
  is: 'semapps-header',

  handleAccountClick(e) {
    "use strict";
    gvc.realLink(e);
  },

  attached() {
    "use strict";
    GVCarto.ready(this.start.bind(this));
  },

  start() {
    "use strict";
    this.domSearchTextInput = gvc.domId('searchText');
    this.haveName= gvc.haveName();
    this.name = gvc.name;
    this.thesaurus = gvc.thesaurus;

    let callbackSearchEvent = this.searchEvent.bind(this);
    let callbackSearchSubmit = (e) => {
      this.domSearchTextInput.blur();
      gvc.scrollToContent();
      callbackSearchEvent(e);
    };

    // Click on submit button.
    gvc.listen('searchForm', 'submit', callbackSearchSubmit);
    gvc.listen('searchThemeFilter', 'change', callbackSearchSubmit);

    let timeout;
    // Type in search field.
    gvc.listen('searchText', 'keyup', () => {
      if (timeout) {
        window.clearTimeout(timeout);
      }
      // Avoid to make too much requests when typing.
      timeout = window.setTimeout(callbackSearchEvent, 500);
    });
  },

  searchEvent(e) {
    // Event may be missing.
    e && e.preventDefault();
    // Do not allow to have both building selected && search term.
    gvc.map.buildingSelect(undefined, false);
    // Load search on term.
    gvc.goSearch();
  }
});
