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

      // get a list of all the uploaded files:
      $newuploads = query("SELECT DISTINCT `box` FROM `files`");

      $uploads = [];
      // extract the box name from each file
      foreach ($newuploads as $nu) {
        $uploads[] = $nu["box"];
      }

      shuffle($uploads);

      $news = "Online: ".implode(", ", $uploads);

      // render home page
      render("home.php", ["title" => "Get Xcytd", "checked" => $abstract,
      "fixed" => $fixation, 'message' => 'Download Boxed Networks', "news" => $news ]);
    }

    // if the user reached page via POST (as by adjusting abstraction settings)
    else if ($_SERVER["REQUEST_METHOD"] == "POST") {

      $file = $_POST['file'];

      // search the public database for matching files
      $boxmatch = query("SELECT * FROM `inibyt`.`files` WHERE `box` = ?", $file);

      if (!empty($boxmatch)){
      foreach ($boxmatch as $bm) {
        $input = $bm['source'];
        $output = $bm['target'];
        $weight = $bm['weight'];
        $type = $bm['type'];
        // add the public files to the user database
        query("INSERT INTO `inibyt`.`userlinks`
          (`userid`, `source`, `type`, `target`, `weight`) VALUES(?,?,?,?,?)",
          $_SESSION["id"], $input, $type, $output, $weight);

          $update = "Links updated.";
        }
      }
      else {
        $update = "No such file.";
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

      // get a list of all the uploaded files:
      $newuploads = query("SELECT DISTINCT `box` FROM `files`");

      $uploads = [];
      // extract the box name from each file
      foreach ($newuploads as $nu) {
        $uploads[] = $nu["box"];
      }

      $news = "Online: ".implode(", ", $uploads);


      // render home page
      render("home.php", ["title" => "Get Xcytd", "checked" => $newabstraction,
      "fixed" => $newfixation, "message" => $update, "news" => $news ]);
    }

?>
