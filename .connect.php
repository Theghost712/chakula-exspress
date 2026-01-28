<?php
$host = "localhost";
$user = "root";
$pass = "@ghost712eve!.";
$dbname = "food_ordering_db";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>