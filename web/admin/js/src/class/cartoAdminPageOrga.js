class CartoAdminPageOrga extends CartoAdminPage {
  init() {
    super.init();

    // On user profile, remove user button.
    $('.orga-delete').click((e) => {
      // Disable default click behavior.
      e.preventDefault();
      var userId = $(e.currentTarget).attr('rel');
      // Use custom modal for message.
      this.admin.modalConfirm('Êtes-vous sûr de vouloir supprimer cette organisation ? Tous les membres associés vont être supprimés.', () => {
        window.location.replace('/mon-compte/orga/delete/' + userId);
      });
    });

    // Change image field.
    let $form = $('#organisationPictureForm');
    $form.find('input[type="file"]').change(()=> {
        // Display a nice spinner.
        lgvAdmin.pageLoadingStart();
        // Send form will reload the page.
        $form.submit();
    });
  }
}
