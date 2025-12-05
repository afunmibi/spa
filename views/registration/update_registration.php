<?php
// Set the content type to JSON
header('Content-Type: application/json');
// Allow only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Only POST method is allowed.']);
    exit();
}

// --- 1. Database Configuration ---
$servername = "localhost";
$username = "your_db_username"; // *** REPLACE with your actual database username ***
$password = "your_db_password"; // *** REPLACE with your actual database password ***
$dbname = "enrollee_db";       // *** REPLACE with your actual database name ***

// --- 2. Input Handling ---
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

// Check if required data is present
if (!isset($data['id']) || !isset($data['name_of_enrollee']) || !isset($data['phone_no']) || !isset($data['date_of_birth'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Missing required fields: id, name_of_enrollee, phone_no, or date_of_birth.']);
    exit();
}

$id = $data['id'];
$name = $data['name_of_enrollee'];
$phone = $data['phone_no'];
$dob = $data['date_of_birth'];

// --- 3. Create connection ---
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

// --- 4. Prepare and Execute the UPDATE Query (using prepared statements for security) ---
$sql = "UPDATE enrollees 
        SET name_of_enrollee = ?, phone_no = ?, date_of_birth = ? 
        WHERE id = ?";

$stmt = $conn->prepare($sql);

if ($stmt) {
    // Bind parameters: 'sssi' means three strings and one integer (for the ID)
    // Adjust 'i' if your ID column is a string type (e.g., UUID)
    $stmt->bind_param("sssi", $name, $phone, $dob, $id); 
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            // Success
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Enrollee record updated successfully.']);
        } else {
            // No rows changed (ID might be invalid or data was identical)
            http_response_code(200);
            echo json_encode(['success' => false, 'message' => 'No record found or no changes made.']);
        }
    } else {
        // Execution failed
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Execution failed: ' . $stmt->error]);
    }
    
    $stmt->close();
} else {
    // Preparation failed
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Statement preparation failed: ' . $conn->error]);
}

// --- 5. Close the connection ---
$conn->close();

?>