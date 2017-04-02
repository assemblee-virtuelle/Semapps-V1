class VirtualAssemblyFieldDbPedia {
  constructor(dom) {
    this.value = {};
    this.$ = $(dom);
    this.$selector = this.$.find('.tags-selector');
    this.$value = this.$.find('.tags-value');
    this.$tags = this.$.find('.tags');
    this.$selector.select2({
      width: '100%',
      placeholder: "Ajoutez un terme ici",
      ajax: {
        url: "http://lookup.dbpedia.org/api/search.asmx/PrefixSearch",
        dataType: 'json',
        delay: 250,
        data: (params) => {
          return {
            QueryString: params.term,
            MaxHits: 15
          };
        },
        processResults: (data) => {
          let items = [];
          this.selectValues = {};
          for (let result of data.results) {
            items.push({
              id: result.uri,
              text: result.label
            });
            this.selectValues[result.uri] = result.label;
          }
          return {
            results: items
          };
        },
        cache: true
      }
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
          this.getDbPediaLabel(uri, getLabelCallback);
        }
      });
    }
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

  getDbPediaLabel(uri, complete, lang = 'fr', propertyUri = 'http://www.w3.org/2000/01/rdf-schema#label') {
    let callbackLanguage = (values, acceptedLang) => {
      for (let data of values) {
        // Found in asked language.
        if (data.lang === acceptedLang) {
          return data.value;
        }
      }
    };
    $.ajax({
      headers: {
        // Ensure to get JSON response.
        'Accept': 'application/json',
        'Accept-Language': 'fr'
      },
      url: uri,
      complete: (r) => {
        "use strict";
        // Get all values.
        let values = r.responseJSON[uri][propertyUri];
        // Return asked language || english || first found.
        let value = callbackLanguage(values, lang) || callbackLanguage(values, 'en') || values[0].value;
        complete(value, uri);
      }
    });
  }
}


