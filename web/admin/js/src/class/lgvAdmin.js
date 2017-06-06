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

    // Change image field.
    $('form').each((index, form) => {
      let $form = $(form);
      $form.find('div.formEditAvatar input[type="file"]').change(() => {
        // Display a nice spinner.
        this.pageLoadingStart();
        // Send form will reload the page.
        $form.submit();
      });
    });

    new LgvAdminPageTeam(this);
    new LgvAdminPageProfile(this);
    new LgvAdminPageOrga(this);
    new LgvAdminPageComponent(this);
    new LgvAdminPageUser(this);
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
