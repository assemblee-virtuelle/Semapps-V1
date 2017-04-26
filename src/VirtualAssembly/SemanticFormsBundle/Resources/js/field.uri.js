class VirtualAssemblyFieldUri {
  constructor(dom) {
    this.value = {};
    this.$ = $(dom);
    this.$selector = this.$.find('.tags-selector');
    this.$value = this.$.find('.tags-value');
    this.$tags = this.$.find('.tags');
    this.urlLookup = this.$.attr('data-sf-lookup');
    this.urlLabel = this.$.attr('data-sf-label');
    this.rdfType = this.$.attr('data-sf-rdfType');
    this.$selector.select2({
      width: '100%',
      placeholder: "Ajoutez un terme ici",
      ajax: {
        url: this.urlLookup,
        dataType: 'json',
        delay: 250,
        data: this.lookupParams.bind(this),
        processResults: this.lookupProcessResults.bind(this)
      },
      cache: true
    });
    this.$selector.on("select2:select", (e) => {
      this.setValue(this.$selector.val(), this.selectValues[this.$selector.val()]);
    });

    // Get value.
    let startupValue = this.$value.val();
    // Parse it.
    startupValue = startupValue && JSON.parse(startupValue);
    let getLabelCallback = (label, uri) => {
      this.setValue(uri, label)
    };
    // Load it.
    if (Array.isArray(startupValue)) {
      $.each(startupValue, (key, uri) => {
        // Avoid all kind of empty fields.
        if (uri) {
          this.getUriLabel(uri, getLabelCallback);
        }
      });
    }
  }

  lookupParams(params) {
    return {
      rdfType: this.rdfType,
      QueryString: params.term,
      MaxHits: 15
    };
  }

  /**
   * Expect to recieve an object with
   * uri => title couples.
   */
  lookupProcessResults(data) {
    let items = [];
    this.selectValues = data;
    // Transform data to select2 expected format.
    $.each(this.selectValues, (uri, title) => {
      items.push({
        id: uri,
        text: title
      })
    });
    return {
      results: items
    };
  }

  setValue(uri, text) {
    // Add to values.
    this.value[uri] = text;
    $.ajax({});
    // Reload list.
    this.fillValues();
  }

  fillValues() {
    this.$value.val(JSON.stringify(this.value));
    this.$tags.empty();
    $.each(this.value, (uri, text) => {
      // Build item.
      let $item = $('<span class="tag">' + text + ' <a href="#" class="remove-tag glyphicon glyphicon-remove"></a></span>')
      // Click event.
      $item.find('a.remove-tag').click((e) => {
        e.preventDefault();
        delete this.value[uri];
        this.fillValues();
      });
      // Append.
      this.$tags.append($item);
    });
    this.$tags.append('<div class="clearfix"></div>');
    // Show tags section if not empty.
    this.$tags.toggle(!!Object.keys(this.value).length);
    // Clear selector.
    this.$selector.val(null).trigger("change");
  }

  getUriLabel(uri, complete) {
    if (uri) {
      $.ajax({
        url: this.urlLabel,
        data: {
          uri: uri
        },
        complete: (r) => {
          this.setValue(uri, r.responseJSON.label);
        }
      });
    }
  }
}


