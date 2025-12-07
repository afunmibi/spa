<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/_auth.php';
header('Content-Type: application/json');

// Require staff authentication to update enrollees
require_staff_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
require_once __DIR__ . '/../csrf.php';

// Verify CSRF token for AJAX/API clients
if (!verify_csrf()) {
     http_response_code(403);
     echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
     exit;
}
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
    exit;
}

$id = (int)($input['id'] ?? 0);
$name = trim($input['name_of_enrollee'] ?? '');
$phone = trim($input['phone_no'] ?? '');
$dob = trim($input['date_of_birth'] ?? '');

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit;
}

// Only update allowed/simple fields
$stmt = mysqli_prepare($conn, "UPDATE enrolment SET principal_name = ?, phone = ?, dob = ?, updated_at = NOW() WHERE id = ?");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . mysqli_error($conn)]);
    exit;
}
mysqli_stmt_bind_param($stmt, 'sssi', $name, $phone, $dob, $id);
$ok = mysqli_stmt_execute($stmt);
if (!$ok) {
    echo json_encode(['success' => false, 'message' => 'Execute failed: ' . mysqli_stmt_error($stmt)]);
    exit;
}

echo json_encode(['success' => true, 'updated' => mysqli_stmt_affected_rows($stmt)]);
