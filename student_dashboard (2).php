<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: login.php");
    exit();
}

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

// Get the user's student ID using the session user ID
$stmt = $conn->prepare("SELECT student_id FROM student WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($student_id);
$stmt->fetch();
$stmt->close();

// Check if student ID was found
if (!$student_id) {
    echo "Student ID not found for the current user.";
    exit();
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['pdfFile'])) {
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["pdfFile"]["name"]);
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $file_name = $_FILES["pdfFile"]["name"];

    // Check if file is a PDF
    if ($fileType != "pdf") {
        echo "Sorry, only PDF files are allowed.";
        $uploadOk = 0;
    }

    // Check if file already exists in the database
    $stmt = $conn->prepare("SELECT COUNT(*) FROM file1 WHERE file_name = ? AND student_id = ?");
    $stmt->bind_param("si", $file_name, $student_id);
    $stmt->execute();
    $stmt->bind_result($file_exists);
    $stmt->fetch();
    $stmt->close();

    if ($file_exists > 0) {
        echo "Error: A file with the same name already exists.";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
    } else {
        if (move_uploaded_file($_FILES["pdfFile"]["tmp_name"], $target_file)) {
            $upload_date = date('Y-m-d H:i:s');
            $status = 'processing'; // default status

            // Read file content into a variable
            $file_content = file_get_contents($target_file);

            // Prepare and bind parameters for the INSERT statement
            $stmt = $conn->prepare("INSERT INTO file1 (file_name, file_content, upload_date, status, student_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssi", $file_name, $file_content, $upload_date, $status, $student_id);

            // Execute the statement
            if ($stmt->execute()) {
                echo "The file " . htmlspecialchars(basename($_FILES["pdfFile"]["name"])) . " has been uploaded.";
            } else {
                echo "Error: " . $stmt->error;
            }

            $stmt->close();
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
}

// Handle file deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_file'])) {
    $file_to_delete = $_POST['delete_file'];

    // Delete file from database
    $stmt = $conn->prepare("DELETE FROM file1 WHERE file_name = ? AND student_id = ?");
    $stmt->bind_param("si", $file_to_delete, $student_id);
    $stmt->execute();
    $stmt->close();

    // Delete file from uploads folder
    $deleted = false;
    if (file_exists("uploads/" . $file_to_delete)) {
        if (unlink("uploads/" . $file_to_delete)) {
            $deleted = true;
        }
    }

    if ($deleted) {
        echo "The file " . htmlspecialchars($file_to_delete) . " has been deleted.";
    } else {
        echo "The file " . htmlspecialchars($file_to_delete) . " does not exist or couldn't be deleted.";
    }
}

// Fetch files data from files table
$sql = "SELECT file_name, MAX(upload_date) AS upload_date, status FROM file1 WHERE student_id = ? GROUP BY file_name";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2; /* Default light mode background */
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transition: background-color 0.3s ease; /* Smooth transition for background color */
        }
        body.dark-mode {
            background-color: #333; /* Dark mode background color */
            color: #fff; /* Dark mode text color */
        }
        .container {
            width: 90%;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: black;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        
        }
        th, td {
            padding: 10px;
            border: 1px solid grey;
            text-align: left;
            color: black;
        }
        th {
            background-color: #c8e1f5;
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
            height: 50px;
        }
        .navbar a {
            display: block;
            color: black;
            text-align: center;
            padding: 14px 16px;
            text-decoration: none;
            font-size: 15px;
            font-family:'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;
            font-weight: 200;
        }
        .navbar a:hover {
            background-color: #f2f2f2;
            color: black;
            height:30px;
        }
        .navbar .title {
            flex-grow: 1;
            text-align: center;
            font-size: 25px;
            color: #2d4a7b;
            font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;
        }
        .form-container {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .form-group {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            color:black;
        }
        label {
            font-weight: bold;
            margin-right: 10px;
        }
        input[type="file"] {
            margin-bottom: 0;
        }
        .buttons {
            display: flex;
            justify-content: center;
            width: 100%;
        }
        input[type="submit"], input[type="button"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            border: none;
            cursor: pointer;
            width: 25%;
            margin: 0 10px;
        }

        input[type="reset"] {
            background-color: #e37d76;
            color: white;
            padding: 10px;
            border: none;
            cursor: pointer;
            width: 20%;
            margin: 0 10px;
        }

        input[value="Delete"] {
            background-color: #e37d76;
            color: white;
            padding: 10px;
            border: none;
            cursor: pointer;
            width: 50%;
            margin: 0 10px;
        }

        input[type="button"] {
            background-color: #f44336;
        }

        input[type="submit"]:hover, input[type="button"]:hover, input[type="reset"]:hover {
            opacity: 0.8;
        }
        .info {
            margin-top: 20px;
            text-align: center;
            line-height: 1.5;
            color:black;
        }

        /* Dark mode toggle button */
        .dark-mode-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 10px 20px;
            background-color: #333;
            color: white;
            border: none;
            cursor: pointer;
            z-index: 1000;
            
        }

        .dark-mode-button:hover {
            opacity: 0.8;
        }


    </style>
</head>
<body>
    <div class="navbar">
        <a href="profilestudent.php">Profile</a>
        <a href="calendarstudent.php">Calendar</a>
        <div class="title">Student Dashboard</div>
        <a href="#">FAQ</a>
        <a href="logout.php" onclick="exitPage();">Exit</a>
    </div>

    <div class="container">
        <h2>Submit Your PDF File</h2>
        <div class="form-container">
            <form action="student_dashboard.php" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="pdfFile">Select PDF to upload:</label>
                    <input type="file" name="pdfFile" id="pdfFile" accept=".pdf" required>
                </div>
                <div class="buttons">
                    <input type="submit" value="Upload PDF" name="submit">
                    <input type="reset" value="Delete PDF">
                </div>
            </form>
            <div class="info">
                <p>Please upload your PDF file using the form above. Ensure that your file is in PDF format and is named appropriately.</p>
            </div>
        </div>

        <h2>Your Uploaded PDF Files</h2>
        <table>
            <tr>
                <th>File Name</th>
                <th>Date Uploaded</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['file_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['upload_date']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                    echo "<td>
                            <form action='student_dashboard.php' method='post' style='display:inline;'>
                                <input type='hidden' name='delete_file' value='" . htmlspecialchars($row['file_name']) . "'>
                                <input type='submit' value='Delete'>
                            </form>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='4'>No files found</td></tr>";
            }
            $conn->close();
            ?>
        </table>
    </div>

    <!-- Dark mode toggle button -->
    <button class="dark-mode-button" onclick="toggleDarkMode()">Dark Mode</button>

    <script>
        
        const body = document.body;
        const containers = document.querySelectorAll('.container');

        function toggleDarkMode() {
            body.classList.toggle('dark-mode');
            containers.forEach(container => {
                container.classList.toggle('dark-mode');
            });
        }
    </script>
</body>
</html>
