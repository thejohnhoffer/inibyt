<?php

    // configuration
    require("../includes/config.php");

    // define the request string
    $byt = $_SERVER['QUERY_STRING'];

    // HTML decode a possible equals sign
    $byt = str_replace("%3D", "=", $byt);
    // HTML decode all possible spaces
    $byt = str_replace("%20", "+", $byt);

    // Remove the query term before the equals sign
    $byt = substr($byt, strpos($byt, "=") + 1);

    // change all periods to groupmarker bqxgp
    $byt = str_replace(".","bqxgp", $byt);

    // set the default connection strength
    $byt4 = " 10mV";

    /*
     * What if there's only one word?
     */
    if (strpos($byt, '+') === false) {
      // search for a partial source cell match...
      $result = query("SELECT DISTINCT `source` FROM `inibyt`.`userlinks`
                WHERE `userid` = ? AND (`source` LIKE ? OR `source` LIKE ?)",
                $_SESSION["id"], $byt.'%', '%bqxgp'.$byt.'%');

      // if no sources match that
      if (empty($result)) {
        // search for all possible source matches in current targets
        $result = query("SELECT DISTINCT `target` FROM `inibyt`.`userlinks`
          WHERE `userid` = ? AND (`target` LIKE ? OR `source` LIKE ?)",
          $_SESSION["id"], $byt.'%', '%bqxgp'.$byt.'%');
        // change the label from target to source
        $result = array_map(function($result) {
          return array(
            'source' => $result['target']
          );
        }, $result);
      }

      if (!empty($result)) {
        // fill in suggestive text
        $max = count($result);
        for($i = 0; $i < $max; $i++) {
          $iresult[$i]["source"] = $result[$i]["source"];
          $iresult[$i]["type"] = "inhibit";
          $iresult[$i]["target"] = "new(new)";
        }
        // fill in suggestive text
        for($i = 0; $i < $max; $i++) {
          $eresult[$i]["source"] = $result[$i]["source"];
          $eresult[$i]["type"] = "excite";
          $eresult[$i]["target"] = "new(new)";
        }
        $result = array_merge( $eresult , $iresult );
      }
      else {
        // otherwise, try for a type match...
        if(strcasecmp($byt[0],"e") === 0) {
          // fill in suggestive text
          $result[0]["type"] = "excite";
          $result[0]["source"] = "new(new)";
          $result[0]["target"] = "new(new)";
        }
        else if (strcasecmp($byt[0],"i") === 0) {
          // fill in suggestive text
          $result[0]["type"] = "inhibit";
          $result[0]["source"] = "new(new)";
          $result[0]["target"] = "new(new)";
        }
        else {
          // fill in suggestive text
          $result[0]["type"] = "excite";
          $result[0]["source"] = $byt."(new)";
          $result[0]["target"] = "new(new)";
          // fill in suggestive text
          $result[1]["type"] = "inhibit";
          $result[1]["source"] = $byt."(new)";
          $result[1]["target"] = "new(new)";
        }
      }
      // append voltage placeholders
      $resmax = sizeof($result);
      for ($link = 0; $link < $resmax; $link++) {
        $result[$link]['weight'] = $byt4;
      }
    }
    /*
     * What if there are two words?
     */
    else if (strrpos($byt, '+') === strpos($byt, '+')) {
      $byt1 = substr($byt,0,strpos($byt, '+'));
      $byt2 = substr($byt,strpos($byt, '+')+1);

      if ($byt1 == null){
        $byt1= "";
      }
      if ($byt2 == null){
        $byt2= "";
      }

      // search for exact source matches
      $sresult = query("SELECT DISTINCT `source` FROM `inibyt`.`userlinks`
        WHERE `userid` = ? AND (`source` = ? OR `source` LIKE ?)",
        $_SESSION["id"], $byt1, '%bqxgp'.$byt1);

      // if no sources match that
      if (empty($sresult)) {
        // search for all possible source matches in current targets
        $sresult = query("SELECT DISTINCT `target` FROM `inibyt`.`userlinks`
          WHERE `userid` = ? AND (`target` = ? OR `target` LIKE ?)",
          $_SESSION["id"], $byt1, '%bqxgp'.$byt1);
        // change the label from target to source
        $sresult = array_map(function($sresult) {
          return array(
            'source' => $sresult['target']
          );
        }, $sresult);
      }

      // search for all possible target matches
      $tresult = query("SELECT DISTINCT `target` FROM `inibyt`.`userlinks`
        WHERE `userid` = ? AND (`target` = ? OR `target` LIKE ?)",
        $_SESSION["id"], $byt2, '%bqxgp'.$byt2);

      // if no targets match that
      if (empty($tresult)) {
        // search for all possible target matches in current sources
        $tresult = query("SELECT DISTINCT `source` FROM `inibyt`.`userlinks`
          WHERE `userid` = ? AND (`source` = ? OR `source` LIKE ?)",
          $_SESSION["id"], $byt2, '%bqxgp'.$byt2);
        // change the label from target to source
        $tresult = array_map(function($tresult) {
          return array(
            'target' => $tresult['source']
          );
        }, $tresult);
      }

      // if there are no targets or sources like that....
      if (empty($tresult) || empty($sresult)) {
        // but if there are some sources like that
        if (!empty($sresult)){
          $smax = count($sresult);
          for($s = 0; $s < $smax; $s++) {
            $iresult[$s]["type"] = "inhibit";
            $iresult[$s]["target"] = $byt2."(new)";
            $iresult[$s]["source"] = $sresult[$s]["source"];
          }
          for($s = 0; $s < $smax; $s++) {
            $eresult[$s]["type"] = "excite";
            $eresult[$s]["target"] = $byt2."(new)";
            $eresult[$s]["source"] = $sresult[$s]["source"];
          }
          $result = array_merge( $eresult , $iresult );
        }
        // or if there are some targets like that
        else if (!empty($tresult)){
          $tmax = count($tresult);
          for($t = 0; $t < $tmax; $t++) {
            $iresult[$t]["type"] = "inhibit";
            $iresult[$t]["source"] = $byt1."(new)";
            $iresult[$t]["target"] = $tresult[$t]["target"];
          }
          for($t = 0; $t < $tmax; $t++) {
            $eresult[$t]["type"] = "excite";
            $eresult[$t]["source"] = $byt1."(new)";
            $eresult[$t]["target"] = $tresult[$t]["target"];
          }
          $result = array_merge( $eresult , $iresult );
        }
        // if neither
        else {
          // fill in suggestive text
          $result[0]["source"] = $byt1."(new)";
          $result[0]["type"] = "excite";
          $result[0]["target"] = $byt2."(new)";
          // fill in suggestive text
          $result[1]["source"] = $byt1."(new)";
          $result[1]["type"] = "inhibit";
          $result[1]["target"] = $byt2."(new)";
        }
      }
      // if there are both targets and sources...
      else {
        // fill in suggestive text
        $tmax = count($tresult);
        $smax = count($sresult);
        $count = 0;

        for($t = 0; $t < $tmax; $t++) {
          for($s = 0; $s < $smax; $s++) {
            $iresult[$count]["type"] = "inhibit";
            $iresult[$count]["source"] = $sresult[$s]["source"];
            $iresult[$count]["target"] = $tresult[$t]["target"];
            $count++;
          }
        }
        $count = 0;
        for($t = 0; $t < $tmax; $t++) {
          for($s = 0; $s < $smax; $s++) {
            $eresult[$count]["type"] = "excite";
            $eresult[$count]["source"] = $sresult[$s]["source"];
            $eresult[$count]["target"] = $tresult[$t]["target"];
            $count++;
          }
        }
        $result = array_merge( $eresult , $iresult );
      }
      // append voltage placeholders
      $resmax = sizeof($result);
      for ($link = 0; $link < $resmax; $link++) {
        $result[$link]['weight'] = $byt4;
      }
    }
    /*
    * What if there are more than two words?
    */
    else {
      $byts = explode("+", $byt);
      $byt1 = $byts[0];
      $byt2 = $byts[1];
      $byt3 = $byts[2];
      if (sizeof($byts) > 3) {
        $byt4 = " ".$byts[3]."mV";
      }

      // search for a source cell match...
      $sresult = query("SELECT DISTINCT `source` FROM `inibyt`.`userlinks`
        WHERE `userid` = ? AND (`source` = ? OR `source` LIKE ?)",
        $_SESSION["id"], $byt1, '%bqxgp'.$byt1);

      // if no sources match that
      if (empty($sresult)) {
        // search for all possible source matches in current targets
        $sresult = query("SELECT DISTINCT `target` FROM `inibyt`.`userlinks`
          WHERE `userid` = ? AND (`target` = ? OR `target` LIKE ?)",
          $_SESSION["id"], $byt1, '%bqxgp'.$byt1);
        // change the label from target to source
        $sresult = array_map(function($sresult) {
          return array(
            'source' => $sresult['target']
          );
        }, $sresult);
      }

      // search for a  target cell match...
      $tresult = query("SELECT DISTINCT `target` FROM `inibyt`.`userlinks`
        WHERE `userid` = ? AND (`target` = ? OR `target` LIKE ?)",
        $_SESSION["id"], $byt3, '%bqxgp'.$byt3);

      // if no sources match that
      if (empty($tresult)) {
        // search for all possible target matches in current sources
        $tresult = query("SELECT DISTINCT `source` FROM `inibyt`.`userlinks`
          WHERE `userid` = ? AND (`source` = ? OR `source` LIKE ?)",
          $_SESSION["id"], $byt3, '%bqxgp'.$byt3);
          // change the label from target to source
          $tresult = array_map(function($tresult) {
            return array(
              'target' => $tresult['source']
            );
          }, $tresult);
        }


      // if the middle word begins with e
      if (strcasecmp($byt2[0],"e")==0){

        // if there are no targets or sources like that....
        if (empty($tresult) || empty($sresult)) {
          // but if there are some sources like that
          if (!empty($sresult)){
            $smax = count($sresult);
            for($s = 0; $s < $smax; $s++) {
              $result[$s]["type"] = "excite";
              $result[$s]["target"] = $byt3."(new)";
              $result[$s]["source"] = $sresult[$s]["source"];
            }
          }
          // or if there are some targets like that
          else if (!empty($tresult)){
            $tmax = count($tresult);
            for($t = 0; $t < $tmax; $t++) {
              $result[$t]["type"] = "excite";
              $result[$t]["source"] = $byt1."(new)";
              $result[$t]["target"] = $tresult[$t]["target"];
            }
          }
          // if neither
          else {
            $result[0]["source"] = $byt1."(new)";
            $result[0]["type"] = "excite";
            $result[0]["target"] = $byt3."(new)";
          }
        }
        // if there are both targets and sources...
        else {
          // fill in suggestive text
          $tmax = count($tresult);
          $smax = count($sresult);
          $count = 0;
          for($t = 0; $t < $tmax; $t++) {
            for($s = 0; $s < $smax; $s++) {
              $result[$count]["type"] = "excite";
              $result[$count]["source"] = $sresult[$s]["source"];
              $result[$count]["target"] = $tresult[$t]["target"];
              $count++;
            }
          }
        }
        // append voltage specification
        $resmax = sizeof($result);
        for ($link = 0; $link < $resmax; $link++) {
          $result[$link]['weight'] = $byt4;
        }
      }
      // if the middle word begins with i
      else if (strcasecmp($byt2[0],"i")==0){

        // if there are no targets or sources like that....
        if (empty($tresult) || empty($sresult)) {
          // but if there are some sources like that
          if (!empty($sresult)){
            $smax = count($sresult);
            for($s = 0; $s < $smax; $s++) {
              $result[$s]["type"] = "inhibit";
              $result[$s]["target"] = $byt3."(new)";
              $result[$s]["source"] = $sresult[$s]["source"];
            }
          }
          // or if there are some targets like that
          else if (!empty($tresult)){
            $tmax = count($tresult);
            for($t = 0; $t < $tmax; $t++) {
              $result[$t]["type"] = "inhibit";
              $result[$t]["source"] = $byt1."(new)";
              $result[$t]["target"] = $tresult[$t]["target"];
            }
          }
          // if neither
          else {
            $result[1]["source"] = $byt1."(new)";
            $result[1]["type"] = "inhibit";
            $result[1]["target"] = $byt3."(new)";
          }
        }
        // if there are both targets and sources...
        else {
          // fill in suggestive text
          $tmax = count($tresult);
          $smax = count($sresult);
          $count = 0;
          for($t = 0; $t < $tmax; $t++) {
            for($s = 0; $s < $smax; $s++) {
              $result[$count]["type"] = "inhibit";
              $result[$count]["source"] = $sresult[$s]["source"];
              $result[$count]["target"] = $tresult[$t]["target"];
              $count++;
            }
          }
        }
        // append voltage specification
        $resmax = sizeof($result);
        for ($link = 0; $link < $resmax; $link++) {
          $result[$link]['weight'] = $byt4;
        }
      }
      // if the middle word begins with r
      else if (strcasecmp($byt2[0],"r")==0){
        // remove the mention of any millivolts
        $byt4 = " at all links";
        // if there are no targets or sources like that....
        if (empty($tresult) || empty($sresult)) {
          // but if there are some sources like that
          if (!empty($sresult)){
            $smax = count($sresult);
            for($s = 0; $s < $smax; $s++) {
              $result[$s]["type"] = "replace";
              $result[$s]["target"] = "none";
              $result[$s]["source"] = $sresult[$s]["source"];
            }
          }
          // or if there are some targets like that
          else if (!empty($tresult)){
            $tmax = count($tresult);
            for($t = 0; $t < $tmax; $t++) {
              $result[$t]["type"] = "replace";
              $result[$t]["source"] = $byt1."(new)";
              $result[$t]["target"] = $tresult[$t]["target"];
            }
          }
          // if neither
          else {
            $result[1]["source"] = $byt1."(new)";
            $result[1]["type"] = "replace";
            $result[1]["target"] = "none";
          }
          // give placeholder values
          $resmax = sizeof($result);
          for ($link = 0; $link < $resmax; $link++) {
            $result[$link]['weight'] = $byt4;
          }
        }
        // if there are both targets and sources...
        else {
          // fill in suggestive text
          $tmax = count($tresult);
          $smax = count($sresult);
          $count = 0;
          for($t = 0; $t < $tmax; $t++) {
            for($s = 0; $s < $smax; $s++) {
              $result[$count]["type"] = "replace";
              $result[$count]["source"] = $sresult[$s]["source"];
              $result[$count]["target"] = $tresult[$t]["target"];
              $count++;
            }
          }
        }
      }
      // otherwise, if no middle letter is specified
      else {
        // if there are no targets or sources like that....
        if (empty($tresult) || empty($sresult)) {
          // but if there are some sources like that
          if (!empty($sresult)){
            $smax = count($sresult);
            for($s = 0; $s < $smax; $s++) {
              $iresult[$s]["type"] = "inhibit";
              $iresult[$s]["target"] = $byt3."(new)";
              $iresult[$s]["source"] = $sresult[$s]["source"];
            }
            for($s = 0; $s < $smax; $s++) {
              $eresult[$s]["type"] = "excite";
              $eresult[$s]["target"] = $byt3."(new)";
              $eresult[$s]["source"] = $sresult[$s]["source"];
            }
            $result = array_merge( $eresult , $iresult );
          }
          // or if there are some targets like that
          else if (!empty($tresult)){
            $tmax = count($tresult);
            for($t = 0; $t < $tmax; $t++) {
              $iresult[$t]["type"] = "inhibit";
              $iresult[$t]["source"] = $byt1."(new)";
              $iresult[$t]["target"] = $tresult[$t]["target"];
            }
            for($t = 0; $t < $tmax; $t++) {
              $eresult[$t]["type"] = "excite";
              $eresult[$t]["source"] = $byt1."(new)";
              $eresult[$t]["target"] = $tresult[$t]["target"];
            }
            $result = array_merge( $eresult , $iresult );
          }
          // if neither
          else {
            $result[0]["source"] = $byt1."(new)";
            $result[0]["type"] = "excite";
            $result[0]["target"] = $byt3."(new)";
            $result[1]["source"] = $byt1."(new)";
            $result[1]["type"] = "inhibit";
            $result[1]["target"] = $byt3."(new)";
          }
        }
        // if there are both targets and sources...
        else {
          // fill in suggestive text
          $tmax = count($tresult);
          $smax = count($sresult);
          $count = 0;
          for($t = 0; $t < $tmax; $t++) {
            for($s = 0; $s < $smax; $s++) {
              $iresult[$count]["type"] = "inhibit";
              $iresult[$count]["source"] = $sresult[$s]["source"];
              $iresult[$count]["target"] = $tresult[$t]["target"];
              $count++;
            }
          }
          $count = 0;
          for($t = 0; $t < $tmax; $t++) {
            for($s = 0; $s < $smax; $s++) {
              $eresult[$count]["type"] = "excite";
              $eresult[$count]["source"] = $sresult[$s]["source"];
              $eresult[$count]["target"] = $tresult[$t]["target"];
              $count++;
            }
          }
          $result = array_merge( $eresult , $iresult );
        }
        // append voltage specification
        $resmax = sizeof($result);
        for ($link = 0; $link < $resmax; $link++) {
          $result[$link]['weight'] = $byt4;
        }
      }
    }

    // change all groupmarkers bqxgp back to periods
    $result = (json_encode($result, JSON_PRETTY_PRINT));
    $result = str_replace("bqxgp",".", $result);

    // print the resulting associative array
    print($result);
?>
