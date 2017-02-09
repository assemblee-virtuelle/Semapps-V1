$(function () {
  "use strict";

  // Use a global object.
  var LgvAdmin = function () {
    // Save globally.
    window.lgvAdmin = this;

    // Define reused variables.
    this.$modalConfirm = $('#modalConfirm');
    this.$modalConfirmBody = this.$modalConfirm.find('.modal-body:first');
    this.$modalConfirmValidate = this.$modalConfirm.find('.btn-primary:first');

    // Create reference for callbacks.
    var $self = this;

    // On user profile, remove user button.
    $('.team-user-destroy').click(function (e) {
      // Disable default click behavior.
      e.preventDefault();
      // Use custom modal for message.
      $self.modalConfirm('Êtes-vous sûr de vouloir supprimer ce compte ? ' +
        'Toutes les informations du profil seront perdues, ' +
        'et le membre n\'aura plus accès au site.', function () {
        // TODO Remove user (by redirecting to deletion page, or via ajax)
        alert('TODO !');
      });
    });
  };

  LgvAdmin.prototype = {
    modalConfirm: function (message, callback) {
      this.$modalConfirmBody.html(message);
      this.$modalConfirm.modal('show');
      var $self = this;
      this.$modalConfirmValidate.one('click', function () {
        $self.$modalConfirm.modal('hide');
        callback();
      });
    }
  };

  new LgvAdmin();
});
