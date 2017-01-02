<?php

// configuration
require("../includes/config.php");

// if user reached page via POSTing a file
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // move uploaded file to upload directory
    if ( move_uploaded_file ($_FILES['json'] ['tmp_name'],
    "../uploads/{$_FILES['json'] ['name']}")  ){

        // open the file
        $json = file_get_contents("../uploads/{$_FILES['json'] ['name']}", "r");

        // save the file as an array
        $data = json_decode($json,true);

        // Go through the file
        foreach ($data as $key=>$datum) {

            $weight = 100;
            $type = "excite"; 
            $source = $datum["source"];
            $target = $datum["target"];
            if (array_key_exists('weight', $datum)) {
                $weight = $datum["weight"];
            }
            if (array_key_exists('type', $datum)) {
                $excites = array("0","excite","excites");
                if (!in_array($datum["type"], $excites)){
                    $type = "inhibit";
                }
            }
            // insert the data into the user's database
            query("INSERT INTO `inibyt`.`userlinks` 
            (`source`, `target`, `type`, `weight`, `linkid`, `userid`)
            VALUES (?, ?, ?, ?, NULL, ?)",$source, $target, $type, $weight, $_SESSION["id"]);
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
        // rerender the page
        render("add_data.php", ["title" => "New Data", "notice" => "Make Connections.", "checked" => $abstract,
        "fixed" => $fixation ]);
    }
    else{
        switch ($_FILES['json'] ['error']) {
            case 1:
            apologize("Unable to upload file.");
                   break;
            case 2:
            apologize("Unable to upload file.");
                   break;
            case 3:
            apologize("The file was incompletely uploaded.");
                   break;
            case 4:
            apologize("No file was uploaded.");
                   break;
            default:
            apologize("Error uploading file.");
        }
    }
}
else {
    // select all user data from the user's database
    $result = query("SELECT * FROM `inibyt`.`userlinks`
          WHERE `userid`=?", $_SESSION["id"]);


    // print the resulting associative array
    print(json_encode($result, JSON_PRETTY_PRINT));
}

?>
