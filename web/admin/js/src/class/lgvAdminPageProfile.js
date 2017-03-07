class LgvAdminPageProfile extends LgvAdminPage {
  constructor(admin) {
    super(admin);

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
    let $form = $('#profilePicture');
    $form.find('input[type="file"]').change(()=> {
      $form.find('form').submit();
    });
  }
}
