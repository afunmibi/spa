<?php
// Simple CSRF helper
if (session_status() === PHP_SESSION_NONE) session_start();

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        try {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        } catch (Exception $e) {
            // fallback
            $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
        }
    }
    return $_SESSION['csrf_token'];
}

function csrf_input(): string {
    $t = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
    return "<input type=\"hidden\" name=\"_csrf\" value=\"$t\">";
}

function csrf_meta_tag(): string {
    $t = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
    return "<meta name=\"csrf-token\" content=\"$t\">";
}

/**
 * Verify token. Accepts token param (string) or reads from POST _csrf or header X-CSRF-Token.
 * Returns true if token matches session token.
 */
function verify_csrf($token = null): bool {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if ($token === null) {
        if (!empty($_POST['_csrf'])) {
            $token = $_POST['_csrf'];
        } else {
            // Try headers (X-CSRF-Token)
            if (function_exists('getallheaders')) {
                $hdrs = getallheaders();
                if (!empty($hdrs['X-CSRF-Token'])) $token = $hdrs['X-CSRF-Token'];
                elseif (!empty($hdrs['x-csrf-token'])) $token = $hdrs['x-csrf-token'];
            } else {
                // PHP fallback for some environments
                $h = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
                if ($h) $token = $h;
            }
        }
    }

    if (empty($token) || empty($_SESSION['csrf_token'])) return false;

    // Use hash_equals to avoid timing attacks
    return hash_equals($_SESSION['csrf_token'], (string)$token);
}
