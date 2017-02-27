class LgvAdminPageProfile extends LgvAdminPage {
  constructor(admin) {
    super(admin);

    // Click on edit profile button.
    let $toggle = $('.profileEditToSwitch');
    $('.profileEditSwitch').click(() => {
      $toggle.toggle();
    });
  }
}
