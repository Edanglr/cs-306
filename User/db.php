<?php
$host = "127.0.0.1";
$user = "root";
$pass = "";   
$db   = "cs306_project";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("DB connection failed: " . mysqli_connect_error());
}
?>
