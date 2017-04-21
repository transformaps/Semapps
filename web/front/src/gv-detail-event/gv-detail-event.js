Polymer({
  is: 'gv-detail-event',
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

    // this.expertize = this.data.expertize;
    // this.knows = this.data.knows;
    // if (this.birthday) {
    //   let birthday = new Date(this.birthday);
    //   this.birthday = birthday.getDate() + '/' + (birthday.getMonth() + 1) + '/' + birthday.getFullYear();
    // }
      this.topicInterest = this.data.topicInterest;
      this.buildingTitle = gvc.buildings[this.data.properties.building].title;
      if(this.eventBegin){
        let eventBegin = new Date(this.eventBegin);
        this.eventBegin = eventBegin.getDate()+ '/' + (eventBegin.getMonth() + 1) + '/' + eventBegin.getFullYear() + ' ' + eventBegin.getHours() + ' H '+ eventBegin.getMinutes() + ' min';
      }
      if(this.eventEnd){
          let eventEnd = new Date(this.eventEnd);
          this.eventEnd = eventEnd.getDate()+ '/' + (eventEnd.getMonth() + 1) + '/' + eventEnd.getFullYear() + ' ' + eventEnd.getHours() + ' H '+ eventEnd.getMinutes() + ' min';
      }
  }
});