<?php
include '.connect.php';

if (isset($_POST['signUp'])) {
    $fullname = $_POST['fullname'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password =$_POST['password'];
    $password=md5($password);

    $checkEmail = "SELECT * FROM users where email='$email'";
    $result = $conn->query($checkEmail);


    if ($result->num_rows > 0) {
        echo "Email already exists!";
    
    } else {
        $insertQuery ="INSERT INTO users (fullname, username, email, password) VALUES ('$fullname', '$username', '$email', '$password')";
    }

        if ($conn->query($insertQuery) === TRUE) {
            echo "Registration successful!";
        } else {
        echo "Error: " . $conn->error;
    }
    if(isset($_POST['signIn'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];
        $password = md5($password);
        $sql ="SELECT * FROM users WHERE email = '$email' AND password = '$password'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            session_start();
            $row = $result->fetch_assoc();
            $_SESSION['email'] =$row['email'];
            header("location: homepage.php");
            exit();

        } else {
            echo "Not found Invalid email or password!.";
        }
    }
}?>