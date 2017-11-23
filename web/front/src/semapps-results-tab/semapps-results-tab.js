Polymer({
  is: 'semapps-results-tab',
  properties: {
    data: {
      type: Object,
      value: null
    },
    counter: {
      type: Number,
      value: 0,
      observer: '_counterChanged'
    },
      show: String
  },

  attached() {
    "use strict";
    this.$counter = $(this.$$('.counter'));
    $.extend(this, this.data);
    semapps.results.tabRegister(this.type, this);
    this.refresh();
  },

  refresh() {
    "use strict";
    if (this.$counter) {
      this.$counter.toggle(this.counter);
      if (this.counter !== 0){
          this.show = '' ;
      }
      else
          this.show = 'none'
    }
  },

  _counterChanged() {
    "use strict";
    this.refresh();
  },

  handleTabClick() {
    "use strict";
    semapps.results.selectType(this.type);
  }
});
