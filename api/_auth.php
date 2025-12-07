<?php
// Simple auth helper for API endpoints
// Include this at the top of API files that need staff authentication
if (session_status() === PHP_SESSION_NONE) session_start();

function require_staff_auth() {
    if (empty($_SESSION['staff_id']) && empty($_SESSION['user_id'])) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
}

function is_admin(): bool {
    return !empty($_SESSION['role']) && strtolower((string)$_SESSION['role']) === 'admin';
}

function require_admin_auth() {
    if (!is_admin()) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Admin privileges required']);
        exit;
    }
}
