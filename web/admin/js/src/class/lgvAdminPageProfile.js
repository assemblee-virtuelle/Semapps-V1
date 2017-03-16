class LgvAdminPageProfile extends LgvAdminPage {
  init() {
    super.init();

    // Click on edit profile button.
    let $toggle = $('.profileEditToSwitch');
    $('.profileEditSwitch').click(() => {
      $toggle.toggle();
    });

    // Display form if asked.
    if (this.getParameterByName('edit')) {
      $toggle.toggle();
    }

    // Change image field.
    let $form = $('#profilePictureForm');
    $form.find('input[type="file"]').change(()=> {
      // Display a nice spinner.
      lgvAdmin.pageLoadingStart();
      // Send form will reload the page.
      $form.submit();
    });
  }
}
