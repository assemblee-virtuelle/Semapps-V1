class LgvAdminPageComponent extends LgvAdminPage {
  init() {
    super.init();

    // On user profile, remove user button.
    $('.component-delete').click((e) => {
      // Disable default click behavior.
      e.preventDefault();
      let uri = $(e.currentTarget).attr('rel');
      let nameComponent = $(e.currentTarget).attr('name');
      // Use custom modal for message.
      this.admin.modalConfirm('Êtes-vous sûr de vouloir supprimer ce '+nameComponent+' ? ' +
        'Toutes les informations seront perdues. ', () => {
        window.location.replace('/mon-compte/component/delete?uri=' + uri+'&componentName='+nameComponent);
      });
    });
    $('.component-new-picture').change((e) => {
      e.preventDefault();
      log($(e.currentTarget).val());
      $("img[id='componentPicture']").attr('src',$(e.currentTarget).val());
    });
      $("a[id='componentPicture']").mouseover((e) => {
          $('#componentImage').attr('class', 'form-group col-xs-12 has-success');
      })
      .mouseout((e) => {
          $('#componentImage').attr('class', 'form-group col-xs-12');
      })
  }
}
