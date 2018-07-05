Polymer({
    is: 'semapps-tag',
    properties: {
        uri: String,
        name: String,
        route: String,
        type: String,
        color: String,
    },

    attached() {
        SemAppsCarto.ready(() => {
            semapps.initElementGlobals(this);
        });
        //log(this.route)
        switch(this.type) {
            case 'person':
            case 'persontype':
                this.color = semapps.entities["http://virtual-assembly.org/pair#Person"].markerColor;
                break;
            case 'organization':
            case 'organizationtype':
                this.color = semapps.entities["http://virtual-assembly.org/pair#Organization"].markerColor;
                break;
            case 'event':
            case 'eventtype':
                this.color = semapps.entities["http://virtual-assembly.org/pair#Event"].markerColor;
                break;
            case 'project':
            case 'projecttype':
                this.color = semapps.entities["http://virtual-assembly.org/pair#Project"].markerColor;
                break;
            case 'proposal':
            case 'proposaltype':
                this.color = semapps.entities["http://virtual-assembly.org/pair#Proposal"].markerColor;
                break;
            case 'document':
            case 'documenttype':
                this.color = semapps.entities["http://virtual-assembly.org/pair#Document"].markerColor;
                break;
            default:
                this.color = '#01acdd';
        }
        this.isDetail = (this.route === "handleClickDetail");
        this.isThematic = (this.route === "onClickThematic");
        this.isRessource = (this.route === "handleClickRessource");
         // this.cutlabel =(this.label.length > 15)? semapps.capitalize(this.label,true).substr(0,10)+'...' : semapps.capitalize(this.label,true);
    },

    handleClickDetail(e) {
        e.preventDefault();
        semapps.goToPath('detail', {
            uri: window.encodeURIComponent(e.currentTarget.getAttribute('rel'))
        });
    },
    onClickThematic(e){
        e.preventDefault();
        let searchThemeFilter = document.getElementById('searchThemeFilter');
        searchThemeFilter.value = e.target.rel;
        //searchThemeFilter._activeChanged();
        semapps.goSearch();

    },
    handleClickRessource(e) {
        e.preventDefault();
        semapps.goToPath('ressource', {
            uri: window.encodeURIComponent(e.currentTarget.getAttribute('rel')),
            person: window.encodeURIComponent(this.uri)
        });
    },
});

