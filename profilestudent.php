<?php
// Start the session
session_start();

// Check if the user is logged in and has the 'student' role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php"); // Redirect to login page if not logged in or not a student
    exit();
}

// Include the database connection
include 'db_connection.php';

// Get the student's data
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT s.firstname, s.middlename, s.lastname, s.birthdate, s.email, s.cont_num, s.student_number, sc.scholarship_classification, yl.year, yl.semester, yl.section
                        FROM student s
                        LEFT JOIN scholarship sc ON s.student_number = sc.student_number
                        LEFT JOIN year_level yl ON s.student_number = yl.student_number
                        WHERE s.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $student = $result->fetch_assoc();
} else {
    echo "No student data found.";
    exit();
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: white;
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .container {
            width: 60%;
            padding: 15px;
            background: #c8e1f5;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px; /* Added margin to separate from navbar and container2 */
            margin-bottom: 100px; /* Adding margin to the bottom */
            color:black;
        }
        .navbar {
            overflow: hidden;
            background-color: #a2cfee;
            position: fixed;
            top: 0;
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 100px;
            height: 50px;
            
        }
        .navbar a {
            display: block;
            color: black;
            text-align: center;
            padding: 14px 16px;
            text-decoration: none;
            font-family:'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;
            font-weight: 200;
            font-size: 15px;
        }
        .navbar a:hover {
            background-color: #f2f2f2;
            color: black;
            height:30px;
        }
        .navbar .title {
            flex-grow: 1;
            text-align: center;
            font-size: 20px;
            color: #f2f2f2;
        }
        #container2 {
            background-image: url(bgprofile.png);
            padding: 15px;
            border-radius: 10px;
            margin: auto;
            width: 60%;
            text-align: center;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 120px; /* Adjusted margin for space below navbar */
            margin-bottom: 20px; 
            height: 600px;
            color:black;
        }
        #container2 h2{
            margin-top: 30px;
        }
       
        .info {
            margin-top: 0px; /* Added margin to separate from container2 */
        }
        .info p {
            padding-top: 10px;
            margin-left: 100px;
        }
        .info p4{
            margin-top: 30px;
            margin-left: 100px;
        }
        .info .l1, .info .l2 {
            display: flex;
            justify-content: space-between;
        }
        .l1 p1{
            margin-top: 10px;
            margin-left: 100px;
        }
        .l1 p2 {
            margin-top: 10px;
            margin-right: 300px; /* Adjust the right margin as needed */
        }
        .l1 p3 {
            margin-top: 25px;
            margin-right: 239px; /* Adjust the right margin as needed */
        }
        .l1 p5 {
            margin-top: 25px;
            margin-left: 100px; /* Adjust the right margin as needed */
            margin-bottom: 25px;
        }

        
        .l1 p:last-child {
            margin-left: 200px; /* Remove margin from the last element */
        }
        .l2 {
            display: flex;
            margin-bottom: 20px;
        }
        
        .l2 p1{
            margin-top: 15px;
            margin-left: 226px; 
        }
        .l2 p:last-child {
            margin-left: 200px; /* Remove margin from the last element */
        }
        .l2 p6{
            margin-top: 15px;
            margin-left: 100px; 
        }
        .l2 p7{
            margin-top: 15px;
            margin-left: 100px; 
        }
        .l2 p8{
            margin-top: 15px;
            margin-right: 100px;
        }
        .dark-mode-button {
            position: fixed;
            right: 20px;
            bottom: 20px;
            padding: 10px 20px;
            background-color: #333;
            color: #fff;
            border: none;
            cursor: pointer;
            z-index: 1000;
        }
        .dark-mode-button:hover {
            opacity: 0.8;
        }
        body.dark-mode {
            background-color: #333;
            color: #fff; /* Text color in dark mode */
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="student_dashboard.php">Dashboard</a>
        <a href="calendarstudent.php">Calendar</a>
        <a href="#">FAQ</a>
        <a href="logout.php" onclick="exitPage();">Exit</a>
    </div>

    <div id="container2">
        <h2>Profile Picture</h2>
        <div class="profile-picture"></div>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
            Select image to upload:
            <input type="file" name="fileToUpload" id="fileToUpload">
            <input type="submit" value="Upload Image" name="submit" style="background-color: #4CAF50; color: white; padding: 10px; border: none; cursor: pointer;"> <br> <br>
        </form>

        <?php
        // Handle file upload
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit"])) {
            // Include the database connection
            include 'db_connection.php';

            $student_id = 1; // Initialize student_id (replace with your actual logic to get student ID dynamically)

            // Handle file upload
            $fileToUpload = $_FILES["fileToUpload"]["tmp_name"];
            $content = file_get_contents($fileToUpload);

            // Replace with your actual logic to get student number
            $student_number = "123456789";

            $sql = "INSERT INTO profilestudent (student_id, student_number, profile_content)
                    VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iss", $student_id, $student_number, $content);

            if ($stmt->execute()) {
                echo "<br><br>Picture uploaded successfully.";
                // Display current profile image after upload
                displayProfileImage($conn, $student_id);
            } else {
                echo "Error uploading file: " . $stmt->error;
            }

            $stmt->close();
            $conn->close();
        }

        // Handle image removal
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["remove"])) {
            $profile_id = $_POST["profile_id"];

            // Include the database connection
            include 'db_connection.php';

            // Delete image data from database
            $delete_sql = "DELETE FROM profilestudent WHERE profile_id = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param("i", $profile_id);

            if ($delete_stmt->execute()) {
                echo "Picture removed successfully.";
            } else {
                echo "Error removing image: " . $delete_stmt->error;
            }

            $delete_stmt->close();
            $conn->close();
        }

        // Function to retrieve profile image content
        function displayProfileImage($conn, $student_id) {
            // Include the database connection
            include 'db_connection.php';

            $retrieve_sql = "SELECT profile_id, profile_content FROM profilestudent WHERE student_id = ?";
            $retrieve_stmt = $conn->prepare($retrieve_sql);
            $retrieve_stmt->bind_param("i", $student_id);
            $retrieve_stmt->execute();
            $retrieve_stmt->store_result();
            $retrieve_stmt->bind_result($profile_id, $profile_content);

            if ($retrieve_stmt->fetch()) {
                echo '<br><h3>Scholar Profile:</h3>';
                echo '<img src="data:image/jpeg;base64,' . base64_encode($profile_content) . '" style="max-width: 150px; max-height: 150px;" />';
                echo '<form action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" method="post">';
                echo '<input type="hidden" name="profile_id" value="' . $profile_id . '">';
                echo '<br><input type="submit" value="Remove Image" name="remove" style="background-color: #e37d76; color: white; padding: 10px; border: none; cursor: pointer;"><br>';
                echo '</form>';
            } else {
                echo "No image found.";
            }

            $retrieve_stmt->close();
            $conn->close();
        }

        // Display current profile image if not already displayed
        if ($_SERVER["REQUEST_METHOD"] != "POST") {
            // Include the database connection
            include 'db_connection.php';

            displayProfileImage($conn, $student_id);

            $conn->close();
        }
        ?>
    </div>

    <div class="container info">
        <p><strong>First Name:</strong> <?php echo htmlspecialchars($student['firstname']); ?></p>
        <p><strong>Middle Name:</strong> <?php echo htmlspecialchars($student['middlename']); ?></p>
        <p><strong>Last Name:</strong> <?php echo htmlspecialchars($student['lastname']); ?></p>
        <div class="l1">
            <p1><strong>Email:</strong> <?php echo htmlspecialchars($student['email']); ?></p1>
            <p2><strong>Birthdate:</strong> <?php echo htmlspecialchars($student['birthdate']); ?></p2>
        </div>
        <div class="l1">
            <p5><strong>Password:</strong> ******</p5>
            <p3><strong>Contact Number:</strong> <?php echo htmlspecialchars($student['cont_num']); ?></p3>
        </div>
        <p4><strong>Student Number:</strong> <?php echo htmlspecialchars($student['student_number']); ?></p4>
        <p><strong>Scholarship Classification:</strong> <?php echo htmlspecialchars($student['scholarship_classification']); ?></p>
        <div class="l2">
            <p6><strong>Year Level:</strong> <?php echo htmlspecialchars($student['year']); ?></p6>
            <p7><strong>Semester:</strong> <?php echo htmlspecialchars($student['semester']); ?></p7>
            <p8><strong>Section:</strong> <?php echo htmlspecialchars($student['section']); ?></p8>
        </div>
    </div>
    <!-- Dark mode toggle button -->
    <button class="dark-mode-button" onclick="toggleDarkMode()">Dark Mode</button>

    <script>
        function toggleDarkMode() {
            const body = document.body;
            body.classList.toggle('dark-mode');
        }
    </script>
</body>
</html>
