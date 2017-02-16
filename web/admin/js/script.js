class LgvAdmin {
  constructor() {
    // Save globally.
    window.lgvAdmin = this;

    // Define reused variables.
    this.$modalConfirm = $('#modalConfirm');
    this.$modalConfirmBody = this.$modalConfirm.find('.modal-body:first');
    this.$modalConfirmValidate = this.$modalConfirm.find('.btn-primary:first');

    // On user profile, remove user button.
    $('.team-user-delete').click((e) => {
      // Disable default click behavior.
      e.preventDefault();
      var userId = $(e.currentTarget).attr('rel');
      // Use custom modal for message.
      this.modalConfirm('Êtes-vous sûr de vouloir supprimer ce compte ? ' +
        'Toutes les informations du profil seront perdues, ' +
        'et le membre n\'aura plus accès au site.', () => {
        window.location.replace('/admin/user/delete/' + userId);
      });
    });
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

$(() => {
  new LgvAdmin();
});
