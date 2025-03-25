<?php
session_start();
$host = "localhost";
$db_user = "root";       // Update with your DB user
$db_pass = "";           // Update with your DB password
$db_name = "quickbil";

$conn = new mysqli($host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>