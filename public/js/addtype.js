//whenready
$(function() {

/**
 * Configures application.
 */
sourceconfigure();

function sourceconfigure()
{
    /*
     * for selecting the source
     */

    $("#newsource").typeahead({
        autoselect: true,
        highlight: true,
        minLength: 1
    },
    {
        source: sourcesearch,
        templates: {
            empty: "New Node?",
            suggestion: _.template("<p><%- source %> <%- type %>s <%- target %> <%- weight %> </p>")
        }
    });

    $("#newsource").on("typeahead:selected", function(eventObject, suggestion, name){
        var chosensource = suggestion.source.replace("(new)","") +" "+ suggestion.type +
        "s " + suggestion.target.replace("(new)","") + suggestion.weight;
        $('#newsource').sendkeys(chosensource);
    });
}
});
function sourcesearch(query, cb)
{
    // get places matching query (asynchronously)
    var parameters = {
        byt: query
    };
    $.getJSON("newsearch.php", parameters)
    .done(function(data, textStatus, jqXHR) {

        // call typeahead's callback with search results (i.e., places)
        cb(data);
    })
    .fail(function(jqXHR, textStatus, errorThrown) {

        // log error to browser's console
        console.log(errorThrown.toString());
    });
}
