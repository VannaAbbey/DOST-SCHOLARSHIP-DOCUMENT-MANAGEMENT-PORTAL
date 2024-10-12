<?php
session_start();

// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "datab";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form data
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Prepare and execute the query
    $stmt = $conn->prepare("SELECT user_id, email, password, role FROM users WHERE email = ? AND password = ? AND role = ?");
    $stmt->bind_param("sss", $email, $password, $role);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $db_email, $db_password, $db_role);
        $stmt->fetch();

        // Set session variables
        $_SESSION['user_id'] = $user_id;
        $_SESSION['email'] = $db_email;
        $_SESSION['role'] = $db_role;

        // Redirect based on role
        if ($db_role == 'student') {
            header("Location: student_dashboard.php");
        } elseif ($db_role == 'personnel') {
            header("Location: personnel_dashboard.php");
        }
        exit();
    } else {
        echo "Error: Invalid email, password, or role.";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <style>
        #btn:hover {
            cursor: pointer;
            background: #3b4f62;
        }
    </style>
</head>
<body style="background: url(bg.png); background-repeat: no-repeat; background-size: 100% 120%">
<div class="container">
    <form class="form" action="login.php" method="POST">
        <h1 class="header">
            <span class="color-blue">Login</span>
            <span class="color-grey"> into account</span>
        </h1>
        <label1>Email: </label1> 
        <input type="email" id="email" name="email" class="box" required><br>
        <label2>Password: </label2> 
        <input type="password" id="password" name="password" class="box" required><br>
        <label3>Role: </label3> 
        <select id="role" name="role" class="box" required>
            <option value="student">Student</option>
            <option value="personnel">DOST Personnel</option>
        </select><br><br>
        <input type="submit" id="btn" value="Login" name="submit"><br><br>
        <div class="newuser">
            <label4>New User? </label4>
            <a href="signup.php">Sign up here</a>
        </div>
    </form>
    <div class="side">
        <img src="img1.png" alt="">
    </div>
</div>
</body>
</html>
