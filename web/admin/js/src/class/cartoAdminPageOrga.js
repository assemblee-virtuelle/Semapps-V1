class CartoAdminPageOrga extends CartoAdminPage {
    init() {
        super.init();

        $('.orga-remove-profile').click((e) => {
            // Disable default click behavior.
            e.preventDefault();
            let route = $(e.currentTarget).attr('rel');
            let uri = $(e.currentTarget).attr('data-uri');
            // Use custom modal for message.
            this.admin.modalConfirm('Êtes-vous sûr de vouloir supprimer ce profil ? ' +
                'Toutes les informations du profil seront supprimées. '
                , () => {
                    window.location.replace(route+'?uri='+uri);
                });
        });
        // On user profile, remove user button.
        $('.orga-delete').click((e) => {
            // Disable default click behavior.
            e.preventDefault();
            var orgaId = $(e.currentTarget).attr('rel');
            // Use custom modal for message.
            this.admin.modalConfirm('Êtes-vous sûr de vouloir supprimer cette organisation ? Toutes les informations du profil seront supprimées.', () => {
                window.location.replace('/administration/organization/delete/'+encodeURI(orgaId));
            });
        });
        // Change image field.
        let $form = $('#organisationPictureForm');
        $form.find('input[type="file"]').change(()=> {
            // Display a nice spinner.
            lgvAdmin.pageLoadingStart();
            // Send form will reload the page.
            $form.submit();
        });
        $('.lookupAdressse input').autocomplete({
            source : function(requete, reponse){ // les deux arguments représentent les données nécessaires au plugin

                $.ajax({

                    url : 'http://api-adresse.data.gouv.fr/search/', // on appelle le script JSON
                    data: 'q=' + $('.lookupAdressse input').val(),

                    //dataType : 'json', // on spécifie bien que le type de données est en JSON

                    success : function(donnee){
                        reponse($.map(donnee.features, function(objet){
                            log(objet.properties.label)
                            return objet.properties.label; // on retourne cette forme de suggestion

                        }));

                    },
                });

            }
        });
    }
}
