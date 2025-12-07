<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/../csrf.php';
header('Content-Type: application/json');

// Require admin authentication to delete enrollees
require_admin_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Verify CSRF token for AJAX/API clients
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

$id = (int)($input['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit;
}

$stmt = mysqli_prepare($conn, "DELETE FROM enrolment WHERE id = ?");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . mysqli_error($conn)]);
    exit;
}
mysqli_stmt_bind_param($stmt, 'i', $id);
$ok = mysqli_stmt_execute($stmt);
if (!$ok) {
    echo json_encode(['success' => false, 'message' => 'Execute failed: ' . mysqli_stmt_error($stmt)]);
    exit;
}

echo json_encode(['success' => true, 'deleted' => mysqli_stmt_affected_rows($stmt)]);
