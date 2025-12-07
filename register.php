<?php
require_once 'db.php'; // Assumes this file establishes $conn

$register_error = '';
$register_success = '';

// 1. Check for and display session messages from previous attempts
if (isset($_SESSION['register_error'])) {
    $register_error = $_SESSION['register_error'];
    unset($_SESSION['register_error']);
}

if (isset($_SESSION['register_success'])) {
    $register_success = $_SESSION['register_success'];
    unset($_SESSION['register_success']);
}

// 2. Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_register'])) {
    
    // Sanitize Input
    $username = htmlspecialchars(trim($_POST['username']));
    $password_input = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $staff_id = isset($_POST['staff_id']) ? htmlspecialchars(trim($_POST['staff_id'])) : null;

    // Validation
    if (empty($username) || empty($password_input) || empty($confirm_password)) {
        $register_error = "All fields are required.";
    } elseif ($password_input !== $confirm_password) {
        $register_error = "Passwords do not match.";
    } else {
        // 3. Hash the Password
        // Use default algorithm (currently BCRYPT)
        $hashed_password = password_hash($password_input, PASSWORD_DEFAULT); 
        
        // 4. Insert User using Prepared Statements (include staff_id if provided)
        if ($staff_id !== null && $staff_id !== '') {
            $sql = "INSERT INTO users (username, password, staff_id) VALUES (?, ?, ?)";
            $param_types = "sss";
        } else {
            $sql = "INSERT INTO users (username, password) VALUES (?, ?)";
            $param_types = "ss";
        }

        if ($stmt = $conn->prepare($sql)) {
            if ($param_types === "sss") {
                $stmt->bind_param($param_types, $username, $hashed_password, $staff_id);
            } else {
                $stmt->bind_param($param_types, $username, $hashed_password);
            }
            
            if ($stmt->execute()) {
                // Success: Set message and redirect to login
                $_SESSION['register_success'] = "Registration successful! You can now log in.";
                header("Location: login.php"); // Updated to login.php (best practice)
                exit();
            } else {
                // Error handling
                if ($conn->errno == 1062) { // MySQL error for duplicate entry
                    $register_error = "Username '{$username}' already exists. Please choose another.";
                } else {
                    $register_error = "Database error: Could not complete registration.";
                }
            }
            $stmt->close();
        } else {
            $register_error = "Database preparation failed.";
        }
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Account</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    
    <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-md">
        
        <h1 class="text-3xl font-bold text-center text-gray-800 mb-6">✍️ Register Account</h1>
        
        <?php if (!empty($register_error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline"><?php echo $register_error; ?></span>
            </div>
        <?php elseif (!empty($register_success)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Success!</strong>
                <span class="block sm:inline"><?php echo $register_success; ?></span>
            </div>
        <?php endif; ?>

        <form id="registerForm" action="register.php" method="POST" class="space-y-6">
            
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username:</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    required 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150"
                    placeholder="Choose a username"
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
                    placeholder="Enter a secure password"
                >
            </div>
            
            <div>
                <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password:</label>
                <input 
                    type="password" 
                    id="confirm_password" 
                    name="confirm_password" 
                    required 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150"
                    placeholder="Confirm your password"
                >
            </div>
            
            <div>
                <button 
                    type="submit" 
                    name="submit_register"
                    class="w-full bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700 transition duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 font-semibold"
                >
                    Create Account
                </button>
            </div>

            <p class="text-center text-sm text-gray-600 mt-4">
                Already have an account? <a href="login.php" class="text-indigo-600 hover:text-indigo-800 font-medium">Log in here</a>.
            </p>
        </form>
    </div>

    </body>
</html>