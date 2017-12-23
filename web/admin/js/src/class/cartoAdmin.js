window.log = (m) => {
    "use strict";
    console.log(m);
};

class CartoAdmin {
    constructor() {
        // Wait document loaded.
        $(()=> {
            this.init();
        });
    }

    init() {
        this.$loadingPageSpin = $('#loadingPageSpin');
        // Save globally.
        window.lgvAdmin = this;

        // Define reused variables.
        this.$modalConfirm = $('#modalConfirm');
        this.$modalConfirmBody = this.$modalConfirm.find('.modal-body:first');
        this.$modalConfirmValidate = this.$modalConfirm.find('.btn-primary:first');
        // For register
        $("#register-form").steps({
            headerTag: "h3",
            bodyTag: "section",
            transitionEffect: "slideLeft",
            enableFinishButton: false,
            labels: {
                next: "Suivant",
                previous: "Précédent",
            }
        });
        $('.steps ul[role="tablist"]').hide();

        // Change image field.
        $('form').each((index, form) => {
            let $form = $(form);
            $form.find('div.formEditAvatar input[type="file"]').change(() => {
                // Display a nice spinner.
                this.pageLoadingStart();
                // Send form will reload the page.
                $form.submit();
            });
        });

        new CartoAdminPageTeam(this);
        new CartoAdminPageProfile(this);
        new CartoAdminPageOrga(this);
        new CartoAdminPageComponent(this);
        new CartoAdminPageUser(this);
        new CartoAdminPageComponentAddress(this);
    }

    modalConfirm(message, callback) {
        this.$modalConfirmBody.html(message);
        this.$modalConfirm.modal('show');
        this.$modalConfirmValidate.one('click', () => {
            this.$modalConfirm.modal('hide');
            callback();
        });
    }

    pageLoadingStart() {
        this.$loadingPageSpin
            .removeClass('fadeOut')
            .addClass('fadeIn');
    }

    pageLoadingStop() {
        this.$loadingPageSpin
            .removeClass('fadeIn')
            .addClass('fadeOut');
    }
}
