class LgvAdminPageProfile extends LgvAdminPage {
  constructor(admin) {
    super(admin);

    $('#profileEditButton').click(() => {
      $('#profileRead').toggle();
      $('#profileEdit').toggle();
    });
  }
}
