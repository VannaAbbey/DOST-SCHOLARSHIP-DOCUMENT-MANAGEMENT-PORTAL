<?php
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
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $last_name = $_POST['last_name'];
    $birthdate = $_POST['birthdate'];
    $cont_num = $_POST['cont_num'];
    $email = $_POST['email'];
    $password = $_POST['password']; // Not hashing the password as per request
    $role = $_POST['role'];
    $student_number = $_POST['student_number'] ?? null;
    $scholarship_classification = $_POST['scholarship_classification'] ?? null;
    $year_level = $_POST['year_level'] ?? null;
    $semester = $_POST['semester'] ?? null;
    $section = $_POST['section'] ?? null;
    $personnel_number = $_POST['personnel_number'] ?? null;

    // Check if the email already exists
    $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "Error: Email already exists.";
        $stmt->close();
        $conn->close();
        exit();
    }
    $stmt->close();

    // Check if the student number or personnel number already exists
    if ($role == 'student' && $student_number) {
        $stmt = $conn->prepare("SELECT student_number FROM student WHERE student_number = ?");
        $stmt->bind_param("s", $student_number);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            echo "Error: Student number already exists.";
            $stmt->close();
            $conn->close();
            exit();
        }
        $stmt->close();
    } elseif ($role == 'personnel' && $personnel_number) {
        $stmt = $conn->prepare("SELECT personnel_number FROM personnel1 WHERE personnel_number = ?");
        $stmt->bind_param("s", $personnel_number);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            echo "Error: Personnel number already exists.";
            $stmt->close();
            $conn->close();
            exit();
        }
        $stmt->close();
    }

    // Insert into users table
    $stmt = $conn->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $email, $password, $role);

    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;

        if ($role == 'student') {
            // Insert into student table
            $stmt = $conn->prepare("INSERT INTO student (user_id, firstname, middlename, lastname, birthdate, email, cont_num, student_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssssis", $user_id, $first_name, $middle_name, $last_name, $birthdate, $email, $cont_num, $student_number);
            $stmt->execute();

            // Insert into scholarship table
            if (!empty($scholarship_classification)) {
                $stmt = $conn->prepare("INSERT INTO scholarship (student_number, scholarship_classification) VALUES (?, ?)");
                $stmt->bind_param("ss", $student_number, $scholarship_classification);
                $stmt->execute();
            }

            // Insert into year_level table
            if (!empty($year_level) && !empty($semester) && !empty($section)) {
                $stmt = $conn->prepare("INSERT INTO year_level (year, section, semester, student_number) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("siss", $year_level, $section, $semester, $student_number);
                $stmt->execute();
            }

            // Set session and redirect to student_dashboard.php
            session_start();
            $_SESSION['user_id'] = $user_id;
            $_SESSION['role'] = $role;
            header("Location: student_dashboard.php");
            exit();

        } elseif ($role == 'personnel') {
            // Insert into personnel table
            $stmt = $conn->prepare("INSERT INTO personnel1 (user_id, firstname, middlename, lastname, birthdate, email, cont_num, personnel_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssssis", $user_id, $first_name, $middle_name, $last_name, $birthdate, $email, $cont_num, $personnel_number);
            $stmt->execute();

            // Set session and redirect to personnel_dashboard.php
            session_start();
            $_SESSION['user_id'] = $user_id;
            $_SESSION['role'] = $role;
            header("Location: personnel_dashboard.php");
            exit();
        }
    } else {
        echo "Error: " . $stmt->error;
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
    <title>SignUp Page</title>
    <link rel="stylesheet" type="text/css" href="style_signup.css">
    <style>
        #submit:hover {
            cursor: pointer;
            background-color: #3b4f62;
        }
        #submit{
            color: white;
            margin-top: 6px;
            background: #1582aa;
            padding: 8px;
            font-size: 14px;
            width: 100%;
            align-self: center;
            font-family:'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;
            border-radius: 5px;
            border:none;
        }

        .privacy {
            margin-top: 30px;
            margin-bottom: 0px;
            font-size: 10px;
        }
        .privacy a {
            color: #1582aa;
            cursor: pointer;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4);
            padding-top: 60px;
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
    <script>
        function showFields(role) {
            var studentFields = document.getElementById('student_fields');
            var personnelFields = document.getElementById('personnel_fields');

            if (role === 'student') {
                studentFields.style.display = 'block';
                personnelFields.style.display = 'none';
            } else if (role === 'personnel') {
                personnelFields.style.display = 'block';
                studentFields.style.display = 'none';
            } else {
                studentFields.style.display = 'none';
                personnelFields.style.display = 'none';
            }
        }

        // Modal functionality
        function showModal() {
            var modal = document.getElementById("myModal");
            modal.style.display = "block";
        }

        function closeModal() {
            var modal = document.getElementById("myModal");
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            var modal = document.getElementById("myModal");
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</head>
<body style="background: url(bg.png); background-repeat:no-repeat; background-size: 100% 120%;">
<section class="container">
    <header>
        <span class="color-blue">Registration</span>
        <span class="color-grey">Form</span>
    </header>
    <form class="form" action="signup.php" method="POST">
        <div class="input-box">
            <label>First Name: </label>
            <input type="text" id="first_name" name="first_name" class="box"><br>
        </div>
        <div class="input-box">
            <label>Middle Name: </label>
            <input type="text" id="middle_name" name="middle_name" class="box"><br>
        </div>
        <div class="input-box">
            <label>Last Name: </label>
            <input type="text" id="last_name" name="last_name" class="box"><br>
        </div>
        <div class="input-box">
            <label>Birthdate: (dd/mm/yyyy) </label>
            <input type="date" id="birthdate" name="birthdate" class="box"><br>
        </div>
        <div class="input-box">
            <label>Contact Number: </label>
            <input type="text" id="cont_num" name="cont_num" class="box"><br>
        </div>
        <div class="input-box">
            <label>Email: </label>
            <input type="email" id="email" name="email" class="box"><br>
        </div>
        <div class="input-box">
            <label>Password: </label>
            <input type="password" id="password" name="password" class="box"><br>
        </div>
        <div class="input-box">
            <label>Role: </label>
            <select id="role" name="role" class="box" onchange="showFields(this.value)">
                <option value="">Select Role</option>
                <option value="student">Student</option>
                <option value="personnel">DOST Personnel</option>
            </select><br>
        </div>
        <div class="input-box" id="student_fields" style="display: none;">
            <label>Student Number: </label>
            <input type="text" id="student_number" name="student_number" class="box"><br>
            <br><label>Scholarship Classification: </label>
            <input type="text" id="scholarship_classification" name="scholarship_classification" class="box"><br>
            <br><label>Year Level: </label>
            <select id="year_level" name="year_level" class="box">
                <option value="">Select Year Level</option>
                <option value="First Year">First Year</option>
                <option value="Second Year">Second Year</option>
                <option value="Third Year">Third Year</option>
                <option value="Fourth Year">Fourth Year</option>
                <option value="Fifth Year">Fifth Year</option>
            </select><br>
            <br><label>Semester: </label>
            <select id="semester" name="semester" class="box">
                <option value="">Select Semester</option>
                <option value="First Semester">First Semester</option>
                <option value="Second Semester">Second Semester</option>
            </select><br>
            <br><label>Section: </label>
            <input type="text" id="section" name="section" class="box"><br>
        </div>
        <div class="input-box" id="personnel_fields" style="display: none;">
            <label>Personnel Number: </label>
            <input type="text" id="personnel_number" name="personnel_number" class="box"><br>
        </div>
        <div class="input-box">
            <input type="submit" id="submit" value="Submit">
        </div>
    </form>

    <div class="privacy">
        <label>By signing up, you acknowledge our</label>
        <a onclick="showModal()">Privacy Policy</a>
    </div>
</section>

<!-- The Modal -->
<div id="myModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Privacy Policy</h2>
        <p>This is a simple privacy policy.</p>
        <p>We value your privacy and are committed to protecting your personal information. We collect information that you provide to us, including your name, email address, and other details. This information is used solely for the purpose of registration and providing our services to you.</p>
        <p>We do not share your information with third parties without your consent, except as required by law.</p>
        <p>By using this site, you agree to our privacy policy.</p>
    </div>
</div>

</body>
</html>
