<?php

require_once 'db.php'; // Assumes this file establishes $conn

$login_error = '';
$register_success_message = '';

1. Check for and retrieve messages from the session
This catches successful registration messages from register.php
if (isset($_SESSION['register_success'])) {
    $register_success_message = $_SESSION['register_success'];
    unset($_SESSION['register_success']);
}

// This catches local login errors
if (isset($_SESSION['login_error'])) {
    $login_error = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
}

// 2. Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_login'])) {
    
    // Sanitize Input
    $username = htmlspecialchars(trim($_POST['username']));
    $password_input = $_POST['password'];

    // Simple Validation
    if (empty($username) || empty($password_input)) {
        $login_error = "Username and password are required.";
    } else {
        // Authenticate User using Prepared Statements
            $stmt = $conn->prepare("SELECT id, password, staff_id, role FROM users WHERE username = ?");
        
        if (!$stmt) {
            $login_error = "Database error: Failed to prepare statement.";
        } else {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();
            
            $is_authenticated = false;
            $fetched_id = null;
            $fetched_staff_id = null;
            $fetched_role = null;
            
            if ($stmt->num_rows === 1) { 
                $stmt->bind_result($fetched_id, $hashed_password, $fetched_staff_id, $fetched_role);
                $stmt->fetch();
                
                // CRITICAL: Use password_verify()
                if (password_verify($password_input, $hashed_password)) {
                    $is_authenticated = true;
                }
            }
            
            $stmt->close();
            // $conn->close(); // Generally best to keep connection open until end of script or close only after all DB ops are done.

            // 3. Handle Authentication Result
                if ($is_authenticated) {
                    // SUCCESS: Set session variables and redirect
                    $_SESSION['username'] = $username;
                    $_SESSION['loggedin'] = true;
                    if ($fetched_id !== null) {
                        $_SESSION['user_id'] = $fetched_id;
                    }
                    if ($fetched_staff_id !== null) {
                        $_SESSION['staff_id'] = $fetched_staff_id;
                    }
                    // Set role in session if available, default to staff
                    if (!empty($fetched_role)) {
                        $_SESSION['role'] = $fetched_role;
                    } elseif (!isset($_SESSION['role'])) {
                        $_SESSION['role'] = 'staff';
                    }

                    header("Location: dashboard/admin_dashboard.php");
                    exit();
                } else {
                // FAILURE: Generic error message for security
                $login_error = "Invalid username or password."; 
            }
        }
    }
}
// If the script reaches here, it renders the HTML (either first time, or after an error)
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-md">
        
        <h1 class="text-3xl font-bold text-center text-gray-800 mb-6">🔑 User Login</h1>
        
        <?php if (!empty($register_success_message)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Success!</strong>
                <span class="block sm:inline"><?php echo $register_success_message; ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($login_error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Login Failed!</strong>
                <span class="block sm:inline"><?php echo $login_error; ?></span>
            </div>
        <?php endif; ?>

        <form id="loginForm" action="login.php" method="POST" class="space-y-6">
            
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username:</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    required 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150"
                    placeholder="Enter your username"
                    value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>"
                >
            </div>
            
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password:</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150"
                    placeholder="Enter your password"
                >
            </div>
            
            <div>
                <button 
                    type="submit" 
                    class="w-full bg-indigo-600 text-white py-2 px-4 rounded-lg hover:bg-indigo-700 transition duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 font-semibold"
                    name="submit_login" >
                    Login
                </button>
            </div>

            <p class="text-center text-sm text-gray-600 mt-4">
                Don't have an account? <a href="register.php" class="text-green-600 hover:text-green-800 font-medium">Register here</a>.
            </p>
        </form>
    </div>
</body>
</html>