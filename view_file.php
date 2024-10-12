<?php
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
        echo "File not found.";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request.";
}
?>
