<?php
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../csrf.php';

// Require login and super_admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../login.php');
    exit;
}
if (!isset($_SESSION['role']) || $_SESSION['role'] !== ROLE_SUPER_ADMIN) {
    http_response_code(403);
    echo "Access denied";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $_SESSION['role_change_error'] = 'Invalid CSRF token.';
        header('Location: index.php');
        exit;
    }
    $userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    $newRole = isset($_POST['new_role']) ? trim($_POST['new_role']) : '';

    global $ALLOWED_ROLES;
    if ($userId <= 0 || empty($newRole) || !in_array($newRole, $ALLOWED_ROLES, true)) {
        $_SESSION['role_change_error'] = 'Invalid input.';
        header('Location: index.php');
        exit;
    }

    try {
        $stmt = $pdo->prepare('UPDATE users SET role = :role WHERE id = :id');
        $stmt->execute([':role' => $newRole, ':id' => $userId]);
        $_SESSION['role_change_success'] = 'Role updated.';
    } catch (Exception $e) {
        error_log('Failed to update role: ' . $e->getMessage());
        $_SESSION['role_change_error'] = 'Failed to update role.';
    }

    header('Location: index.php');
    exit;
}

http_response_code(405);
echo 'Method not allowed';
