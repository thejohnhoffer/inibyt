# Inibyt

This is my first CS project, as a part of CS50 circa 2014.

## Dependencies

You'll want to install PHP 7 and MySQL. You'll need MySQL running locally.

## Building the local database

In MySQL console `mysql -u admin -p`, run the following sql:

```
CREATE DATABASE inibyt;
CREATE USER 'admin'@'localhost' IDENTIFIED WITH mysql_native_password BY 'password';
CREATE TABLE inibyt.users ( id int PRIMARY KEY AUTO_INCREMENT, username varchar(255), hash varchar(255), fixed varchar(255), abstract varchar(255) );
CREATE TABLE inibyt.userlinks ( linkid int PRIMARY KEY AUTO_INCREMENT, userid int, source varchar(255), type varchar(255), target varchar(255), weight int );
CREATE TABLE inibyt.files ( id int PRIMARY KEY AUTO_INCREMENT, source varchar(255), type varchar(255), target varchar(255), box varchar(255), weight int );
GRANT ALL PRIVILEGES ON inibyt.* TO 'admin'@'localhost' WITH GRANT OPTION;
```

## Running PHP

`php -S 127.0.0.1:8000`

Then open a browser to `localhost:8000`
