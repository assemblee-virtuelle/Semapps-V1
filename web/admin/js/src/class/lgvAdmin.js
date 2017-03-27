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

    $('.select2-dbPedia').select2({
      width: '100%',
      ajax: {
        url: "http://lookup.dbpedia.org/api/search.asmx/PrefixSearch",
        dataType: 'json',
        delay: 250,
        data: function (params) {
          return {
            QueryString: params.term,
            MaxHits: 15
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
        },
        cache: true
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

