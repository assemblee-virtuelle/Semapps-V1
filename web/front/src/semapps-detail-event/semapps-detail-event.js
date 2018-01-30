Polymer({
    is: 'semapps-detail-event',
    properties: {},

    handleClickDetail(e) {
        e.preventDefault();
        semapps.goToPath('detail', {
            uri: window.encodeURIComponent(e.currentTarget.getAttribute('rel'))
        });
    },

    attached() {
        SemAppsCarto.ready(() => {
            semapps.initElementGlobals(this);
        });
        // Raw values.
        $.extend(this, this.data.properties);
        //this.representedBy = this.data.representedBy;
        this.hasInterest = this.data.hasInterest;
        this.organizedBy = this.data.organizedBy;
        this.hasParticipant = this.data.properties.hasParticipant;
        this.hasSubject = this.data.hasSubject;
        if (semapps.isMember()){
            this.addressTitle = this.address[0];
        }else{
            this.addressTitle = "";
            let addressSplit = this.address[0].split(" ");
            for (let i = addressSplit.length-1; i>=0 ; i--){
                this.addressTitle= addressSplit[i]+" "+this.addressTitle;
                if (isNaN(addressSplit[i]) ===false)
                    break;
            }
        }
        this.complementAddress = this.complementAddress[0];
        log(this.complementAddress);
        //this.buildingTitle = semapps.buildings[this.data.properties.building].title;
        if (this.startDate) {
            let startDate = new Date(this.startDate);
            this.startDate = startDate.getDate() + '/' + (startDate.getMonth() + 1) + '/' + startDate.getFullYear() + ' ' + startDate.getHours() + ' H ' + startDate.getMinutes() + ' min';
        }
        if (this.endDate) {
            let endDate = new Date(this.endDate);
            this.endDate = endDate.getDate() + '/' + (endDate.getMonth() + 1) + '/' + endDate.getFullYear() + ' ' + endDate.getHours() + ' H ' + endDate.getMinutes() + ' min';
        }

    },

    onClickThematic(e){
        e.preventDefault();
        let searchThemeFilter = document.getElementById('searchThemeFilter');
        searchThemeFilter.value = e.target.rel;
        semapps.goSearch();
    },
    handleClickRessource(e) {
        e.preventDefault();
        log('test');
        semapps.goToPath('ressource', {
            uri: window.encodeURIComponent(e.currentTarget.getAttribute('rel')),
            person: window.encodeURIComponent(this.uri)
        });
    },

});
