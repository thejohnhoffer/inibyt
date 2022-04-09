<?php

    // configuration
    require("../includes/config.php");

    // if user reached page via GET (as by clicking a link or via redirect)
    if ($_SERVER["REQUEST_METHOD"] == "GET") {

      // query database for user abstraction preference
      $abstract = query("SELECT abstract FROM users WHERE id = ?", $_SESSION["id"]);
      // query database for user fixation preference
      $fixation = query("SELECT fixed FROM users WHERE id = ?", $_SESSION["id"]);
      // prepare the preferences for formatting
      if (!empty($abstract)){
        $abstract = $abstract[0]['abstract'];
      }
      else {
        $abstract = 'checked';
      }
      if (!empty($fixation)){
        $fixation = $fixation[0]['fixed'];
      }
      else {
        $fixation = '0.4';
      }
      // render home page
      render("shome.php", ["title" => "Get Xcytd", "checked" => $abstract,
      "fixed" => $fixation, 'message' => 'Upload boxed networks' ]);
    }

    // if the user reached page via POST
    else if ($_SERVER["REQUEST_METHOD"] == "POST") {

        $json = $_POST['groupfile'];
        $gdata = substr($json, strrpos($json," goes online             "));
        $gbox = substr($json,0,-1*strlen($gdata));

        // check if the gbox is empty
        if (empty($gbox)) {
          $update = "Which group?";
        }
        else {
          // check if the gbox is already in the database...
          $duplicate = query("SELECT * FROM `files` WHERE `box` = ?", $gbox);

          // if the box is already in the database
          if (!empty($duplicate)) {
            $update = "Sorry: your group name is taken";
          }
          else {
            while (strrpos($gdata, "\"target\":") != false){
              $newlink = substr($gdata, strrpos($gdata, "\"target\":"));
              $gdata = substr($gdata,0,-1*strlen($newlink));

              $newtype = substr($newlink, strrpos($newlink, "\"type\":"));
              $newlink = substr($newlink,0,-1*strlen($newtype));

              $newweight = substr($newlink, strrpos($newlink, "\"weight\":"));
              $newlink = substr($newlink,0,-1*strlen($newweight));

              $newsource = substr($newlink, strrpos($newlink, "\"source\":"));
              $newlink = substr($newlink,0,-1*strlen($newsource));

              $newtarget = substr($newlink, strrpos($newlink, "\"target\":"));


              $newtype = str_replace("\"type\":\"","",$newtype);
              $newtype = str_replace("\"]","",$newtype);
              $newtype = str_replace("\",","",$newtype);


              $newweight = str_replace("\"weight\":","",$newweight);
              $newweight = str_replace("]","",$newweight);
              $newweight = str_replace(",","",$newweight);


              $newsource = str_replace("\"source\":\"","",$newsource);
              $newsource = str_replace("\"]","",$newsource);
              $newsource = str_replace("\",","",$newsource);

              $newtarget = str_replace("\"target\":\"","",$newtarget);
              $newtarget = str_replace("\"]","",$newtarget);
              $newtarget = str_replace("\",","",$newtarget);

              $match = query ("SELECT box FROM files WHERE source = ? AND
                  type = ? AND target = ? AND box = ?", $newsource, $newtype,
                  $newtarget, $gbox);

              if (empty($match)) {
                $results = query("INSERT INTO `inibyt`.`files`
                  (`source`, `type`, `target`, `box`, `weight`) VALUES(?,?,?,?,?)",
                  $newsource, $newtype, $newtarget, $gbox, $newweight);
              }
            }

          $update = $gbox." online.";
        }
      }

      // query database for user abstraction preference
      $abstract = query("SELECT abstract FROM users WHERE id = ?", $_SESSION["id"]);
      // query database for user fixation preference
      $fixation = query("SELECT fixed FROM users WHERE id = ?", $_SESSION["id"]);

      // prepare the preferences for formatting
      if (!empty($abstract)){
        $abstract = $abstract[0]['abstract'];
      }
      else {
        $abstract = 'checked';
      }
      if (!empty($fixation)){
        $fixation = $fixation[0]['fixed'];
      }
      else {
        $fixation = '0.4';
      }
      // set the new preferences to current preferences
      $newabstraction = $abstract;
      $newfixation = $fixation;

      // if there's been a change, update the preference
      if(isset($_POST['checked'])) {
        if ($_POST['checked'] == 'true') {
          $newabstraction='checked';
        }
        else {
          $newabstraction='';
        }
      }
      // if there's been a change, update the preference
      if(isset($_POST['fixed'])) {
        $newfixation = $_POST['fixed'];
      }

      // update the user abstraction preference
      query("UPDATE users SET abstract = ? WHERE id = ?", $newabstraction ,$_SESSION["id"]);
      // update the user fixation preference
      query("UPDATE users SET fixed = ? WHERE id = ?", $newfixation ,$_SESSION["id"]);


      // render home page
      render("shome.php", ["title" => "Get Xcytd", "checked" => $newabstraction,
      "fixed" => $newfixation, "message" => $update ]);
    }

?>
