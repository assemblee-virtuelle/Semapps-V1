class VirtualAssemblyFieldDbPedia extends VirtualAssemblyFieldUri {
  lookupProcessResults(data) {
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
  }

  getUriLabel(uri, complete, lang = 'fr', propertyUri = 'http://www.w3.org/2000/01/rdf-schema#label') {
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
        // Avoid errors.
        if (r.responseJSON) {
          // Get all values.
          let values = r.responseJSON[uri][propertyUri];
          // Return asked language || english || first found.
          let value = callbackLanguage(values, lang) || callbackLanguage(values, 'en') || values[0].value;
          complete(value, uri);
        }
      }
    });
  }
}


