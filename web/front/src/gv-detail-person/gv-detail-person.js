Polymer({
  is: 'gv-detail-person',
  properties: {},

  attached() {
    GVCarto.ready(() => {
      gvc.initElementGlobals(this);
    });
    // Raw values.
    $.extend(this, this.data.properties);
    this.memberOf = this.data.memberOf;
    this.topicInterest = this.data.topicInterest;
    this.expertize = this.data.expertize;
    this.knows = this.data.knows;
    if (this.birthday) {
      let birthday = new Date(this.birthday);
      this.birthday = birthday.getDate() + '/' + (birthday.getMonth() + 1) + '/' + birthday.getFullYear();
    }
  }
});
