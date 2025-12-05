<?php
session_start();
require_once 'config/db.php';

// Check if the form was submitted (assuming the registration form sends POST data)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Sanitize and Validate Input
    $username = htmlspecialchars(trim($_POST['username']));
    $password_input = $_POST['password'];

    if (empty($username) || empty($password_input)) {
        $_SESSION['register_error'] = "Username and password are required.";
        header("Location: register.html"); // Redirect to a registration form page
        exit();
    }
    
    // 2. Hash the Password (CRITICAL for security)
    // The password_hash function creates a secure, irreversible hash
    $hashed_password = password_hash($password_input, PASSWORD_BCRYPT);
    
    // 3. Insert User into Database using Prepared Statements
    $sql = "INSERT INTO users (username, password) VALUES (?, ?)";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ss", $username, $hashed_password);
        
        if ($stmt->execute()) {
            // Registration successful!
            $_SESSION['register_success'] = "Registration successful! You can now log in.";
            header("Location: login.html"); // Redirect to the login page
            exit();
        } else {
            // Error handling for duplicate username, etc.
            if ($conn->errno == 1062) { // 1062 is MySQL error for duplicate entry
                 $_SESSION['register_error'] = "Username already exists. Please choose another.";
            } else {
                 $_SESSION['register_error'] = "Something went wrong. Please try again later.";
            }
            header("Location: register.html");
            exit();
        }
    }
    
    $stmt->close();
    $conn->close();
} else {
    // If accessed directly without POST data
    header("Location: register.html"); 
    exit();
}
?>