//whenready
$(function() {

/**
 * Configures application.
 */
fileconfigure();

function fileconfigure()
{
    /*
     * for selecting the file
     */

    $("#file").typeahead({
        autoselect: true,
        highlight: true,
        minLength: 1
    },
    {
        source: filesearch,
        templates: {
            empty: "No such file",
            suggestion: _.template("<p><%- box %></p>")
        }
    });

    $("#file").on("typeahead:selected", function(eventObject, suggestion, name){
        var chosenfile = suggestion.box;
        $('#file').sendkeys(chosenfile);
    });
}
});
function filesearch(query, cb)
{
    // get places matching query (asynchronously)
    var parameters = {
        byt: query
    };
    $.getJSON("filesearch.php", parameters)
    .done(function(data, textStatus, jqXHR) {
        // call typeahead's callback with search results (i.e., places)
        cb(data);
    })
    .fail(function(jqXHR, textStatus, errorThrown) {
        // log error to browser's console
        console.log(errorThrown.toString());
    });
}
