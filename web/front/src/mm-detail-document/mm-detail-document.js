Polymer({
    is: 'mm-detail-document',
    properties: {},

    handleClickDetail(e) {
        e.preventDefault();
        gvc.goToPath('detail', {
            uri: window.encodeURIComponent(e.currentTarget.getAttribute('rel'))
        });
    },

    attached() {
        GVCarto.ready(() => {
            gvc.initElementGlobals(this);
        });
        log("hello document")
        // Raw values.
        $.extend(this, this.data.properties);
        this.documents = this.data.documents;
        this.references = this.data.references;
        this.referencesBy = this.data.referencesBy;
        this.hasType = this.data.hasType;
        if (this.publicationDate) {
            let publicationDate = new Date(this.publicationDate);
            this.publicationDate = publicationDate.getDate() + '/' + (publicationDate.getMonth() + 1) + '/' + publicationDate.getFullYear();
        }
        //this.buildingTitle = gvc.buildings[this.data.properties.building].title;
    },

    onClickThematic(e){
        e.preventDefault();
        let searchThemeFilter = document.getElementById('searchThemeFilter');
        searchThemeFilter.value = e.target.rel;
        gvc.goSearch();
    }

});
