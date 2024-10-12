<?php
ob_start(); // Start output buffering

// Function to establish database connection
function getDBConnection() {
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

    return $conn;
}

// Check if form is submitted for updating status
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["file_id"]) && isset($_POST["status"])) {
    $conn = getDBConnection();

    $file_id = $_POST["file_id"];
    $new_status = $_POST["status"];

    // Fetch file_name and student_id based on file_id
    $sql_fetch_details = "SELECT file_name, student_id FROM file1 WHERE file_id = ?";
    $stmt_fetch_details = $conn->prepare($sql_fetch_details);
    $stmt_fetch_details->bind_param("i", $file_id);
    $stmt_fetch_details->execute();
    $stmt_fetch_details->bind_result($file_name, $student_id);

    if ($stmt_fetch_details->fetch()) {
        $stmt_fetch_details->close();

        // Update the latest uploaded file with the same name and student_id
        $sql_update_status = "UPDATE file1 SET status = ? WHERE file_name = ? AND student_id = ? AND upload_date = (SELECT MAX(upload_date) FROM file1 WHERE file_name = ? AND student_id = ?)";
        $stmt_update_status = $conn->prepare($sql_update_status);
        $stmt_update_status->bind_param("ssiss", $new_status, $file_name, $student_id, $file_name, $student_id);
        $stmt_update_status->execute();

        if ($stmt_update_status->affected_rows > 0) {
            // Redirect to personnel dashboard or refresh page as needed
            header("Location: personnel_dashboard.php");
            exit();
        } else {
            echo "Error updating status.";
        }

        $stmt_update_status->close();
    } else {
        // If file details not found, handle it appropriately (redirect, error message, etc.)
        header("Location: personnel_dashboard.php"); // Redirect or handle differently as per your needs
        exit();
    }

    $conn->close();
}

// Check if file_id is set for viewing file details
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["file_id"])) {
    $file_id = $_GET["file_id"];

    $conn = getDBConnection();

    // Query to fetch file details based on file_id
    $sql = "SELECT file_name, file_content FROM file1 WHERE file_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $file_id);
    $stmt->execute();
    $stmt->bind_result($file_name, $file_content);

    if ($stmt->fetch()) {
        // Display file details
        header("Content-Type: application/pdf"); // Adjust content type based on file type
        header("Content-Disposition: inline; filename=\"$file_name\"");
        echo $file_content;
        exit;
    } else {
        // If file not found, handle it appropriately (redirect, error message, etc.)
        header("Location: personnel_dashboard.php"); // Redirect or handle differently as per your needs
        exit();
    }

    $stmt->close();
    $conn->close();
}

// Display file history
$conn = getDBConnection();

// Initialize where clause
$where_clause = "";

// Sorting conditions
$order_by = " ORDER BY upload_date ASC"; // Default sorting

if (isset($_GET['sort'])) {
    switch ($_GET['sort']) {
        case 'lastname':
            $order_by = " ORDER BY lastname";
            break;
        case 'date':
            $order_by = " ORDER BY upload_date DESC";
            break;
        case 'scholarship':
            $order_by = " ORDER BY scholarship.scholarship_classification";
            break;
        default:
            $order_by = " ORDER BY upload_date DESC";
            break;
    }
}

// Check if search query is set
if (isset($_GET['search'])) {
    $search_term = $_GET['search'];
    $where_clause = " WHERE scholarship.scholarship_classification LIKE '%$search_term%'";
}

$sql = "SELECT file_id, student.student_number, student.firstname, student.lastname, year_level.year, scholarship.scholarship_classification, file_name, upload_date, status
        FROM file1
        JOIN student ON file1.student_id = student.student_id
        JOIN year_level ON year_level.student_number = student.student_number
        LEFT JOIN scholarship ON scholarship.student_number = student.student_number"
        . $where_clause .
        $order_by;

$result = $conn->query($sql);

if ($result === false) {
    echo "Error: " . $conn->error;
}

ob_end_flush(); // Flush the output buffer and turn off output buffering

