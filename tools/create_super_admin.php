<?php
// CLI-only script to create a super_admin user
// Usage: php tools/create_super_admin.php

if (PHP_SAPI !== 'cli') {
    echo "This script must be run from the command line for safety.\n";
    http_response_code(403);
    exit;
}

require_once __DIR__ . '/../db.php';

function prompt($msg) {
    echo $msg;
    $line = trim(fgets(STDIN));
    return $line;
}

echo "Create super_admin user\n";
$username = prompt("Username: ");
$email = prompt("Email (optional): ");
// Read password twice
while (true) {
    echo "Password: ";
    // hide input is not portable; use normal input
    $pass1 = trim(fgets(STDIN));
    echo "Confirm Password: ";
    $pass2 = trim(fgets(STDIN));
    if ($pass1 !== $pass2) {
        echo "Passwords do not match. Try again.\n";
        continue;
    }
    if (strlen($pass1) < 8) {
        echo "Password too short (min 8 chars). Try again.\n";
        continue;
    }
    break;
}

$hashed = password_hash($pass1, PASSWORD_DEFAULT);

try {
    // check if username exists
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = :u LIMIT 1');
    $stmt->execute([':u' => $username]);
    if ($stmt->fetch()) {
        echo "User with that username already exists. Exiting.\n";
        exit(1);
    }

    $insert = $pdo->prepare('INSERT INTO users (username, email, password, role) VALUES (:u, :e, :p, :r)');
    $insert->execute([':u' => $username, ':e' => $email, ':p' => $hashed, ':r' => ROLE_SUPER_ADMIN]);
    $id = $pdo->lastInsertId();
    echo "Created super_admin user: {$username} (id: {$id})\n";
} catch (Exception $e) {
    echo "Failed to create user: " . $e->getMessage() . "\n";
    exit(1);
}

?>
