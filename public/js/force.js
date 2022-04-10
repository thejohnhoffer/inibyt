/*
 * Inibyt:
 * A beuatiful simulation
 * Of the magical wonders of
 * Inhibtory and Excitatory Networks
 *
 * John T. Hoffer
 * CS50 Final Project 2014
 * hoffer@college.harvard.edu
 */
var simpledata = [];
var globaldata = [];
var globalgroups = [];
//dock ready
$(function(){
// initialize tick count, data, nodes, pan, and zoom
var t = 0,
    data = [],
    nodes = {},
    datanodes = [],
    translation = [0,0],
    scaleFactor = 1,
    speed = 100,
    timemoving= true;

var selected_node = null;

function getTopBuffer() {
  var b1 = document.getElementById("top").offsetHeight;
  var b2 = document.getElementById("nav").offsetHeight;
  return b1 + b2;
}

// get the data from yourdata.php
$.getJSON("feedin.php", function (data) {

simpledata = data;
if (data[0] != null) {
  // set size of the entire layout
  var width = window.innerWidth;
  var height = window.innerHeight + getTopBuffer() / 2;

  // for each link
  data.forEach(function(link) {
    var newdatanode = true;
    // make sure the source has not been added
    datanodes.forEach(function(datanode) {
      if (datanode == link.source) {
        newdatanode = false;
      }
    });
    // if not, add the source to the array of datanodes
    if (newdatanode) {
      datanodes.push(link.source);
    }
    newdatanode = true;
    // make sure the target has not been added
    datanodes.forEach(function(datanode) {
      if (datanode == link.target) {
        newdatanode = false;
      }
    });
    // if not, add the target to the array of datanodes
    if (newdatanode) {
      datanodes.push(link.target);
    }
  });

  var tobegrouped = [];
  // for each datanode
  for (var key in datanodes) {
    // if the node needs to be grouped
    if (datanodes[key].indexOf("bqxgp") == -1) {
      // add the node to the list to be grouped
      tobegrouped.push({"tobe": datanodes[key], "grouped": []});
      // remember the index of this recently added node
      var nind = tobegrouped.length -1;
      // For each link
      data.forEach(function(link) {
        // if the node to be grouped is the source of the link
        if (tobegrouped[nind].tobe == link.source) {
          // if the target of the link also needs to be grouped
          if (link.target.indexOf("bqxgp") == -1) {
            // add each of the links to the node to be grouped
            tobegrouped[nind].grouped.push(link.target);
          }
        }
      });
      // For each link
      data.forEach(function(link) {
        // if the node to be grouped is the target of the link
        if (tobegrouped[nind].tobe == link.target) {
          if (link.source.indexOf("bqxgp") == -1) {
            // add each of the links to the node to be grouped
            tobegrouped[nind].grouped.push(link.source);
          }
        }
      });
    }
  }

  // if there are any nodes that need to be grouped
  if (tobegrouped[0] != null) {

    // make each to be grouped array of nodes to be unique
    for (var tbg in tobegrouped) {
      tobegrouped[tbg].grouped = _.uniq(tobegrouped[tbg].grouped);
    }
    // sort each node descending by number of connections
    tobegrouped = tobegrouped.sort(function(a, b) {
      return -1*(a.grouped.length-b.grouped.length);
    });

    if (tobegrouped[0].grouped.length > 1) {
      // for each link
      data.forEach(function(link) {
        tobegrouped[0].grouped.forEach(function(con) {
          // if the source is connected to the node with the most connections
          if (link.source == con) {
            // make the source a member of an artifical group
            link.source = tobegrouped[0].tobe + "bqxgp" + con;
          }
          // if the target is connected to the node with the most connections
          if (link.target == con) {
            // make the target a member of an artifical group
            link.target = tobegrouped[0].tobe + "bqxgp" + con;
          }
        });
      });
      // for each link
      data.forEach(function(link) {
        // if the source is the node with the most connections
        if (link.source == tobegrouped[0].tobe) {
          // make the source a member of an artifical group
          link.source = tobegrouped[0].tobe + "bqxgp" + tobegrouped[0].tobe;
        }
        // if the target is the node with the most connections
        if (link.target == tobegrouped[0].tobe) {
          // make the target a member of an artifical group
          link.target = tobegrouped[0].tobe + "bqxgp" + tobegrouped[0].tobe;
        }
      });
    }
  }


    // Create nodes from links
    data.forEach(function(link) {
      // if the source node is already defined
      link.source = nodes[link.source] ||
      // if the node isn't yet defined
      (nodes[link.source] = {name: link.source,
         thresh: 100,
         state:"inactive",
         value:0});
      // if the target node is already defined
      link.target = nodes[link.target] ||
      // if the node isn't yet defined
      (nodes[link.target] = {name: link.target,
        thresh: 100,
        state:"inactive",
        value:0});
      });

  globaldata = data;
  // initiates the the force diagram
  var force = d3.layout.force()
      // defines the nodes
      .nodes(d3.values(nodes))
      // defines the links
      .links(data)
      // some properties
      .size([width, height])
      .linkDistance(100)
      .charge(-1000)
      // starts the clock
      .on("tick", tick)
      .start();

  /*** Configure zoom behaviour ***/
  var zoomer = d3.behavior.zoom()
      //allow 100 times zoom in or out
      .scaleExtent([0.1,10])
      .on("zoom", zoom);

  //define the event handler function
  function zoom() {
      scaleFactor = d3.event.scale;
      translation = d3.event.translate;
      tick(); //update position
  }

  // Create an svg division in html
  var svg = d3.select("#viz").append("svg")
      .attr("width", width)
      .attr("height", height)
      .attr("id", "forcevisual")
      // including some event watchers
      .on("mousemove", mousemove)
      .on("mousedown", mousedown)
      .call(zoomer)
      .on("dblclick.zoom", null);

  // create a circle for the cursor
  var cursor = svg.append("circle")
      .attr("r", 25)
      .attr("transform", "translate(-100,-100)")
      .attr("class", "cursor");

  // create a space for detecting zoom
  var rect = svg.append("rect")
      .attr("width", width)
      .attr("height", height)
      .style("fill", "none")
      .style("pointer-events", "all");

  // define the arrows as markers
  var arrow = svg.append("defs").selectAll("marker")
      // for an array of two values
      .data(["excite", "inhibit", "excyte", "inhibyt"])
      // define a marker for both values
      .enter().append("marker")
        // with an id equal to that value
        .attr("id", function(d) { return d; })
        // prepare to define arrowheads
        .attr("viewBox", "0 -6 10 10")
        .attr("refX", 15)
        .attr("refY", -1.5)
        // width and height of arrowhead
        .attr("markerWidth", 10)
        .attr("markerHeight", 10)
        .attr("orient", "auto")
      // draw arrowheads
      .append("path")
        .attr("d", function(d) {
          // pointy arrow
          if (d == "excite")
            return "M0,-5L10,-1L0,3";
          // flat arrow
          else if (d == "inhibit")
            return "M6,-6L10,-6L10,6L6,4";
          // abstract arrows
          else if (d == "excyte")
            return "M3,-5L10,-1L3,3";
          else
            return "M0,-5L3,-5L3,3L0,3";
          });

  // create an empty space for black box paths
  var boxpath = svg.append("g").attr("id","boxpath").selectAll("path");

  // create a division with an array of paths
  var path = svg.append("g").attr("id","paths").selectAll("path")
      // for all links of the force diagram
      .data(force.links())
      // define a path for each link
    .enter().append("path")
      // with a class given by its type
      .attr("class", function(d) { return "link " + d.type; })
      // with an marker given by its type
      .attr("marker-end", function(d) { return "url(#" + d.type + ")"; })
      // with a specified source
      .attr("source", function(d) { return d.source.name; })
      // and a specified target
      .attr("target", function(d) { return d.target.name; })
      .style("opacity", function(d) {return d.weight / 100});

  // create a division with an array of circles
  var circle = svg.append("g").attr("id","nodes").selectAll("circle")
      // for all nodes of the force diagram
      .data(force.nodes())
      // define a circle for each node
    .enter().append("circle")
      // unique id
      .attr("id", function(d) { return "cir"+d.name; })
      // radius 10
      .attr("r", 12);

  // create labels for each circle
  var text = svg.append("g").attr("id","nodetext").selectAll("text")
      .data(force.nodes())
    .enter().append("text")
      .attr("class", "label")
      .attr("x", -5)
      .attr("y", ".31em")
      .text(function(d) {return d.name.replace(/(.*)bqxgp/g,'');})
      // fixable
      .on("click", dblclick);

  // create an empty space for black boxes
  var box = svg.append("g").attr("id","box").selectAll("rect");

  // create an empty space for black box text
  var boxtext = svg.append("g").attr("id","boxtext").selectAll("text");

  // listens for any changes in the sim checkbox
  $("#sim").click(function() {
      var chance = 0.4;
      // global clock flag
      if (timemoving == false) {
        force.nodes().forEach(function (fn) {
        chance = document.getElementById("fixins").value;
          if (Math.random() < 0.5 - 0.4*chance){
            fn.fixed = true;
          }
          else {
            fn.fixed = false;
          }
        });
      }
      // set the speed of pulses // higher is slower...
      speed = 100 * (1-chance);
      // start the clock
      tick();
  });

  // listen for fixed slider changes
  $("#fixins").click(function(){
    // send the new value to index.php
    $.post( "index.php", { fixed: this.value} );
  });

  // listens for abstraction box changes
  $("#abstract").click(function() {
    // send the new value to index.php
    $.post( "index.php", { checked: this.checked} );
    upslide();
  });
  upslide();

  function upslide() {
    // if the checkbox is checked
    if (document.getElementById("abstract").checked) {
      // remove the black box and all its goodies
      box = d3.select("#box").selectAll("rect").remove();
      boxtext = d3.select("#boxtext").selectAll("text").remove();
      boxpath = d3.select("#boxpath").selectAll("path").remove();

      // for every possible link
      for (var k = 0; k < path[0].length; k++) {
            // make the link visible again
            path[0][k].style.visibility = "visible"
      }

      // search through all possible nodes
      for (var j = 0; j < circle[0].length; j++) {
          // make the node visible again
          circle[0][j].style.visibility = "visible";
          // make the text visible again
          text[0][j].style.visibility = "visible";
      }
    }
    // if checkbox isn't checked
    else {
      var groupo = [],
          groupi = [],
          groups = [],
          newlinks = [],
          middlement = [],
          nodesbetween = [],
          nodeswithin = [],
          boxlinks = [],
          iopaths = [];
      // for every possible link
      for (var key in data) {
        // process the link's data
        var sname = data[key].source.name,
        tname = data[key].target.name,
        type = data[key].type,
        sgroup = "sempty",
        tgroup = "tempty";

        // parse out the name of the source group
        if (sname.indexOf("bqxgp") > 0) {
          sgroup = sname.substring(0, sname.indexOf("bqxgp"));
        }
        // parse out the name of the target group
        if (tname.indexOf("bqxgp") > 0) {
          tgroup = tname.substring(0, tname.indexOf("bqxgp"));
        }

        // if either source or target is in a group
        if (sgroup != "sempty" || tgroup != "tempty" ) {
          // if only the target is in a group
          if (sgroup == "sempty") {
            // add to the list of possible inputs to target group
            groupi.push({"node": sname, "group": tgroup, "type": type, "to": [tname], "valid": false});
            // add to the list of possible connections to different groups
            iopaths.push(path[0][key]);
          }
          // if only the source is in a group
          else if (tgroup == "tempty") {
            // add to the list of possible outputs from source group
            groupo.push({"node": tname, "group": sgroup, "type": type, "fro": [sname], "valid": false});
            // add to the list of possible connections to different groups
            iopaths.push(path[0][key]);
          }
          // if both source and target are in a group
          else {
            // if the source and target are in the same group
            if (sgroup == tgroup) {
              // update input links
              var inputcheck = [];
              inputcheck = $.grep(groupi, function(i){
                // if the same input already exists
                if (i.node == sname && i.group == tgroup){
                  // add to the list of targets
                  i.to.push(tname);
                  // if the type of link is different
                  if (i.type != type) {
                    i.type = "both";
                  }
                  return i;
                }
              });
              // update output links
              var outputcheck = [];
              outputcheck = $.grep(groupo, function(o){
                // if the same output already exists
                if (o.node == tname && o.group == sgroup){
                  // add to the list of sources
                  o.fro.push(sname);
                  // if the type of link is different
                  if (o.type != type) {
                    o.type = "both";
                  }
                  return o;
                }
              });
              if (inputcheck[0] == null){
                // add to the list of possible inputs to target group
                groupi.push({"node": sname, "ngroup": sgroup, "type": type,
                            "to": [tname], "group": tgroup, "valid": false});
              }
              if (outputcheck[0] == null){
                // add to the list of possible outputs from source group
                groupo.push({"node": tname, "ngroup": tgroup, "type": type,
                            "fro": [sname], "group": sgroup, "valid": false});
              }
            }
            // if the source and target are in different groups
            else {
              // update link between these groups
              var boxcheck = [];
              boxcheck = $.grep(boxlinks, function (b){
                // if the same connection occurs forwards
                if (b.sgp == sgroup && b.tgp == tgroup) {
                  // add to the list of sources
                  b.snodes.push(sname);
                  // add to the list of targets
                  b.tnodes.push(tname);
                  var setlinkand = b.type.indexOf("and");
                  // if the set link has bidirectionality
                  if (setlinkand != -1){
                    var setforward = b.type.substring(0, setlinkand),
                    setbackward = b.type.substring(setlinkand+1);
                    // if the new link is different from the set forward link
                    if (setforward != type){
                      // update the set forward link to signify both
                      b.type = "bothand" + setbackward;
                    }
                  }
                  // if the set link is unidirectional
                  else {
                    // if the new link is different from the set link
                    if (b.type != type) {
                      // update the set link to signify both
                      b.type = "both"
                    }
                  }
                  // always
                  return b;
                }
                // if the same connection occurs backwards
                else if (b.tgp == sgroup && b.sgp == tgroup) {
                  // add to the list of sources
                  b.snodes.push(tname);
                  // add to the list of targets
                  b.tnodes.push(sname);
                  var setlinkand = b.type.indexOf("and");
                  // if the set link has bidirectionality
                  if (setlinkand != -1){
                    // forward and backward are relative to the set link
                    var setforward = b.type.substring(0, setlinkand),
                    setbackward = b.type.substring(setlinkand+1);
                    // if the new link is diffrent from the set backward link
                    if (setbackward != type) {
                      // update the set backward link to signify both
                      b.type = setforward +"andboth";
                    }
                  }
                  // if the set link is unidirectional
                  else {
                    // udate the set link to signify bidirectionaly
                    b.type = b.type + "and" + type;
                  }
                  // always
                  return b;
                }
                // otherwise, return nothing.
              });
              if (boxcheck[0] == null) {
                // add to the list of links between different group boxes
                boxlinks.push({"sgp": sgroup, "tgp": tgroup, "type": type,
                              "snodes": [sname], "tnodes": [tname], "valid": false});
              }
              // add to the list of possible connections to different groups
              iopaths.push(path[0][key]);
            }
          }
          // as long as groups are involved, hide the links from the graph
          path[0][key].style.visibility = "hidden";
          // as long as groups are involved, remember the groups
          if (sgroup != "sempty") {
            groups.push(sgroup);
          }
          if (tgroup != "tempty") {
            groups.push(tgroup);
          }
        }
        // even if groups aren't involved, hide the links from the graph
        path[0][key].style.visibility = "hidden";
        // add to the list of possible connections to different groups
        iopaths.push(path[0][key]);
      }

      // make a list of unique groups
      groups = $.unique(groups);
      globalgroups = groups;

      // find nodes that are inputs and outputs for the same groups
      middlemen = iointersect(groupi,groupo);
      nodeswithin = _.pluck(middlemen, 'node');

      // find nodes that connect different groups
      nodesbetween = _.pluck(gconnect(groupi,groupo), 'node');

      // remove conencting nodes from internal nodes
      nodeswithin = _.difference(nodeswithin, nodesbetween);

      var lostsouls = [];
      // for all nodes that are within groups
      for (var win = 0; win < nodeswithin.length; win++){
        // if the node has no groupname
        if (nodeswithin[win].indexOf('bqxgp') == -1) {
          // find all of its links in the data
          lostsouls.push(nodeswithin[win]);
        }
        groupi = _.reject(groupi, function(i){
          // remove all inputs that actually come from a node within
          return i.node == nodeswithin[win];
        });
        groupo = _.reject(groupo, function(o){
          // remove all outputs that actually go to a node within
          return o.node == nodeswithin[win];
        });
      }

      // make the list of absorbed nodes unique
      lostsouls = _.uniq(lostsouls);

      var groupguess = [];
      // for each absorbed node
      lostsouls.forEach(function(ls){
        // for each link in the data
        data.forEach(function(datum){
          // if the target is an absorbed node
          if (datum.target.name == ls) {
            var dotguess = datum.source.name.indexOf("bqxgp");
            if ( dotguess != -1) {
              groupguess.push(datum.source.name.substring(0,dotguess) + "bqxgp" + ls);
            }
          }
          // if the source is an abosrbed node
          if (datum.source.name == ls) {
            var dotguess = datum.target.name.indexOf("bqxgp");
            if ( dotguess != -1) {
              groupguess.push(datum.target.name.substring(0,dotguess) + "bqxgp" + ls);
            }
          }
        });
      });

      // make sure there are unique guesses for each lost souls' group
      groupguess = _.unique(groupguess);

      var groupmath = [];
      groupguess.forEach(function(gg){
        var lostnode = gg.substring(gg.indexOf('bqxgp')+5);
        var guessing = gg.substring(0, gg.indexOf('bqxgp'));
        // check if the lost node already has a guess row added for it
        var guesscheck = [];
        guesscheck = $.grep(groupmath, function (gm){
          if (gm.ls == lostnode) {
            gm.guess.push(guessing);
            return gm;
          }
        });
        if (guesscheck[0] == null) {
          groupmath.push({"guess": [guessing], "ls": lostnode})
        }
      });


      // take the most common guess
      groupmath.forEach(function(gm){
        // find the most common element out of all the guesses
        gm.guess = _.chain(gm.guess).countBy().pairs().max(_.last).head().value();
      });

      // for each absorbed node
      lostsouls.forEach(function(ls){
        // for each link in the data
        data.forEach(function(datum){
          // process the link's data
          var sname = datum.source.name,
          tname = datum.target.name,
          type = datum.type,
          sgroup = "sempty",
          tgroup = "tempty";

          // parse out the name of the source group
          if (sname.indexOf("bqxgp") > 0) {
            sgroup = sname.substring(0, sname.indexOf("bqxgp"));
          }
          // parse out the name of the target group
          if (tname.indexOf("bqxgp") > 0) {
            tgroup = tname.substring(0, tname.indexOf("bqxgp"));
          }

          var issorwithin = _.intersection([sname], nodeswithin);
          var istarwithin = _.intersection([tname], nodeswithin);
          // if the target is a groupless absorbed node and the source is external
          if (tname == ls && _.isEmpty(issorwithin)) {
            // try to guess the group of the target
            tgroup = $.grep(groupmath, function (gm){
              return gm.ls = tname;
            });
            tgroup = tgroup[0].guess;
            // add the link as an input to the guessed group
            groupi.push({"node": sname, "ngroup": sgroup, "type": type,
            "to": [tname], "group": tgroup, "valid": false});
            // as long as groups are involved, hide the links from the graph
            path[0][key].style.visibility = "hidden";
          }
          // if the source is a groupless absorbed node and the target is external
          if (sname == ls && _.isEmpty(istarwithin)) {
            // try to guess the group of the source
            sgroup = $.grep(groupmath, function (gm){
              return gm.ls = sname;
            });
            sgroup = sgroup[0].guess;
            // add the link as an output from the guessed group
            groupo.push({"node": tname, "ngroup": tgroup, "type": type,
            "fro": [sname], "group": sgroup, "valid": false});
            // as long as groups are involved, hide the links from the graph
            path[0][key].style.visibility = "hidden";
          }
        });
      });


      // for every given input
      for ( var i in groupi ) {
        var testgroupi = _.intersection(groupi[i].to,nodeswithin),
            testi = _.intersection(groupi[i].node,nodeswithin);
        // if the input goes to a node within
        if (testgroupi[0] || testi[0]) {
          groupi[i].valid = true;
        }
      }
      groupi = _.reject(groupi, function(i){
        // remove all inputs that don't go somewhere within
        return i.valid == false;
      });

      // for every given output
      for ( var o in groupo ) {
        var testgroupo = _.intersection(groupo[o].fro,nodeswithin),
            testo = _.intersection(groupo[o].node,nodeswithin);
        // if the input goes to a node within
        if (testgroupo[0] || testo[0]) {
          groupo[o].valid = true;
        }
      }
      groupo = _.reject(groupo, function(o){
        // remove all outputs that don't come from within
        return o.valid == false;
      });

      // for every given boxlink
      for ( var b in boxlinks ) {
        var testboxtnodes = _.intersection(boxlinks[b].tnodes,nodeswithin),
        testboxsnodes = _.intersection(boxlinks[b].snodes,nodeswithin);
        // if the input and output both go to a node within
        if (testboxtnodes[0] && testboxsnodes[0]) {
          boxlinks[b].valid = true;
        }
        // if either the input or output is outside the box
        else{
          // for every box link target within the box
          for (var t = 0; t < testboxtnodes.length; t++){
            var boxlinkindex = boxlinks[b].tnodes.indexOf(testboxtnodes[t]),
            isorname = boxlinks[b].snodes[boxlinkindex],
            itarname = boxlinks[b].tnodes[boxlinkindex],
            iboxname = boxlinks[b].tgp,
            niboxname = boxlinks[b].sgp,
            itype = boxlinks[b].type;
            // create a new input to the box
            groupi.push({"node": isorname, "group": iboxname, "ngroup": niboxname, "type": itype, "to": [itarname], "valid": false});
          }
          // for every box link source within the box
          for (var s = 0; s < testboxsnodes.length; s++){
            var boxlinkindex = boxlinks[b].snodes.indexOf(testboxsnodes[s]),
            osorname = boxlinks[b].snodes[boxlinkindex],
            otarname = boxlinks[b].tnodes[boxlinkindex],
            oboxname = boxlinks[b].sgp,
            noboxname = boxlinks[b].tgp,
            otype = boxlinks[b].type;
            // create a new output from the box
            groupo.push({"node": otarname, "group": oboxname, "ngroup": noboxname, "type": otype, "fro": [osorname], "valid": false});
          }
        }
      }
      boxlinks = _.reject(boxlinks, function(b){
        // remove all boxlinks that don't connect things within
        return b.valid == false;
      });

      // for every circle on the stage
      for (var j = 0; j < circle[0].length; j++) {
        // for every middleman in the group
        for (var n = 0; n < nodeswithin.length; n++) {
            // if the circle is a node within
            if (circle[0][j].id == "cir" +nodeswithin[n]) {
              // hide the circle from the graph
              circle[0][j].style.visibility = 'hidden';
              // hide the text from the graph
              text[0][j].style.visibility = 'hidden';
            }
        }
      }

      // append a new rectangle for all groups
      box = d3.select("#box").selectAll("rect")
      .data(groups)
      .enter().append("rect")
      .attr("width", 30)
      .attr("height", 30)
      .attr("id", function(d) {return "box" + d});
      // append a new label for all groups
      boxtext = d3.select("#boxtext").selectAll("text")
      .data(groups)
      .enter().append("text")
      .attr("class", "boxlabel")
      .attr("x", ".25em")
      .attr("y", "1em")
      .attr("id", function(d) { return d;})
      .text(function(d) { return d; });

      // for all inputs
      for (var key in groupi) {
        var input = groupi[key].node,
        group = groupi[key].group,
        type = groupi[key].type;
        if (type == 'bothandexcite' || type == 'bothandboth' || type == 'bothandinhibit') {
          if (type = 'bothandexcite') {
            var newred = {"source": input, "target": group, "group": group, "type": "inhibyt"};
            var newblack = {"source": input, "target": group, "group": group, "type": "excyte"};
            var backblack = {"source": group, "target": input, "group": group, "type": "excite"};
            newlinks.push(newred);
            newlinks.push(newblack);
            newlinks.push(backblack);
          }
          else if (type = 'bothandboth') {
            var newred = {"source": input, "target": group, "group": group, "type": "inhibyt"};
            var newblack = {"source": input, "target": group, "group": group, "type": "excyte"};
            var backblack = {"source": group, "target": input, "group": group, "type": "excyte"};
            var backred = {"source": group, "target": input, "group": group, "type": "inhibyt"};
            newlinks.push(newred);
            newlinks.push(backred);
            newlinks.push(newblack);
            newlinks.push(backblack);
          }
          else {
            var newred = {"source": input, "target": group, "group": group, "type": "inhibyt"};
            var newblack = {"source": input, "target": group, "group": group, "type": "excyte"};
            var backred = {"source": group, "target": input, "group": group, "type": "inhibit"};
            newlinks.push(newred);
            newlinks.push(backred);
            newlinks.push(newblack);
          }
        }
        else if (type == 'exciteandexcite' || type == 'exciteandboth' || type == 'exciteandinhibit') {
          if (type = 'exciteandexcite') {
            var newblack = {"source": input, "target": group, "group": group, "type": "excite"};
            var backblack = {"source": group, "target": input, "group": group, "type": "excite"};
            newlinks.push(newblack);
            newlinks.push(backblack);
          }
          else if (type = 'exciteandboth') {
            var newblack = {"source": input, "target": group, "group": group, "type": "excite"};
            var backblack = {"source": group, "target": input, "group": group, "type": "excyte"};
            var backred = {"source": group, "target": input, "group": group, "type": "inhibyt"};
            newlinks.push(backred);
            newlinks.push(newblack);
            newlinks.push(backblack);
          }
          else {
            var newblack = {"source": input, "target": group, "group": group, "type": "excite"};
            var backred = {"source": group, "target": input, "group": group, "type": "inhibit"};
            newlinks.push(backred);
            newlinks.push(newblack);
          }
        }
        else if (type == 'inhibitandexcite' || type == 'inhibitandboth' || type == 'inhibitandinhibit') {
          if (type = 'inhibitandexcite') {
            var newred = {"source": input, "target": group, "group": group, "type": "inhibit"};
            var backblack = {"source": group, "target": input, "group": group, "type": "excite"};
            newlinks.push(newred);
            newlinks.push(backblack);
          }
          else if (type = 'inhibitandboth') {
            var newred = {"source": input, "target": group, "group": group, "type": "inhibit"};
            var backblack = {"source": group, "target": input, "group": group, "type": "excyte"};
            var backred = {"source": group, "target": input, "group": group, "type": "inhibyt"};
            newlinks.push(backred);
            newlinks.push(newred);
            newlinks.push(backblack);
          }
          else {
            var newred = {"source": input, "target": group, "group": group, "type": "inhibit"};
            var backred = {"source": group, "target": input, "group": group, "type": "inhibit"};
            newlinks.push(backred);
            newlinks.push(newred);
          }
        }
        else {
          if (type == 'both') {
            var newblack = {"source": input, "target": group, "group": group, "type": "excyte"};
            var newred = {"source": input, "target": group, "group": group, "type": "inhibyt"};
            newlinks.push(newred);
            newlinks.push(newblack);
          }
          else if (type == 'excite') {
            var newblack = {"source": input, "target": group, "group": group, "type": "excite"};
            newlinks.push(newblack);
          }
          else {
            var newred = {"source": input, "target": group, "group": group, "type": "inhibit"};
            newlinks.push(newred);
          }
        }
      }

      // for all outputs
      for (var key in groupo) {
      var output = groupo[key].node,
          group = groupo[key].group,
          type = groupo[key].type;
          if (type == 'bothandexcite' || type == 'bothandboth' || type == 'bothandinhibit') {
            if (type = 'bothandexcite') {
              var newred = {"source": group, "target": output, "group": group, "type": "inhibyt"};
              var newblack = {"source": group, "target": output, "group": group, "type": "excyte"};
              var backblack = {"source": output, "target": group, "group": group, "type": "excite"};
              newlinks.push(newred);
              newlinks.push(newblack);
              newlinks.push(newblack);
            }
            else if (type = 'bothandboth') {
              var newred = {"source": group, "target": output, "group": group, "type": "inhibyt"};
              var newblack = {"source": group, "target": output, "group": group, "type": "excyte"};
              var backblack = {"source": output, "target": group, "group": group, "type": "excyte"};
              var backred = {"source": output, "target": group, "group": group, "type": "inhibyt"};
              newlinks.push(newred);
              newlinks.push(backred);
              newlinks.push(newblack);
              newlinks.push(backblack);
            }
            else {
              var newred = {"source": group, "target": output, "group": group, "type": "inhibyt"};
              var newblack = {"source": group, "target": output, "group": group, "type": "excyte"};
              var backred = {"source": output, "target": group, "group": group, "type": "inhibit"};
              newlinks.push(newred);
              newlinks.push(backred);
              newlinks.push(newblack);
            }
          }
          else if (type == 'exciteandexcite' || type == 'exciteandboth' || type == 'exciteandinhibit') {
            if (type = 'exciteandexcite') {
              var newblack = {"source": group, "target": output, "group": group, "type": "excite"};
              var backblack = {"source": output, "target": group, "group": group, "type": "excite"};
              newlinks.push(newblack);
              newlinks.push(backblack);
            }
            else if (type = 'exciteandboth') {
              var newblack = {"source": group, "target": output, "group": group, "type": "excite"};
              var backblack = {"source": output, "target": group, "group": group, "type": "excyte"};
              var backred = {"source": output, "target": group, "group": group, "type": "inhibyt"};
              newlinks.push(backred);
              newlinks.push(newblack);
              newlinks.push(backblack);
            }
            else {
              var newblack = {"source": group, "target": output, "group": group, "type": "excyte"};
              var backred = {"source": output, "target": group, "group": group, "type": "inhibit"};
              newlinks.push(backred);
              newlinks.push(newblack);
            }
          }
          else if (type == 'inhibitandexcite' || type == 'inhibitandboth' || type == 'inhibitandinhibit') {
            if (type = 'inhibitandexcite') {
              var newred = {"source": group, "target": output, "group": group, "type": "inhibit"};
              var backblack = {"source": output, "target": group, "group": group, "type": "excite"};
              newlinks.push(newred);
              newlinks.push(backblack);
            }
            else if (type = 'inhibitandboth') {
              var newred = {"source": group, "target": output, "group": group, "type": "inhibit"};
              var backblack = {"source": output, "target": group, "group": group, "type": "excyte"};
              var backred = {"source": output, "target": group, "group": group, "type": "inhibyt"};
              newlinks.push(backred);
              newlinks.push(newred);
              newlinks.push(backblack);
            }
            else {
              var newred = {"source": group, "target": output, "group": group, "type": "inhibit"};
              var backred = {"source": output, "target": group, "group": group, "type": "inhibit"};
              newlinks.push(backred);
              newlinks.push(newred);
            }
          }
          else {
            if (type == 'both') {
              var newblack = {"source": group, "target": output, "group": group, "type": "excyte"};
              var newred = {"source": group, "target": output, "group": group, "type": "inhibyt"};
              newlinks.push(newred);
              newlinks.push(newblack);
            }
            else if (type == 'excite') {
              var newblack = {"source": group, "target": output, "group": group, "type": "excite"};
              newlinks.push(newblack);
            }
            else {
              var newred = {"source": group, "target": output, "group": group, "type": "inhibit"};
              newlinks.push(newred);
            }
          }
      }

      // for all connections between boxes
      for (var key in boxlinks) {
        var targroup = boxlinks[key].tgp,
            sorgroup = boxlinks[key].sgp,
            type = boxlinks[key].type;
        if (type == 'bothandexcite' || type == 'bothandboth' || type == 'bothandinhibit') {
          if (type == 'bothandexcite') {
            var newred = {"source": sorgroup, "target": targroup, "group": sorgroup, "type": "inhibyt"};
            var newblack = {"source": sorgroup, "target": targroup, "group": sorgroup, "type": "excyte"};
            var backblack = {"source": targroup, "target": sorgroup, "group": targroup, "type": "excite"};
            newlinks.push(backblack);
            newlinks.push(newblack);
            newlinks.push(newred);
          }
          else if (type == 'bothandboth') {
            var backblack = {"source": targroup, "target": sorgroup, "group": targroup, "type": "excyte"};
            var newblack = {"source": sorgroup, "target": targroup, "group": sorgroup, "type": "excyte"};
            var backred = {"source": targroup, "target": sorgroup, "group": targroup, "type": "inhibyt"};
            var newred = {"source": sorgroup, "target": targroup, "group": sorgroup, "type": "inhibyt"};
            newlinks.push(newblack);
            newlinks.push(backblack);
            newlinks.push(newred);
            newlinks.push(backred);
          }
          else {
            var newred = {"source": sorgroup, "target": targroup, "group": sorgroup, "type": "inhibyt"};
            var newblack = {"source": sorgroup, "target": targroup, "group": sorgroup, "type": "excyte"};
            var backblack = {"source": targroup, "target": sorgroup, "group": targroup, "type": "inhibit"};
            newlinks.push(backblack);
            newlinks.push(newblack);
            newlinks.push(newred);
          }
        }
        else if (type == 'exciteandexcite' || type == 'exciteandboth' || type == 'exciteandinhibit') {
          var newblack = {"source": sorgroup, "target": targroup, "group": sorgroup, "type": "excite"};
          newlinks.push(newblack);
          if (type == 'exciteandexcite') {
            var backblack = {"source": targroup, "target": sorgroup, "group": targroup, "type": "excite"};
            newlinks.push(backblack);
          }
          else if (type == 'exciteandboth') {
            var backred = {"source": targroup, "target": sorgroup, "group": targroup, "type": "inhibyt"};
            var backblack = {"source": targroup, "target": sorgroup, "group": targroup, "type": "excyte"};
            newlinks.push(backblack);
            newlinks.push(backred);
          }
          else {
            var backblack = {"source": targroup, "target": sorgroup, "group": targroup, "type": "inhibit"};
            newlinks.push(backblack);
          }
        }
        else if (type == 'inhibitandexcite' || type == 'inhibitandboth' || type == 'inhibitandinhibit') {
          var newred = {"source": sorgroup, "target": targroup, "group": sorgroup, "type": "inhibit"};
          newlinks.push(newblack);
          newlinks.push(newred);
          if (type == 'inhibitandexcite') {
            var backblack = {"source": targroup, "target": sorgroup, "group": targroup, "type": "excite"};
            newlinks.push(backblack);
          }
          else if (type == 'inhibitandboth') {
            var backred = {"source": targroup, "target": sorgroup, "group": targroup, "type": "inhibyt"};
            var backblack = {"source": targroup, "target": sorgroup, "group": targroup, "type": "excyte"};
            newlinks.push(backblack);
            newlinks.push(backred);
          }
          else {
            var backblack = {"source": targroup, "target": sorgroup, "group": targroup, "type": "inhibit"};
            newlinks.push(backblack);
          }
        }
        else {
          if (type == 'both') {
            var newblack = {"source": sorgroup, "target": targroup, "group": sorgroup, "type": "excyte"};
            var newred = {"source": sorgroup, "target": targroup, "group": sorgroup, "type": "inhibyt"};
            newlinks.push(newblack);
          }
          else if (type == 'excite') {
            var newblack = {"source": sorgroup, "target": targroup, "group": sorgroup, "type": "excite"};
            newlinks.push(newblack);
          }
          else {
            var newred = {"source": sorgroup, "target": targroup, "group": sorgroup, "type": "inhibit"};
            newlinks.push(newred);
          }
        }
      }

      // for all links between groups
      for (var key in iopaths) {
        // check if the source or target of the link are within a group
        var innersource = $.inArray(iopaths[key].getAttribute("source"), nodeswithin),
        innertarget = $.inArray(iopaths[key].getAttribute("target"), nodeswithin);
        // if neither the source nor target are in a group
        if (innersource == -1 && innertarget == -1) {
          iopaths[key].style.visibility = "visible";
        }
      }

      // append new group paths for all groups
      boxpath = d3.select("#boxpath").selectAll("path")
      .data(newlinks)
      .enter().append("path")
      .attr("class", function(d) { return "link " + d.type; })
      .attr("marker-end", function(d) { return "url(#" + d.type + ")"; })
      .attr("source", function(d) { return d.source; })
      .attr("target", function(d) { return d.target; })
      .attr("group", function(d) { return d.group; });

    }
    //reheat force
    tick();
  }

  // listens for any changes in the window
  $(window).resize(function(){windowchange()});

  // follow mouse
  function mousemove() {
    cursor.attr("transform", "translate(" + d3.mouse(this) + ")");
  }

  // effects of clicks
  function mousedown() {
    // get the position of the mouse
    var mouspos = $(".cursor").position();
    // for all circle positions, subtract mouse position
    Object.keys(nodes).forEach(function(node) {
       // get the corresponding circle for the current node
        var svgid = document.getElementById("cir" + nodes[node].name);
        // if the circle is not hidden
        if (svgid !== null) {
          var svgpos = $(svgid).position();
          // calculate the difference in coordinates
          x = svgpos.left - 15 - mouspos.left,
          y = svgpos.top - 15 - mouspos.top;

          // if a node is touching the mouse
          if (Math.sqrt(x * x + y * y) < 30) {
            // check if the current node is above threshold
            if (nodes[node].value > 0) {
              // inhibit the neuron
              nodes[node].state = "inactive";
              nodes[node].value = 0;
              // make the neuron look inactive
              svgid.style.fill = "grey";
            }
            // else, the current node is below threshold
            else {
              // excite the neuron
              nodes[node].state = "active";
              nodes[node].value = 1;
              // make the neuron look active
              svgid.style.fill = "green";
            }
          }
        }
    });
  }

  // the main clock
  function tick() {
    // global clock flag
    timemoving = true;
    // find link position
    path.attr("d", linkline);
    // find node position
    circle.attr("transform", transform);
    // find text position
    text.attr("transform", transform);
    // find black box position
    box.attr("transform", boxform);
    // find black box link position
    boxpath.attr("d", boxline);
    // find black box text position
    boxtext.attr("transform", boxform);

    // run one of two slower clocks
      if (document.getElementById("sim").checked) {
        if ( t > speed ) {
          // pulse
          restart();
          // update values, change graphic
          tock1(tock2);
        }
      }
      else {
          t = speed;
      }

      // one tick passes
      t++;

      // global clock flag
      timemoving = false;
  }

  // fist step, update values
  function tock1(callback){
    // for each possible link in the data
    for (var key in data) {
      // keep track of its type
      var synapse = data[key].type,
          // keep track of the name of the source
          sorname = data[key].source.name;

      // if the source is active
      if (nodes[sorname].state == "active")
        {
          // keep track of the name of the target
          var tarname = data[key].target.name;

          // if the link is inhibitory
          if (synapse == "inhibit")
            {
            // get the weight
            var ipsp = data[key].weight;
            // inhibit the target
            nodes[tarname].value = nodes[tarname].value - ipsp;
            // reset the source
          //  nodes[sorname].value = 0;
            }
          // if the link is excitatory
          if (synapse == "excite")
            {
            // get the weight
            var epsp = data[key].weight;
            // excite the target
            nodes[tarname].value = nodes[tarname].value + epsp;
            // reset the source
           // nodes[sorname].value = 0;
            }
        }
      }
      if (typeof callback == "function") callback();
  }

  // second step, update activtiy
  function tock2() {
      // For each node
      Object.keys(nodes).forEach(function(node) {
        // keep track of the corresponding circle
        var tarsvg = document.getElementById("cir" + nodes[node].name);
        // compare the value to the threshold
        if (nodes[node].value >= nodes[node].thresh)
          {
            // if the node exists
            if (tarsvg !== null) {
              // cause the neuron to be lit up
              tarsvg.style.fill = "green";
            }
            // cause the node to be activated
            nodes[node].state = "active",
            // reset the value to zero
            nodes[node].value = 0;
          }
        else
          {
            // if the node exists
            if (tarsvg !== null) {
              // cause the neuron to be turned off
              tarsvg.style.fill = "grey";
            }
            // cause the node to be inhibited
            nodes[node].state = "inactive",
            // reset the value to zero
            nodes[node].value = 0;
          }
      });
  }

  function windowchange() {

    // recenter the force location
    var width = window.innerWidth;
    var height = window.innerHeight + getTopBuffer() / 2;
    force.size([width, height]),

    // resize the viewing window
    svg.attr("width", width),
    svg.attr("height", height),

    // resize the zooming detector space
    rect.attr("width", width),
    rect.attr("height", height),

    restart();
  }

  // link line position
  function linkline(d) {
    var x = translation[0] + scaleFactor*(d.source.x),
        xt = translation[0] + scaleFactor*(d.target.x),
        y = translation[1] + scaleFactor*(d.source.y);
        yt = translation[1] + scaleFactor*(d.target.y);
    return "M" +x+ "," +y+ "L" +xt+ "," +yt;
  }

  // node position
  function transform(d) {
      var x = translation[0] + scaleFactor*(d.x),
          y = translation[1] + scaleFactor*(d.y);
      return "translate(" +x+ "," +y+ ")";
  }


  // box position
  function boxform(d) {
    var sumx = 0,
        sumy = 0,
        x = 0,
        y = 0,
        i = 0;

    // find all circles within the group
    circle[0].forEach(function(circ){
      if (circ.id.indexOf(d + "bqxgp") > -1) {
        // if the circles are hidden
          // parse the position of each circle
          var posit = d3.select(circ).attr("transform"),
              pars1 = posit.indexOf("("),
              pars2 = posit.indexOf(","),
              pars3 = posit.indexOf(")");
          // record their coordinates
          sumx = sumx + parseInt(posit.substring(pars1 + 1, pars2), 10);
          sumy = sumy + parseInt(posit.substring(pars2 + 1, pars3), 10);
          i++;
    //    }
      }
    });
    if (i == 0) {
      // find all circles within the group
      circle[0].forEach(function(circ){
        if (circ.id.indexOf(d + "bqxgp") > -1) {
          // parse the position of each circle
          var posit = d3.select(circ).attr("transform"),
          pars1 = posit.indexOf("("),
          pars2 = posit.indexOf(","),
          pars3 = posit.indexOf(")");
          // record their coordinates
          sumx = sumx + parseInt(posit.substring(pars1 + 1, pars2), 10);
          sumy = sumy + parseInt(posit.substring(pars2 + 1, pars3), 10);
          i++;
        }
      });
    }

    x = sumx / i - 15,
    y = sumy / i - 15;

    return "translate(" +x+ "," +y+ ")";
  }

  function boxline(d) {

    // if the box is the target
    if (d.group == d.target) {
        var tar = d3.select("#box"+d.target),
        sor = d3.select("#cir"+d.source);
        // parse the position of target and source
        if (tar[0][0] != null && sor[0][0] != null) {
          var tarpos = tar.attr("transform"),
          sorpos = sor.attr("transform");
        }
        else {
          tarpos = "(0,0)";
          sorpos = "(0,0)";
        }

        var tarpars1 = tarpos.indexOf("("),
        tarpars2 = tarpos.indexOf(","),
        tarpars3 = tarpos.indexOf(")"),

        sorpars1 = sorpos.indexOf("("),
        sorpars2 = sorpos.indexOf(","),
        sorpars3 = sorpos.indexOf(")");

        // record their coordinates
        var sx = parseInt(sorpos.substring(sorpars1 + 1, sorpars2), 10),
        sy = parseInt(sorpos.substring(sorpars2 + 1, sorpars3), 10),
        tx = parseInt(tarpos.substring(tarpars1 + 1, tarpars2), 10) + 15,
        ty = parseInt(tarpos.substring(tarpars2 + 1, tarpars3), 10) + 15;
    }
    // if the box is the source
    else if (d.group == d.source) {
        var sor = d3.select("#box"+d.source),
        tar = d3.select("#cir"+d.target),
        joiny = 0;
        // if the box connects to another box
        if (tar [0][0] == null) {
          tar = d3.select("#box"+d.target);
          joiny = 15;
        }
        // parse the position of target and source
        if (tar[0][0] != null && sor[0][0] != null) {
          var tarpos = tar.attr("transform"),
          sorpos = sor.attr("transform");
        }
        else {
          tarpos = "(0,0)";
          sorpos = "(0,0)";
        }

        var tarpars1 = tarpos.indexOf("("),
        tarpars2 = tarpos.indexOf(","),
        tarpars3 = tarpos.indexOf(")"),

        sorpars1 = sorpos.indexOf("("),
        sorpars2 = sorpos.indexOf(","),
        sorpars3 = sorpos.indexOf(")");

        // record their coordinates
        var sx = parseInt(sorpos.substring(sorpars1 + 1, sorpars2), 10) + 15,
        sy = parseInt(sorpos.substring(sorpars2 + 1, sorpars3), 10) + 15,
        tx = parseInt(tarpos.substring(tarpars1 + 1, tarpars2), 10) + joiny,
        ty = parseInt(tarpos.substring(tarpars2 + 1, tarpars3), 10) + joiny;
    }

    return "M" +sx+ "," +sy+ "L" +tx+ "," +ty;
  }

  // stick/unstick nodes

  function dblclick(d) {
    if (d3.select(this).classed("fixed")) {
      d3.select(this).classed("fixed", d.fixed = false);
    }
    else {
      d3.select(this).classed("fixed", d.fixed = true);
    }
  }

  // restart force and time
  function restart() {
    force.start();
    t = 1;
  }

  function iointersect(a,b){
    var within=[];
    for(m in a){
      for(n in b){
        if((a[m].node==b[n].node)&&(a[m].group==b[n].group))
          within.push(a[m]);
        }}
        return within;
      }

  function gconnect(a,b){
    var between=[];
    for(m in a){
      for(n in b){
        if((a[m].node==b[n].node)&&(a[m].group!=b[n].group))
          between.push(a[m]);
        }}
        return between;
      }
}
// if there is no data
else {
  // set size of the entire layout
  var width = window.innerWidth;
  var height = window.innerHeight + getTopBuffer() / 2;

  // Create an svg division in html
  var svg = d3.select("#viz").append("svg")
  .attr("width", width)
  .attr("height", height)
  .attr("id", "forcevisual")
  .on("mousemove", mousemove)
  .on("dblclick.zoom", null);

  // create a circle for the cursor
  var cursor = svg.append("circle")
  .attr("r", 25)
  .attr("transform", "translate(-100,-100)")
  .attr("class", "cursor");

  // listen for fixed slider changes
  $("#fixins").click(function(){
    // send the new value to index.php
    $.post( "index.php", { fixed: this.value} );
  });

  // listens for abstraction box changes
  $("#abstract").click(function() {
    // send the new value to index.php
    $.post( "index.php", { checked: this.checked} );
  });

  // follow mouse
  function mousemove() {
    cursor.attr("transform", "translate(" + d3.mouse(this) + ")");
  }

}
});
});
