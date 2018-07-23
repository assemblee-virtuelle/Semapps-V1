class CartoAdminPageProfile extends CartoAdminPage {
    init() {
        super.init();
        // On user profile, remove user button.
        $('.user-remove-profile').click((e) => {
            // Disable default click behavior.
            e.preventDefault();
            let route = $(e.currentTarget).attr('rel');
            // Use custom modal for message.
            this.admin.modalConfirm('Êtes-vous sûr de vouloir supprimer ce profil ? ' +
                'Toutes les informations du profil seront supprimées. '
                , () => {
                window.location.replace(route);
            });
        });
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
