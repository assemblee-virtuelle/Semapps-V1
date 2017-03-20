Polymer({
  is: 'gv-header',

  attached() {
    "use strict";
    GVCarto.ready(this.start.bind(this));
  },

  start() {
    "use strict";
    this.domSearchTextInput = gvc.domId('searchText');

    let callbackSearchEvent = this.searchEvent.bind(this);

    // Click on submit button.
    gvc.listen('searchForm', 'submit', (e) => {
      this.domSearchTextInput.blur();
      gvc.scrollToSearchResults();
      callbackSearchEvent(e);
    });

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
    gvc.goSearch();
  }
});
