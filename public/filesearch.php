<?php

    // configuration
    require("../includes/config.php");

    // define the request string
    $byt = $_SERVER['QUERY_STRING'];

    // HTML decode a possible equals sign
    $byt = str_replace("%3D", "=", $byt);
    // HTML remove all possible spaces
    $byt = str_replace("%20", "", $byt);
    $byt = str_replace("+", "", $byt);

    // Remove the query term before the equals sign
    $byt = substr($byt, strpos($byt, "=") + 1);

    // change all periods to groupmarker bqxgp
    $byt = str_replace(".","bqxgp", $byt);

    /*
     * What if there's only one word?
     */

    // search for a partial box match.
    $result = query("SELECT DISTINCT `box` FROM `inibyt`.`files` WHERE `box` LIKE ?", $byt.'%');

    // change all groupmarkers bqxgp back to periods
    $result = (json_encode($result, JSON_PRETTY_PRINT));
    $result = str_replace("bqxgp",".", $result);

    // print the resulting associative array
    print($result);
?>
