<?php
$host = '127.0.0.1';
$username = 'emojitalk_user';
$password = 'emojitalk123';
$database = 'emojitalk_beta';

// mit Datenbank verbinden
$mysqli = new \mysqli($host, $username, $password, $database);

// fehlermeldung, falls verbindung fehl schlägt.
if ($mysqli->connect_error) {
 die('Connect Error (' . $mysqli->connect_errno . ') '. $mysqli->connect_error);
}
?>