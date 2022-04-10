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

    $("#groupfile").typeahead({
        autoselect: true,
        highlight: true,
        minLength: 1
    },
    {
        source: filesearch,
        templates: {
            empty: "No such group",
            suggestion: _.template("<p><%- box %></p>")
        }
    });

    $("#groupfile").on("typeahead:selected", function(eventObject, suggestion, name){
        var chosengroup = suggestion.box;

        var chosendata = [];

        // make sure the data is simplified

        if (!document.getElementById("abstract").checked){
          $("#abstract").trigger( "click" );
        }

        globaldata.forEach(function(gd){

          if (gd.target.name.indexOf(chosengroup+"bqxgp") != -1){
            chosendata.push({"target":gd.target.name, "source":gd.source.name, "weight":gd.weight, "type":gd.type});
          }
          if (gd.source.name.indexOf(chosengroup+"bqxgp") != -1){
            chosendata.push({"target":gd.target.name, "source":gd.source.name, "weight":gd.weight, "type":gd.type});
          }
        });

        chosendata = (JSON.stringify(chosendata));

        // send json data to share.php
        $('#groupfile').sendkeys(chosengroup +" goes online             "+chosendata);
    });
}
});
function filesearch(query, cb)
{
    var parameters = {
        byt: query
    };

    // make sure the data is simplified
    if (document.getElementById("abstract").checked){
      $("#abstract").trigger( "click" );
    }

    datagroups = globalgroups;

    query = query.toUpperCase();

    var dataarray = [];
    datagroups.forEach(function(dg){
      // if the query is contained within the data
      if (dg.indexOf(query) != -1) {
        dataarray.push(dg);
      }
    });

    // make sure the data isn't redundant
    dataarray = _.unique(dataarray);

    // convert the data into an object
    var data = [];
    dataarray.forEach(function(da){
      data.push({"box": da});
    })

    cb(data);

}
