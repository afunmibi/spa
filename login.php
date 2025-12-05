<?php
session_start();
require_once 'config/db.php';

// Check if the form was submitted via POST
if (isset($_POST['submit_login']) ) {
    
    // 1. Sanitize Input
    // Use htmlspecialchars to prevent XSS. Use trim to remove whitespace.
    $username = htmlspecialchars(trim($_POST['username']));
    $password_input = $_POST['password']; // Keep the password as-is for password_verify()

    // 2. Simple Server-Side Validation
    if (empty($username) || empty($password_input)) {
        $_SESSION['login_error'] = "Username and password are required.";
        header("Location: index.html");
        exit();
    }

    // 3. Authenticate User using Prepared Statements
    $stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
    
    // Check if the statement preparation was successful
    if (!$stmt) {
        $_SESSION['login_error'] = "Database error: Failed to prepare statement.";
        header("Location: index.html");
        exit();
    }
    
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    
    $is_authenticated = false;
    
    if ($stmt->num_rows === 1) { // We expect exactly one user
        $stmt->bind_result($hashed_password);
        $stmt->fetch();
        
        // CRITICAL: Use password_verify() with the unhashed input password
        if (password_verify($password_input, $hashed_password)) {
            $is_authenticated = true;
        }
    }
    
    $stmt->close();
    $conn->close();

    // 4. Handle Authentication Result
    if ($is_authenticated) {
        // Set session variables upon successful login
        $_SESSION['username'] = $username;
        $_SESSION['loggedin'] = true;

        // Redirect to a protected page
        header("Location: dashboard/admin_dashboard.php");
        exit();
    } else {
        // Authentication failed
        // It's a security best practice to give a generic error message
        $_SESSION['login_error'] = "Invalid username or password."; 
        header("Location: login.html");
        exit();
    }
} else {
    // If someone tries to access login.php directly without POST
    header("Location: login.html");
    exit();
}
?>