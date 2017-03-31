window.log = (m) => {
  "use strict";
  console.log(m);
};

class LgvAdmin {
  constructor() {
    // Wait document loaded.
    $(()=> {
      this.init();
    });
  }

  init() {
    this.$loadingPageSpin = $('#loadingPageSpin');
    // Save globally.
    window.lgvAdmin = this;

    // Define reused variables.
    this.$modalConfirm = $('#modalConfirm');
    this.$modalConfirmBody = this.$modalConfirm.find('.modal-body:first');
    this.$modalConfirmValidate = this.$modalConfirm.find('.btn-primary:first');

    $('.select2-dbPedia').each((e, item) => {
      new lgvAdminFormFieldDbPedia(item);
    });

    $('.select2-lookup').select2({
      ajax: {
        url: "/webservice/lookup",
        dataType: 'json',
        delay: 250,
        data: function (params) {
          return {
            QueryString: params.term,
            QueryClass: $(this).attr('data-query-class')
          };
        },
        processResults: function (data, params) {
          let items = [];
          for (let result of data.results) {
            items.push({
              id: result.uri,
              text: result.label
            });
          }
          return {
            results: items
          };
        }
      }
    });

    new LgvAdminPageTeam(this);
    new LgvAdminPageProfile(this);
    new LgvAdminPageOrga(this);
  }

  modalConfirm(message, callback) {
    this.$modalConfirmBody.html(message);
    this.$modalConfirm.modal('show');
    this.$modalConfirmValidate.one('click', () => {
      this.$modalConfirm.modal('hide');
      callback();
    });
  }

  pageLoadingStart() {
    this.$loadingPageSpin
      .removeClass('fadeOut')
      .addClass('fadeIn');
  }

  pageLoadingStop() {
    this.$loadingPageSpin
      .removeClass('fadeIn')
      .addClass('fadeOut');
  }
}

class lgvAdminFormFieldDbPedia {
  constructor(dom) {
    this.value = {};
    this.$ = $(dom);
    this.$selector = this.$.find('.tags-selector');
    this.$value = this.$.find('.tags-value');
    this.$tags = this.$.find('.tags');
    this.$selector.select2({
      width: '100%',
      placeholder: "Ajoutez un terme ici",
      ajax: {
        url: "http://lookup.dbpedia.org/api/search.asmx/PrefixSearch",
        dataType: 'json',
        delay: 250,
        data: (params) => {
          return {
            QueryString: params.term,
            MaxHits: 15
          };
        },
        processResults: (data) => {
          let items = [];
          this.selectValues = {};
          for (let result of data.results) {
            items.push({
              id: result.uri,
              text: result.label
            });
            this.selectValues[result.uri] = result.label;
          }
          return {
            results: items
          };
        },
        cache: true
      }
    });
    this.$selector.on("select2:select", (e) => {
      let uri = this.$selector.val();
      // Add to values.
      this.value[uri] = this.selectValues[this.$selector.val()];
      this.fillValues();
    });
  }

  fillValues() {
    this.$value.val(JSON.stringify(this.value));
    this.$tags.empty();
    $.each(this.value, (uri, text) => {
      // Build item.
      let $item = $('<span class="tag">' + text + ' <a href="#" class="remove-tag glyphicon glyphicon-remove"></a></span>')
      // Click event.
      $item.find('a.remove-tag').click((e) => {
        e.preventDefault();
        delete this.value[uri];
        this.fillValues();
      });
      // Append.
      this.$tags.append($item);
    });
    // Show tags section if not empty.
    this.$tags.toggle(!!Object.keys(this.value).length);
    // Clear selector.
    this.$selector.val(null).trigger("change");
  }
}
