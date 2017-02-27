class LgvAdmin {
  constructor() {
    // Wait document loaded.
    $(()=> {
      this.init();
    });
  }

  init() {
    // Save globally.
    window.lgvAdmin = this;

    // Define reused variables.
    this.$modalConfirm = $('#modalConfirm');
    this.$modalConfirmBody = this.$modalConfirm.find('.modal-body:first');
    this.$modalConfirmValidate = this.$modalConfirm.find('.btn-primary:first');

    new LgvAdminPageTeam(this);
    new LgvAdminPageProfile(this);
  }

  modalConfirm(message, callback) {
    this.$modalConfirmBody.html(message);
    this.$modalConfirm.modal('show');
    this.$modalConfirmValidate.one('click', () => {
      this.$modalConfirm.modal('hide');
      callback();
    });
  }
}

