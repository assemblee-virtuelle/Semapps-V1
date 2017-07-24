class CartoAdminPageProfile extends CartoAdminPage {
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
  }
}
