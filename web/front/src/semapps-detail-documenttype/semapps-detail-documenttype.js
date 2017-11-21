Polymer({
    is: 'semapps-detail-documenttype',
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
        // Raw values.
        log("hello document")
        $.extend(this, this.data.properties);
        this.typeOf = this.data.typeOf;

    },

    onClickThematic(e){
        e.preventDefault();
        let searchThemeFilter = document.getElementById('searchThemeFilter');
        searchThemeFilter.value = e.target.rel;
        gvc.goSearch();
    }

});