?>
<!DOCTYPE html>
<html>
<head>
    <title>Personnel Dashboard</title>
    <style>
        /* Your CSS styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .container {
           
            width: 95%;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 80px;
            color:black;
            
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
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
            color:black;
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
            margin-bottom: 20px;
            color:black;
        }
        .form-group {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
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
            gap: 10px;
            margin-bottom: 20px;
        }
        /* Style for individual sorting buttons */
        .buttons form {
            margin: 0;
        }
        .buttons input[type="submit"] {
            background-color:  #4CAF50;
            color: white;
            font-size: 13px;
            padding: 10px;
            border: none;
            cursor: pointer;
            width: auto;
            min-width: 100px;
            max-width: 200px;
        }
        .buttons input[type="submit"]:hover {
            opacity: 0.8;
        }
        .info {
            margin-top: 20px;
            text-align: center;
            line-height: 1.5;
        }
        
input[type="submit"] {
    background-color: #4CAF50;
    color: white;
    padding: 7px;
    border: none;
    cursor: pointer;
    width: 50%;
    margin: 0 10px;
    height: 35px;
}

input[value="Search"] {
    background-color:rgb(212, 133, 76);
    color: white;
    padding: 7px;
    border: none;
    cursor: pointer;
    width: 30%;
    margin: 0 10px;
    height: 35px;
    font-size: 14;
}

input[value="View"] {
    background-color:rgb(231, 233, 112);
    color: white;
    padding: 7px;
    border: none;
    cursor: pointer;
    width: 50%;
    margin: 0 10px;
    height: 35px;
    font-size: 14;
}


input[type="submit"]:hover {
    opacity: 0.8;

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

        body.dark-mode {
            background-color: #333; /* Dark mode background color */
            color: #fff; /* Dark mode text color */
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="profilepersonnel.php">Profile</a>
        <a href="calendarpersonnel.php">Calendar</a>
        <div class="title">Personnel Dashboard</div>
        <a href="#">FAQ</a>
        <a href="logout.php" onclick="exitPage();">Exit</a>
    </div>

    <div class="container">
        <h2>File History</h2>

        <!-- Search Form -->
        <div class="form-container">
            <form method="get" action="personnel_dashboard.php">
                <div class="form-group">
                    <label for="search">Search by Scholarship Classification:</label>
                    <input type="text" id="search" name="search" placeholder="Enter classification...">
                    <input type="submit" value="Search">
                </div>
            </form>
        </div>

        <!-- Sorting Buttons -->
        <div class="buttons">
            <form method="get" action="personnel_dashboard.php">
                <input type="hidden" name="sort" value="lastname">
                <input type="submit" value="Sort by Last Name">
            </form>
            <form method="get" action="personnel_dashboard.php">
                <input type="hidden" name="sort" value="date">
                <input type="submit" value="Sort by Date">
            </form>
            <form method="get" action="personnel_dashboard.php">
                <input type="hidden" name="sort" value="scholarship">
                <input type="submit" value="Sort by Scholarship">
            </form>
        </div>

        <table>
            <tr>
                <th>ID</th>
                <th>Student Number</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Year Level</th>
                <th>Scholarship <br>Classification</th>
                <th>File Name</th>
                <th>Date Upload</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row["file_id"] . "</td>";
                    echo "<td>" . $row["student_number"] . "</td>";
                    echo "<td>" . $row["firstname"] . "</td>";
                    echo "<td>" . $row["lastname"] . "</td>";
                    echo "<td>" . $row["year"] . "</td>";
                    echo "<td>" . $row["scholarship_classification"] . "</td>";
                    echo "<td>" . $row["file_name"] . "</td>";
                    echo "<td>" . $row["upload_date"] . "</td>";
                    echo "<td>" . $row["status"] . "</td>";
                    echo "<td>";

                    echo "<form method='post' action='personnel_dashboard.php'>";
                    echo "<input type='hidden' name='file_id' value='" . $row["file_id"] . "'>";
                    echo "<select name='status'>";
                    echo "<option value='accepted' " . ($row["status"] == "accepted" ? "selected" : "") . ">Accepted</option>";
                    echo "<option value='processing' " . ($row["status"] == "processing" ? "selected" : "") . ">Processing</option>";
                    echo "<option value='not accepted' " . ($row["status"] == "not accepted" ? "selected" : "") . ">Not Accepted</option>";
                    echo "</select>";
                    echo "<input type='submit' value='Update'>";
                    echo "</form>";
                    echo "<form method='get' action='view_file.php'>";
                    echo "<input type='hidden' name='file_id' value='" . $row["file_id"] . "'>";
                    echo "<input type='submit' value='View'>";
                    echo "</form>";
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='10'>No files found.</td></tr>";
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
