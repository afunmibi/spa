<?php
// create_package.php

// 1. Include the Database Connection
require_once dirname(__DIR__, 2) . '/db.php'; 

$success_message = '';
$error_message = '';
$allowed_plans = ['basic', 'standard', 'premium', 'custom']; 

// --- 2. Check for Form Submission ---
if (isset($_POST['create_package'])) {
    
    // --- 3. Data Validation and Sanitization ---
    $required_fields = ['package_name', 'package_description', 'package_price', 'package_plan', 'package_code'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $error_message = "Error: The field '" . str_replace('_', ' ', $field) . "' is required.";
            break;
        }
    }

    if (empty($error_message)) {
        // Sanitize and validate inputs
        $packageName = trim($_POST['package_name']);
        $packageDescription = trim($_POST['package_description']);
        $packagePlan = trim(strtolower($_POST['package_plan'])); // Convert to lowercase for ENUM check
        $packageCode = trim(strtoupper($_POST['package_code']));

        // Validate Price
        $packagePrice = filter_var($_POST['package_price'], FILTER_VALIDATE_FLOAT);
        if ($packagePrice === false || $packagePrice <= 0) {
            $error_message = "Error: Package price must be a valid positive number.";
        }
        
        // Validate ENUM plan
        if (!in_array($packagePlan, $allowed_plans)) {
            $error_message = "Error: Package plan must be one of: " . implode(', ', $allowed_plans);
        }
    }

    // Only proceed to DB if no errors were found
    if (empty($error_message)) {
        
        // --- 4. Prepare and Execute SQL Statement (MySQLi) ---
        $sql = "INSERT INTO packages (package_name, package_description, package_price, package_plan, package_code) 
                 VALUES (?, ?, ?, ?, ?)";

        // Start Transaction Management 
        $mysqli->begin_transaction(); 

        try {
            $stmt = $mysqli->prepare($sql);
            
            if ($stmt === false) {
                throw new Exception("MySQLi Prepare failed: " . $mysqli->error);
            }

            // Bind parameters: ssds (s=string, d=double/float)
            $stmt->bind_param("ssdss", $packageName, $packageDescription, $packagePrice, $packagePlan, $packageCode);
            
            if (!$stmt->execute()) {
                 throw new Exception("MySQLi Execute failed: " . $stmt->error, $stmt->errno);
            }
            
            $mysqli->commit();

            // --- 5. Success Handling ---
            $lastId = $mysqli->insert_id;
            $success_message = "✅ Success! Package **{$packageName}** created (ID: **{$lastId}**, Code: **{$packageCode}**).";
            
            // Clear post data 
            $_POST = array(); 

        } catch (Exception $e) {
            // --- 6. Error Handling ---
            $mysqli->rollback(); 
            
            // 1062 is the MySQL error code for Duplicate entry for key (UNIQUE constraint)
            if ($e->getCode() == 1062) {
                 $error_message = "Error: A package with the code **{$packageCode}** already exists. Please choose a unique code.";
            } else {
                error_log("SQL Error: " . $e->getMessage());
                $error_message = "A system error occurred during insertion. Code: " . $e->getCode();
            }
        } finally {
            if (isset($stmt)) {
                 $stmt->close();
            }
        }
    }
}
// HTML form for package creation
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Package</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    
    <div class="max-w-xl w-full space-y-8 bg-white p-10 rounded-xl shadow-2xl">
        
        <div class="text-center">
            <h1 class="text-4xl font-extrabold text-gray-900">📦 Create a New Package</h1>
        </div>

        <?php if (!empty($success_message)): ?>
            <div class="p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg" role="alert"><p><?php echo $success_message; ?></p></div>
        <?php endif; ?>
        
        <?php if (!empty($error_message)): ?>
            <div class="p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg" role="alert"><p><?php echo $error_message; ?></p></div>
        <?php endif; ?>

        <form action="create_package.php" method="POST" class="mt-8 space-y-6">
            
            <div>
                <label for="package_name" class="block text-sm font-medium text-gray-700 mb-1">Package Name:</label>
                <input type="text" id="package_name" name="package_name" required value="<?php echo htmlspecialchars($_POST['package_name'] ?? ''); ?>">
            </div>
            
            <div>
                <label for="package_description" class="block text-sm font-medium text-gray-700 mb-1">Package Description:</label>
                <textarea id="package_description" name="package_description" rows="4" required><?php echo htmlspecialchars($_POST['package_description'] ?? ''); ?></textarea>
            </div>
            
            <div>
                <label for="package_price" class="block text-sm font-medium text-gray-700 mb-1">Package Price (₦):</label>
                <input type="number" id="package_price" name="package_price" required min="0.01" step="0.01" value="<?php echo htmlspecialchars($_POST['package_price'] ?? ''); ?>">
            </div>
            
            <div>
                <label for="package_plan" class="block text-sm font-medium text-gray-700 mb-1">Package Plan Level:</label>
                <select id="package_plan" name="package_plan" required>
                    <?php foreach ($allowed_plans as $plan): ?>
                        <option value="<?php echo $plan; ?>" <?php echo (($_POST['package_plan'] ?? '') === $plan) ? 'selected' : ''; ?>>
                            <?php echo ucwords($plan); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="package_code" class="block text-sm font-medium text-gray-700 mb-1">Package Code (Unique):</label>
                <input type="text" id="package_code" name="package_code" required value="<?php echo htmlspecialchars($_POST['package_code'] ?? ''); ?>">
            </div>
            
            <div>
                <button type="submit" name="create_package">🚀 Create Package</button>
            </div>
        </form>
    </div>
</body>
</html>