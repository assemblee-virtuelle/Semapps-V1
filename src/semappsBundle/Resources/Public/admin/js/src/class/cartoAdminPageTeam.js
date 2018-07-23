class CartoAdminPageTeam extends CartoAdminPage {
    init() {
        super.init();

        // On user profile, remove user button.
        $('.team-user-delete').click((e) => {
            // Disable default click behavior.
            e.preventDefault();
            let userId = $(e.currentTarget).attr('rel');
            // Use custom modal for message.
            this.admin.modalConfirm('Êtes-vous sûr de vouloir supprimer ce compte ? ' +
                'Toutes les informations du profil seront perdues, ' +
                'et le membre n\'aura plus accès au site.', () => {
                window.location.replace('/administration/user/'+ userId+'/delete' );
            });
        });

        $(document.getElementById('teamManager')).find('tr').each(function () {
            let $this = $(this);
            let userId = $this.attr('rel');

            // Manage select changes.
            $this.find('select[name=accessLevel]').change(function () {
                let roles = $(this).val();
                window.location.replace('/administration/access/'+ userId +'/change/'+ roles);
            });
        });

        $('.invite-delete').click((e) => {
            // Disable default click behavior.
            e.preventDefault();
            let email = $(e.currentTarget).attr('rel');
            // Use custom modal for message.
            this.admin.modalConfirm('Êtes-vous sûr de vouloir supprimer cette invitation ? ', () => {
                window.location.replace('/administration/invite/delete/'+ email );
            });
        });
        $('.invite-send').click((e) => {
            // Disable default click behavior.
            e.preventDefault();
            let email = $(e.currentTarget).attr('rel');
            let token = $(e.currentTarget).attr('data-token');
            // Use custom modal for message.
            window.location.replace('/administration/invite/send/'+email+'/'+token);
        });
    }
}
