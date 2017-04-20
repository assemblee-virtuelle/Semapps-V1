Polymer({
  is: 'gv-detail-projet',
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
    $.extend(this, this.data.properties);
    // this.memberOf = this.data.memberOf;
    // this.topicInterest = this.data.topicInterest;
    // this.expertize = this.data.expertize;
    // this.knows = this.data.knows;
    // if (this.birthday) {
    //   let birthday = new Date(this.birthday);
    //   this.birthday = birthday.getDate() + '/' + (birthday.getMonth() + 1) + '/' + birthday.getFullYear();
    // }
      this.buildingTitle = gvc.buildings[this.data.properties.building].title;
      if(this.projectStart){
        let projectStart = new Date(this.projectStart);
        this.projectStart = projectStart.getDate()+ '/' + (projectStart.getMonth() + 1) + '/' + projectStart.getFullYear() + ' ' + projectStart.getHours() + ' H '+ projectStart.getMinutes() + ' min';
      }
  }
});
