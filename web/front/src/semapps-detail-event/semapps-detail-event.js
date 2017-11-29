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

        //this.buildingTitle = semapps.buildings[this.data.properties.building].title;
        if (this.startDate) {
            let startDate = new Date(this.startDate);
            this.startDate = startDate.getDate() + '/' + (startDate.getMonth() + 1) + '/' + startDate.getFullYear() + ' ' + startDate.getHours() + ' H ' + startDate.getMinutes() + ' min';
        }
        if (this.endDate) {
            let endDate = new Date(this.endDate);
            this.endDate = endDate.getDate() + '/' + (endDate.getMonth() + 1) + '/' + endDate.getFullYear() + ' ' + endDate.getHours() + ' H ' + endDate.getMinutes() + ' min';
        }
        log(this.data.localizedBy);

        if(this.data.localizedBy !== null ){
            log('hello !');
            log(this.data.localizedBy.address[0]);
            log(this.data.localizedBy.address[0].uri);
            log(this.data.localizedBy.address[0].name);
            log(this.data.localizedBy.address[0].latitude);
            log(this.data.localizedBy.address[0].longitude);
            let address = this.data.localizedBy.address[0];
            semapps.map.pinShowOne(address.latitude,address.longitude, address.uri, address.uri + ' -> '+ address.name);
        }

    },

    onClickThematic(e){
        e.preventDefault();
        let searchThemeFilter = document.getElementById('searchThemeFilter');
        searchThemeFilter.value = e.target.rel;
        semapps.goSearch();
    }

});
