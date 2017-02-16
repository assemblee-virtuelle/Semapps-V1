class LgvAdminPageTeam extends LgvAdminPage {
  constructor(admin) {
    super(admin);

    // On user profile, remove user button.
    $('.team-user-delete').click((e) => {
      // Disable default click behavior.
      e.preventDefault();
      var userId = $(e.currentTarget).attr('rel');
      // Use custom modal for message.
      this.admin.modalConfirm('Êtes-vous sûr de vouloir supprimer ce compte ? ' +
        'Toutes les informations du profil seront perdues, ' +
        'et le membre n\'aura plus accès au site.', () => {
        window.location.replace('/admin/user/delete/' + userId);
      });
    });
  }
}
