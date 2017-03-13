var resultsCount = 15;
var urlReqPrefix = "http://lookup.dbpedia.org/api/search.asmx/PrefixSearch?QueryClass=&MaxHits=" +
  resultsCount + "&QueryString=";

$(function () {
  var topics = [];
  $(".sf-standard-form").on('focus', '.hasLookup', function (event) {
    $(this).autocomplete({
      autoFocus: true,
      minlength: 3,
      search: function () {
        $(this).addClass('sf-suggestion-search')
      },
      open: function () {
        $(this).removeClass('sf-suggestion-search')
      },
      select: function (event, ui) {
        console.log("Topic chosen label event ");
        console.log($(this));
        console.log("Topic chosen label ui");
        console.log(ui);
        $emptyFields = $(this).siblings().filter(function (index) {
          return $(this).val() == ''
        }).length;
        console.log('Champs vides : ' + $emptyFields);

      },
      source: function (request, callback) {
        console.log("Déclenche l'événement :")
        console.log($(event.target));
        console.log(event.target.value);
        $.ajax({
          url: "http://lookup.dbpedia.org/api/search/PrefixSearch",
          data: {MaxHits: resultsCount, QueryString: request.term},
          dataType: "json",
          timeout: 5000
        }).done(function (response) {
          console.log(response)
          callback(response.results.map(function (m) {
            // topics[m.label] = m.uri;
            return {
              "label": m.label + " - " +
              cutStringAfterCharacter(m.description, '.'), "value": m.uri
            }
          }));
        }).fail(function (error) {
          $.ajax({
            url: "/lookup",
            data: {MaxHits: resultsCount, QueryString: request.term + "*"},
            dataType: "json",
            timeout: 5000
          }).done(function (response) {
            console.log('Done');
            var topics = [];
            callback(response.results.map(function (m) {
              // topics[m.label] = m.uri;
              return {
                "label": m.label /* + " - " +
                 cutStringAfterCharacter(m.description, '.') */, "value": m.uri
              }
            }))
          });
        })
      }
    })
  });
});

function cutStringAfterCharacter(s, c) {
  if (!(s === null)) {
    var n = s.indexOf(c);
    return s.substring(0, n != -1 ? n : s.length);
  } else {
    return s;
  }
};
