class LgvAdminPageTeam extends LgvAdminPage {
  constructor(admin) {
    super(admin);

    // On user profile, remove user button.
    $('.team-user-delete').click((e) => {
      // Disable default click behavior.
      e.preventDefault();
      let userId = $(e.currentTarget).attr('rel');
      // Use custom modal for message.
      this.admin.modalConfirm('Êtes-vous sûr de vouloir supprimer ce compte ? ' +
        'Toutes les informations du profil seront perdues, ' +
        'et le membre n\'aura plus accès au site.', () => {
        window.location.replace('/mon-compte/user/delete/' + userId);
      });
    });

    $(document.getElementById('teamManager')).find('tr').each(function () {
      let $this = $(this);
      let userId = $this.attr('rel');
      // Manage select changes.
      $this.find('select[name=accessLevel]').change(function () {
        // TODO Faire les contolleurs change-user-access/userId/accessLevel
        console.log(userId);
      });
    });
  }
}
