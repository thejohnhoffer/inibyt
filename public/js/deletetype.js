//whenready
$(function() {

/**
 * Configures application.
 */
configure();

function configure()
{
    $("#link").typeahead({
        autoselect: true,
        highlight: true,
        minLength: 1
    },
    {
        source: search,
        templates: {
            empty: "No links like that... yet",
            suggestion: _.template("<p><%- source %> <%- type %>s <%- target %></p>")
        }
    });

    $("#link").on("typeahead:selected", function(eventObject, suggestion, name){
        var chosenlink = suggestion.source +" "+ suggestion.type + "s "+suggestion.target;
        $('#link').sendkeys(chosenlink);
    });

}

});

function search(query, cb)
{
    // get places matching query (asynchronously)
    var parameters = {
        byt: query
    };
    $.getJSON("search.php", parameters)
    .done(function(data, textStatus, jqXHR) {
          // call typeahead's callback with search results (i.e., places)
          cb(data);
    })
    .fail(function(jqXHR, textStatus, errorThrown) {
    });
}
