<?php

    // configuration
    require("../includes/config.php");

    // if user reached page via GET (as by clicking a link or via redirect)
    if ($_SERVER["REQUEST_METHOD"] == "GET")
    {
        // else render form
        render("register_form.php", ["title" => "Register"]);
    }

    // else if user reached page via POST (as by submitting a form via POST)
    else if ($_SERVER["REQUEST_METHOD"] == "POST")
    {
        // check if no username
        if (empty($_POST["username"]))
        {
            apologize("You must select a username.");
        }
        // check if no password
        else if (empty($_POST["password"]))
        {
            apologize("You must create a password.");
        }

        // check if passwords don't match
        if ($_POST["confirmation"] != $_POST["password"])
        {
            apologize("Your passwords don't match. Try again!");
        }

        // try to add user to database
        $result = query("INSERT INTO users (username, hash) VALUES(?, ?)", $_POST["username"], password_hash($_POST["password"], PASSWORD_DEFAULT));

        // check if username is taken
        if ($result === false)
        {
            apologize("The selected username is already taken.");
        }

        // remember the new user id in current session
        $rows = query("SELECT LAST_INSERT_ID() AS id");
        $_SESSION["id"] = $rows[0]["id"];

        // redirect to portfolio
        redirect("./index.php");
    }

?>
