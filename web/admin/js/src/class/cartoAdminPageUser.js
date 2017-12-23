class CartoAdminPageUser extends CartoAdminPage {
    init() {
        super.init();

        // On user profile, remove user button.
        $('.user-send-email').click((e) => {
            // Disable default click behavior.
            e.preventDefault();
            let userId = $(e.currentTarget).attr('rel');
            let nameRoute = $(e.currentTarget).attr('nameRoute');
            // Use custom modal for message.
            window.location.replace('/administration/user/'+userId+'/send/' +nameRoute);
        });


    }
}
