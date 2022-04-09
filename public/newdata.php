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
        render("add_data.php", ["title" => "New Data", "notice" => "Make Connections.", "checked" => $abstract,
        "fixed" => $fixation ]);
    }

    // else if user reached page via POST (as by submitting a form via POST)
    else if ($_SERVER["REQUEST_METHOD"] == "POST")
    {
      // query database for user abstraction preference
      $abstract = query("SELECT abstract FROM users WHERE id = ?", $_SESSION["id"]);
      // prepare the abstraction preference for formatting
      $abstract = $abstract[0]['abstract'];

      $request = $_POST["newsource"];
      $parts = explode(" ", $request);

      if (sizeof($parts) > 2) {
        if (sizeof($parts) > 4) {
          $weight = $parts[4];
          $target = $parts[2];
          $type = $parts[1];
          $source = $parts[0];

          if (strcasecmp($weight, "mV") == 0)  {
            $weight = "10mV";
          }
        }
        else if (sizeof($parts) > 3) {
          $weight = $parts[3];
          $target = $parts[2];
          $type = $parts[1];
          $source = $parts[0];

          if (strcasecmp($weight, "mV") == 0)  {
            $weight = "10mV";
          }
        }
        else {
          $weight = "10mV";
          $target = $parts[2];
          $type = $parts[1];
          $source = $parts[0];
        }

        // process the types
        $sverb = strripos($type, 's');
        $type = substr($type, 0, $sverb);

        // convert the nodes to uppercase
        $source = strtoupper($source);
        $target = strtoupper($target);

        // Remove all (new) from nodes
        $source = str_replace("(NEW)","", $source);
        $target = str_replace("(NEW)","", $target);

        // Remove all mV from weights
        $weight = str_replace("mV","", $weight);

        // convert the type to lowercase
        $type = strtolower($type);

        // change all periods to groupmarker bqxgp
        $source = str_replace(".","bqxgp", $source);
        $target = str_replace(".","bqxgp", $target);

        // remove all non-alphanumeric characters from everything
        $source = preg_replace("/[^A-Za-z0-9 ]/", '', $source);
        $target = preg_replace("/[^A-Za-z0-9 ]/", '', $target);
        $type = preg_replace("/[^A-Za-z0-9 ]/", '', $type);

        // Annoyingly, change all 0s, 3s, 5s, to Os, Es, and Ss for comparison
        $scmp = str_replace("0", "O", $source);
        $scmp = str_replace("5", "S", $scmp);
        $scmp = str_replace("3", "E", $scmp);
        // Annoyingly, change all 0s, 3s, 5s, to Os, Es, and Ss for comparison
        $tcmp = str_replace("0", "O", $target);
        $tcmp = str_replace("5", "S", $tcmp);
        $tcmp = str_replace("3", "E", $tcmp);

        // create an array of bad word substrings // sorry this has to happen
        $uhoh = array("censored");

        // Assume the word is innocent until proven guilty
        $tbad = false;
        $sbad = false;

        // make sure the word contains no bad strings
        foreach ($uhoh as $w) {
          // if the target contains a bad string
          if (stripos($tcmp, $w) !== false){
            $tbad = true;
          }
          // if the source contains a bad string
          if (stripos($scmp, $w) !== false){
            $sbad = true;
          }
        }

        // check if the target is bad
        if ($tbad) {
          $answer = "Please check your diction.";
        }
        // check if the source is bad
        else if ($sbad) {
          $answer = "Please check your diction.";
        }
        else if (!is_numeric($weight)) {
            // allow for the user to replace nodes here
          if (strcasecmp($type,"replace") == 0){
            // search for a match in the database
            $tarmatch = query("SELECT `target` FROM `inibyt`.`userlinks`
              WHERE `userid` = ? AND `target` = ?",
              $_SESSION["id"], $target);
            if (empty($tarmatch)){
              $tarmatch = query("SELECT `source` FROM `inibyt`.`userlinks`
              WHERE `userid` = ? AND `source` =?",
              $_SESSION["id"], $target);

              // change the label from source to target
              $tarmatch = array_map(function($tarmatch) {
                return array(
                  'target' => $tarmatch['source']
                );
              }, $tarmatch);
            }

            if (!empty($tarmatch)){
              // rename all matches in the database
              query("UPDATE `inibyt`.`userlinks` SET `target` = ? WHERE
                `userid` = ? AND `target` = ? ", $source, $_SESSION["id"], $target);
              query("UPDATE `inibyt`.`userlinks` SET `source` = ? WHERE
                `userid` = ? AND `source` = ?", $source, $_SESSION["id"], $target);

              $answer = "Update: ".$source." has replaced ".$target.".";
            }
            else {
              $answer = "Sorry: there's no node called " .$target.".";
            }
          }
          else {
            $answer = "Sorry: no such thing as ".$weight." mV.";
          }
        }

        else if ($weight > 10 ) {
          $answer = "Sorry: ".$weight."mV is too strong.";
        }
        else if ($weight < 0.01 ) {
          if ($weight < 0){
            $answer = "Sorry: inibyt has no signed voltage.";
          }
          else {
            $answer = "Sorry: ".$weight."mV is too weak.";
          }
        }
        else {
           if (strcasecmp($type,"excite")==0 || strcasecmp($type,"inhibit")==0){
            // search for a match in the database
            $match = query("SELECT * FROM `inibyt`.`userlinks`
              WHERE `userid` = ? AND `source` = ? AND `target` = ?",
              $_SESSION["id"], $source, $target);

            if (!empty($match)){
              // delete the match in the database
              $match = query("DELETE FROM `inibyt`.`userlinks`
                WHERE `userid` = ? AND `source` = ? AND `target` = ?",
                $_SESSION["id"], $source, $target);

              // add the selected link to the database
              query("INSERT INTO `inibyt`.`userlinks`
                (`userid`, `source`, `type`, `target`,`weight`) VALUES(?,?,?,?,?)",
                $_SESSION["id"], $source, $type, $target, 10*$weight);

                // answer in joyous triumph to the user
                $answer = "Update: ".$source." ".$type."s ".$target." ".$weight."mV.";
            }
            else {
              // add the selected link to the database
              query("INSERT INTO `inibyt`.`userlinks`
                (`userid`, `source`, `type`, `target`,`weight`) VALUES(?,?,?,?,?)",
                $_SESSION["id"], $source, $type, $target, 10*$weight);

              // answer in joyous triumph to the user
              $answer = "Update: ".$source." ".$type."s ".$target." ".$weight."mV.";
            }
          }
          else {
            $answer = "Please specify a link.";
          }
        }
      }
      else {
        if (sizeof($parts) > 1) {
          $answer = "Please specify a target.";
        }
        else if (sizeof($parts) > 0) {
          $answer = "Please specify a link.";
        }
        else {
          $answer = "Please specify a source.";
        }
      }

      // change all groupmarkers bqxgp back to periods
      $answer = str_replace("bqxgp",".", $answer);

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


      // render form
      render("add_data.php", ["title" => "New Data", "fixed" => $newfixation, "notice" => $answer, "checked" => $abstract]);

    }
?>
