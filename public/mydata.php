<?php

    // configuration
    require("../includes/config.php");

    // if user reached page via GET (as by clicking a link or via redirect)
    if ($_SERVER["REQUEST_METHOD"] == "GET")
    {
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
      // render form
      render("delete_data.php", ["title" => "New Data", "notice" => "Break Connections.", "checked" => $abstract,
      "fixed" => $fixation ]);
    }

    // else if user reached page via POST (as by submitting a form via POST)
    else if ($_SERVER["REQUEST_METHOD"] == "POST")
    {
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

      // derp secret check if John typed eliminate
      if (strcasecmp($_POST["link"],"this eliminates all") === 0) {
      query("DELETE FROM `inibyt`.`userlinks`
          WHERE `userid` = ?", $_SESSION["id"]);
      $answer = "All has been eliminated.";
      // render form
      render("delete_data.php", ["title" => "Mydata", "notice" => $answer, "fixed" => $fixation, "checked" => $abstract]);
      }

      // derp
      else {

      // parse the text field
      $request = $_POST["link"];

      // change all periods to groupmarker bqxgp
      $request = str_replace(".","bqxgp", $request);

      $space1 = strpos($request, ' ');
      $space2 = strrpos($request, ' ');
      $source = substr($request, 0, $space1);
      $type = substr($request, $space1, $space2);
            $sverb = strrpos($type, 's');
            $type = substr($type, 1, $sverb -1);
      $target = substr($request, $space2 + 1);

      // if the user is trying to delete a group
      if (strcasecmp($type, "i") == 0) {
        // search for a group match in the database
        $tarmatch = query("SELECT * FROM `inibyt`.`userlinks`
          WHERE `userid` = ? AND `target` LIKE ?",
          $_SESSION["id"], $source."bqxgp%");

        if (empty($tarmatch)) {
          // respond failed and ashamed to the user
          $answer = "Sorry: No groups like that.";
        }
        else {
          // delete the selected group from the database
          query("DELETE FROM `inibyt`.`userlinks`
            WHERE `userid` = ? AND `source` LIKE ?",
            $_SESSION["id"], $source."bqxgp%");

          query("DELETE FROM `inibyt`.`userlinks`
            WHERE `userid` = ? AND `target` = ?",
            $_SESSION["id"], $source."bqxgp%");
            // respond sucessful to the user
            $answer = "Update: ".$source." ".$type."s no longer ".$target.".";
        }

      }
      else {
        // search for a match in the database
        $match = query("SELECT * FROM `inibyt`.`userlinks`
                  WHERE `userid` = ? AND `source` = ?
                  AND `type` = ? AND `target` = ?",
                  $_SESSION["id"], $source, $type, $target);

        if (empty($match)) {
          // respond failed and ashamed to the user
          $answer = "Sorry: No links like that.";
        }
        else {
          // delete the selected link from the database
          query("DELETE FROM `inibyt`.`userlinks`
              WHERE `userid` = ? AND `source` = ?
              AND `type` = ? AND `target` = ?",
              $_SESSION["id"], $source, $type, $target);
          // respond sucessful to the user
          $answer = "Update: ".$source." doesn't ".$type." ".$target.".";
        }
      }

      // change groupmarkers bqxgp back to periods
      $answer = str_replace("bqxgp",".", $answer);

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

      // render form
      render("delete_data.php", ["title" => "Mydata", "fixed" => $newfixation, "notice" => $answer, "checked" => $abstract]);

    }//derp derp derp

    }
?>
