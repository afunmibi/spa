<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/../csrf.php';
header('Content-Type: application/json');

// Require staff authentication to add enrollees
require_staff_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Verify CSRF token for AJAX/API clients (expects header or _csrf)
if (!verify_csrf()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
    exit;
}

$name = trim($input['name_of_enrollee'] ?? '');
$phone = trim($input['phone_no'] ?? '');
$dob = trim($input['date_of_birth'] ?? '');

if ($name === '') {
    echo json_encode(['success' => false, 'message' => 'Name is required']);
    exit;
}

// Generate a simple policy number (non-atomic). For heavy concurrency consider insert+update or a sequence table.
$maxRes = mysqli_query($conn, "SELECT MAX(id) as maxid FROM enrolment");
$maxRow = mysqli_fetch_assoc($maxRes);
$next = ((int)$maxRow['maxid']) + 1;
$policy_no = 'POL' . date('Ym') . str_pad($next, 6, '0', STR_PAD_LEFT) . substr(md5(uniqid('', true)), 0, 4);

$stmt = mysqli_prepare($conn, "INSERT INTO enrolment (policy_no, principal_name, phone, dob, date_enrolled, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW(), NOW())");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . mysqli_error($conn)]);
    exit;
}
mysqli_stmt_bind_param($stmt, 'ssss', $policy_no, $name, $phone, $dob);
$ok = mysqli_stmt_execute($stmt);
if (!$ok) {
    echo json_encode(['success' => false, 'message' => 'Execute failed: ' . mysqli_stmt_error($stmt)]);
    exit;
}
$insertId = mysqli_insert_id($conn);

echo json_encode(['success' => true, 'id' => $insertId, 'policy_no' => $policy_no]);
