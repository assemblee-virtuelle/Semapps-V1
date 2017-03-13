class LgvAdminPage {
  constructor(admin) {
    this.admin = admin;

    // Need wikipedia.js and formInteraction.js
    if (window.cloneWidget) {
      // Manage widgets duplication.
      $(".form-group").on('click', '.add-widget', function () {
        cloneWidget($(this)
          .parent()
          .parent()
          .find('.sf-value-block')
          .first()
          .find('.ref')
          .first())
      });
    }
  }

  getParameterByName(name, url) {
    if (!url) {
      url = window.location.href;
    }
    name = name.replace(/[\[\]]/g, "\\$&");
    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
      results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, " "));
  }
}
