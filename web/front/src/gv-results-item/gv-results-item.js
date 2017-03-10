Polymer({
  is: 'gv-results-item',
  properties: {
    uri: String,
    label: String,
    description: String
  },
  handleClick(e) {
    e.preventDefault();
    gvc.mainComponent.set('route.path', 'detail');
    gvc.mainComponent.set('queryParams', {
      uri: window.encodeURIComponent(this.uri)
    });
  }
});
