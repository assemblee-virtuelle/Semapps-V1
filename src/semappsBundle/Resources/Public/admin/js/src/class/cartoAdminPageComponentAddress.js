class CartoAdminPageComponentAddress extends CartoAdminPage {

    constructor() {
        super();
        this.currentText ="";
    }
    init() {

        super.init();
        // On user profile, remove user button.
        $('.address-elem').change((e) => {
            // Disable default click behavior.
            e.preventDefault();
            let textelem = $(e.currentTarget).find('.tag').first().text();
            if(this.currentText !== textelem){
                this.currentText = textelem;
                let address_latitude= $(e.currentTarget).parent().find('#address_latitude');
                let address_longitude = $(e.currentTarget).parent().find('#address_longitude');

                if(this.currentText.replace(/\s/g,'') !== "" ){
                    $.ajax({
                        url: 'https://api-adresse.data.gouv.fr/search/',
                        data: {
                            q: this.currentText
                        },
                        complete: (r) => {
                            let array =  r.responseJSON.features;
                            if (array.length > 0){
                                let result = array[0];
                                log(result);
                                log(result.geometry.coordinates[0]);
                                log(result.geometry.coordinates[1]);
                                let longitude = result.geometry.coordinates[0];
                                let latitude = result.geometry.coordinates[1];
                                address_latitude.attr('value',latitude);
                                address_longitude.attr('value',longitude);
                            }

                        }
                    });
                }
                else{
                    address_latitude.attr('value','');
                    address_longitude.attr('value','');
                }

            }

        });

    }
}
