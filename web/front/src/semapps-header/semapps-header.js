Polymer({
  is: 'semapps-header',

  handleAccountClick(e) {
    "use strict";
    semapps.realLink(e);
  },

  handleResetClick(e) {
      "use strict";
      semapps.domId('searchText').value = '';
      semapps.domId('searchThemeFilter').value = '';
      semapps.goSearch();
  },

  attached() {
    "use strict";
    SemAppsCarto.ready(this.start.bind(this));
  },

  start() {
    "use strict";
    this.domSearchTextInput = semapps.domId('searchText');
    this.haveName= semapps.haveName();
    this.name = semapps.user.name;
    this.thesaurus = semapps.thesaurus;

    let callbackSearchEvent = this.searchEvent.bind(this);
    let callbackSearchSubmit = (e) => {
      this.domSearchTextInput.blur();
      semapps.scrollToContent();
      callbackSearchEvent(e);
    };

    // Click on submit button.
    semapps.listen('searchForm', 'submit', callbackSearchSubmit);
    semapps.listen('searchThemeFilter', 'change', callbackSearchSubmit);

    let timeout;
    // Type in search field.
    semapps.listen('searchText', 'keyup', () => {
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
    // Load search on term.
    semapps.goSearch();
  }
});
