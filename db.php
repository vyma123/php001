<?php
$server_name = "localhost";
$username = "root";
$password = "";
$dbname = "php001";

$conn = new mysqli($server_name, $username, $password, $dbname);

if($conn->connect_error){
    die("connection failed". $conn->connect_error);
}
?>