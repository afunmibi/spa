<?php
// Set the content type to JSON
header('Content-Type: application/json');

// --- 1. Database Configuration ---
$servername = "localhost";
$username = "your_db_username"; // *** REPLACE with your actual database username ***
$password = "your_db_password"; // *** REPLACE with your actual database password ***
$dbname = "enrollee_db";       // *** REPLACE with your actual database name ***

// --- 2. Create connection ---
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

// --- 3. Define the SQL Query ---
// Select the required fields from your enrollee table. 
// Note: 'id' must match the Registration ID field used in the JavaScript.
$sql = "SELECT id, name_of_enrollee, phone_no, date_of_birth FROM enrollees ORDER BY id DESC";

// --- 4. Execute the Query ---
$result = $conn->query($sql);

$enrollees = [];

if ($result) {
    if ($result->num_rows > 0) {
        // Fetch all rows as associative arrays
        while ($row = $result->fetch_assoc()) {
            $enrollees[] = $row;
        }
        // Success: Return the list of enrollees
        http_response_code(200); // OK
        echo json_encode($enrollees);
    } else {
        // Success, but no data found
        http_response_code(200); // OK
        echo json_encode([]); 
    }
} else {
    // Query failed
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Query execution failed: ' . $conn->error]);
}

// --- 5. Close the connection ---
$conn->close();

?>