<?php
session_start();
include '.connect.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage</title>
</head>
<div style="text-align: center; padding-top: 15%;">
    <p style="font-size:50px; font-weight: bold;">
        hellow <?php
        if(isset ($_SESSION['email'])) {
            $email=$_SESSION['email'];
            $query=mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
            
            while($row=mysqli_fetch_array($query)){
                echo $row['username'];
            }
        }
        ?>
    </p>
</div>

<body>
