<?php

    require_once("constants.php");

    $host = SERVER;
    $db = DATABASE;
    $user = USERNAME;
    $pass = PASSWORD;

    print("$db\n");
    try {
        $dbh = new PDO("mysql:host=$host", "root", "");

        $dbh->query("CREATE DATABASE IF NOT EXISTS `$db`;
            SET GLOBAL validate_password_length=0;
            SET GLOBAL validate_password_policy=LOW;
            SET GLOBAL validate_password_number_count=0;
            SET GLOBAL validate_password_mixed_case_count=0;
            SET GLOBAL validate_password_special_char_count=0;
            CREATE USER '$user'@'$host' IDENTIFIED BY '$pass';
            GRANT ALL PRIVILEGES ON *.* TO '$user'@'$host';
            FLUSH PRIVILEGES;")
        or print_r($dbh->errorInfo()); 

        $dbh->query("USE `$db`;
            CREATE TABLE IF NOT EXISTS users(
            id MEDIUMINT NOT NULL AUTO_INCREMENT,
            abstract varchar(255) NOT NULL DEFAULT '',
            fixed varchar(255) NOT NULL DEFAULT '0.4',
            username varchar(255) NOT NULL,
            hash varchar(255) NOT NULL,
            PRIMARY KEY (id)
        );")
        or print_r($dbh->errorInfo()); 

        $dbh->query("USE `$db`;
            CREATE TABLE IF NOT EXISTS userlinks(
            linkid MEDIUMINT NOT NULL AUTO_INCREMENT,
            weight MEDIUMINT NOT NULL DEFAULT 100,
            type varchar(255) NOT NULL DEFAULT 'excite',
            userid MEDIUMINT NOT NULL,
            source varchar(255) NOT NULL,
            target varchar(255) NOT NULL,
            PRIMARY KEY (linkid)
        );")
        or print_r($dbh->errorInfo()); 

        $dbh->query("USE `$db`;
            CREATE TABLE IF NOT EXISTS files(
            id MEDIUMINT NOT NULL AUTO_INCREMENT,
            weight MEDIUMINT NOT NULL DEFAULT 100,
            type varchar(255) NOT NULL DEFAULT 'excite',
            box varchar(255) NOT NULL,
            source varchar(255) NOT NULL,
            target varchar(255) NOT NULL,
            PRIMARY KEY (id)
        );")
        or print_r($dbh->errorInfo()); 

    } catch (PDOException $e) {
        die("DB ERROR: ". $e->getMessage());
    }
?>
