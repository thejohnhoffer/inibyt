<?php

    // configuration
    require("../includes/config.php");

    // define the request string
    $byt = $_SERVER['QUERY_STRING'];

    // HTML decode a possible equals sign
    $byt = str_replace("%3D", "=", $byt);
    // HTML decode all possible spaces
    $byt = str_replace("%20", "+", $byt);

    // remove s from the end of verbs
    $byt = str_replace("excites", "excite", $byt);
    // HTML decode a possible equals sign
    $byt = str_replace("inhibits", "inhibit", $byt);

    // Remove the query term before the equals sign
    $byt = substr($byt, strpos($byt, "=") + 1);

    // change all periods to groupmarker bqxgp
    $byt = str_replace(".","bqxgp", $byt);

    /*
     * What if there's only one word?
     */
    if (strpos($byt, '+') === false) {
        // search for a source cell match...
        $result = query("SELECT * FROM `inibyt`.`userlinks`
          WHERE `userid` = ? AND (`source` = ? OR `source` LIKE ?)",
          $_SESSION["id"], $byt, '%bqxgp'.$byt);


        // otherwise, search for a partial source cell match...
        if(empty($result)) {
          $result = query("SELECT * FROM `inibyt`.`userlinks`
            WHERE `userid` = ? AND (`source` LIKE ? OR `source` LIKE ?)",
            $_SESSION["id"], $byt.'%', '%bqxgp'.$byt.'%');
        }

        // otherwise, search for a target cell match...
        if(empty($result)) {
          $result = query("SELECT * FROM `inibyt`.`userlinks`
            WHERE `userid` = ? AND (`target` = ? OR `target` LIKE ?)",
            $_SESSION["id"], $byt, '%bqxgp'.$byt);
        }

        // otherwise, search for a partial target cell match...
        if(empty($result)) {
        $result = query("SELECT * FROM `inibyt`.`userlinks`
          WHERE `userid` = ? AND (`target` LIKE ? OR `target` LIKE ?)",
          $_SESSION["id"], $byt.'%', '%bqxgp'.$byt.'%');
        }

        // otherwise, search for a partial type match
        if(empty($result)) {
        $result = query("SELECT * FROM `inibyt`.`userlinks`
                  WHERE `userid` = ? AND `type` LIKE ?",
                  $_SESSION["id"], $byt.'%');
        }

        $isgroup = [];
        foreach ($result as $link) {
          // find the position of the group marker
          $groupend = stripos($link["source"], 'bqxgp');
          // record the name of the group
          $group = substr($link["source"], 0, $groupend);
          // if there is a group in the source of the link
          if ($groupend !== false && array_search($group,$isgroup) === false) {
            // record the next key needed for array
            $key = sizeof($result);
            $gresult = array (
                $key => array(
                  "source" => $group,
                  "target" => "a group",
                  "type" => "i",
                  "linkid" => $group,
                  "userid" => $group
                )
            );
            $result = $result + $gresult;
            array_push($isgroup,$group);
          }
        }
        // show the groups first
        $result = array_reverse($result);
    }
    /*
     *  What if there is only one space?
     */
    else if (strrpos($byt, '+') === strpos($byt, '+')){
        $byt1 = substr($byt,0,strpos($byt, '+'));
        $byt2 = substr($byt,strpos($byt, '+')+1);

        // search for an exact ordered source-target match
        $result = query("SELECT * FROM
          `inibyt`.`userlinks` WHERE `userid` = ?
          AND (`source` = ? OR `source` LIKE ?)
          AND (`target` = ? OR `target` LIKE ?)",
          $_SESSION["id"], $byt1, '%bqxgp'.$byt1,
          $byt2, '%bqxgp'.$byt2);

        if(empty($result)) {
        // search for a partial ordered source-target match
        $result = query("SELECT * FROM
          `inibyt`.`userlinks` WHERE `userid` = ?
          AND (`source` LIKE ? OR `source` LIKE ?)
          AND (`target` LIKE ? OR `target` LIKE ?)",
          $_SESSION["id"], $byt1.'%', '%bqxgp'.$byt1.'%',
          $byt2.'%', '%bqxgp'.$byt2.'%');
        }

        if(empty($result)) {
        // search for an exact reverse source-target match
        $result = query("SELECT * FROM
          `inibyt`.`userlinks` WHERE `userid` = ?
          AND (`source` = ? OR `source` LIKE ?)
          AND (`target` = ? OR `target` LIKE ?)",
          $_SESSION["id"], $byt2, '%bqxgp'.$byt2,
          $byt1, '%bqxgp'.$byt1);
        }

        if(empty($result)) {
        // search for a partial reverse source-target match
        $result = query("SELECT * FROM
          `inibyt`.`userlinks` WHERE `userid` = ?
          AND (`source` LIKE ? OR `source` LIKE ?)
          AND (`target` LIKE ? OR `target` LIKE ?)",
          $_SESSION["id"], $byt2.'%', '%bqxgp'.$byt2.'%',
          $byt1.'%', '%bqxgp'.$byt1.'%');
        }

        if(empty($result)) {
          // search for an exact ordered source-type match
          $result = query("SELECT * FROM
            `inibyt`.`userlinks` WHERE `userid` = ?
            AND (`source` = ? OR `source` LIKE ?)
            AND `type` LIKE ?",
            $_SESSION["id"], $byt1, '%bqxgp'.$byt1,
            $byt2.'%');
          }

        if(empty($result)) {
          // search for a partial ordered source-type match
          $result = query("SELECT * FROM
            `inibyt`.`userlinks` WHERE `userid` = ?
            AND (`source` LIKE ? OR `source` LIKE ?)
            AND `type` LIKE ?",
            $_SESSION["id"], $byt1.'%', '%bqxgp'.$byt1.'%',
            $byt2.'%');
          }
    }
    /*
     *  What if there are two or more spaces?
     */
    else {
        $space1 = strpos($byt, '+');
        $space2 = strrpos($byt, '+');
        $byt1 = substr($byt,0,$space1);
        $byt2 = substr($byt,$space1+1, $space2-2);
        $byt3 = substr($byt,$space2+1);

        // search for an exact match..
        $result = query("SELECT * FROM `inibyt`.`userlinks`
                  WHERE `userid` = ?
                  AND (`source` = ? OR `source` LIKE ?)
                  AND `type` LIKE ?
                  AND (`target` = ? OR `target`LIKE ?)",
                  $_SESSION["id"], $byt1, '%bqxgp'.$byt1,
                  $byt2.'%', $byt3, '%bqxgp'.$byt3);

        if (empty($result)) {
          // search for a partial match
          $result = query("SELECT * FROM `inibyt`.`userlinks`
            WHERE `userid` = ?
            AND (`source` LIKE ? OR `source` LIKE ?)
            AND `type` LIKE ?
            AND (`target` LIKE ? OR `target`LIKE ?)",
            $_SESSION["id"], $byt1.'%', '%bqxgp'.$byt1.'%',
            $byt2.'%', $byt3.'%', '%bqxgp'.$byt3.'%');
        }
    }

    // make an elimination suggestion
    if (strpos($byt,"all") !== false) {
      array_push($result, array(
        "source" => "this",
        "type" => "eliminate",
        "target" => "all"
      ));
    }

    //if (strpos($byt))

    // change all groupmarkers bqxgp back to periods
    $result = (json_encode($result, JSON_PRETTY_PRINT));
    $result = str_replace("bqxgp",".", $result);

    // print the resulting associative array
    print($result);
?>
