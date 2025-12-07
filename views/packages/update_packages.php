<?php
// update_packages.php

// 1. Include the Database Connection
// Assumes db.php creates a $mysqli object, connected to the database.
require_once dirname(__DIR__, 2) . '/db.php'; 

$package_data = null;
$error_message = '';
$success_message = '';
$allowed_plans = ['basic', 'standard', 'premium', 'custom']; 

// --- 2. Get Package ID from URL and Fetch Existing Data ---
$package_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($package_id > 0) {
    // Fetch current package data using a prepared statement for safety
    $sql_select = "SELECT package_id, package_name, package_description, package_price, package_plan, package_code 
                   FROM packages 
                   WHERE package_id = ?";
    
    try {
        $stmt_select = $mysqli->prepare($sql_select);
        $stmt_select->bind_param("i", $package_id);
        $stmt_select->execute();
        $result = $stmt_select->get_result();
        
        if ($result->num_rows === 1) {
            $package_data = $result->fetch_assoc();
        } else {
            $error_message = "Error: Package not found.";
        }
        $stmt_select->close();
    } catch (Exception $e) {
        error_log("Select Error: " . $e->getMessage());
        $error_message = "A system error occurred while loading package data.";
    }
} else {
    $error_message = "Error: No package ID provided for update.";
}

// --- 3. Handle Form Submission (POST Request) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_package']) && empty($error_message)) {
    
    // --- Data Validation and Sanitization ---
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
        $packagePlan = trim(strtolower($_POST['package_plan']));
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
        
        // --- 4. Prepare and Execute SQL UPDATE Statement ---
        $sql_update = "UPDATE packages 
                       SET package_name = ?, package_description = ?, package_price = ?, package_plan = ?, package_code = ? 
                       WHERE package_id = ?";

        // Start Transaction Management for robust update
        $mysqli->begin_transaction(); 

        try {
            $stmt_update = $mysqli->prepare($sql_update);
            
            if ($stmt_update === false) {
                throw new Exception("MySQLi Prepare failed: " . $mysqli->error);
            }

            // Bind parameters: ssdssi (s=string, d=double, i=integer for the package_id)
          
$stmt_update->bind_param("ssdssi", 
    $packageName, 
    $packageDescription, 
    $packagePrice, 
    $packagePlan, 
    $packageCode, // s
    $package_id   // i
);
            
            if (!$stmt_update->execute()) {
                 throw new Exception("MySQLi Execute failed: " . $stmt_update->error, $stmt_update->errno);
            }
            
            $mysqli->commit();

            // --- Success Handling ---
            $success_message = "🎉 Success! Package **{$packageName}** (ID: **{$package_id}**) has been updated.";
            
            // Update $package_data to reflect changes
            $package_data = [
                'package_id' => $package_id,
                'package_name' => $packageName,
                'package_description' => $packageDescription,
                'package_price' => $packagePrice,
                'package_plan' => $packagePlan,
                'package_code' => $packageCode,
            ];

        } catch (Exception $e) {
            // --- Error Handling ---
            $mysqli->rollback(); 
            
            // 1062 is the MySQL error code for Duplicate entry for key (UNIQUE constraint)
            if ($e->getCode() == 1062) {
                 $error_message = "Error: A package with the code **{$packageCode}** already exists. Please choose a unique code.";
            } else {
                error_log("SQL Update Error: " . $e->getMessage());
                $error_message = "A system error occurred during the update. Code: " . $e->getCode();
            }
        } finally {
            if (isset($stmt_update)) {
                 $stmt_update->close();
            }
        }
    }
}

// Ensure the data displayed in the form is the original data OR the newly submitted data
$display_data = $package_data;
if (!empty($error_message) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // If there's an error on POST, use the POSTed data to repopulate the form
    $display_data = [
        'package_id' => $package_id,
        'package_name' => $_POST['package_name'],
        'package_description' => $_POST['package_description'],
        'package_price' => $_POST['package_price'],
        'package_plan' => $_POST['package_plan'],
        'package_code' => $_POST['package_code'],
    ];
}

// If package data was not found or an initial ID was missing, show a generic error page
if (!$package_data && empty($error_message)) {
     $error_message = "Package data could not be loaded or the ID is invalid.";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Package <?php echo $package_id; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    
    <div class="max-w-xl w-full space-y-8 bg-white p-10 rounded-xl shadow-2xl">
        
        <div class="text-center">
            <h1 class="text-4xl font-extrabold text-gray-900">✏️ Update Package: ID <?php echo $package_id; ?></h1>
        </div>

        <?php if (!empty($success_message)): ?>
            <div class="p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg" role="alert"><p><?php echo $success_message; ?></p></div>
        <?php endif; ?>
        
        <?php if (!empty($error_message)): ?>
            <div class="p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg" role="alert"><p><?php echo $error_message; ?></p></div>
            <?php if (!$package_data): ?>
                <a href="search_packages.php" class="text-indigo-600 hover:text-indigo-800 font-medium mt-4 block text-center">Go back to Search</a>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($display_data): ?>
            <form action="update_packages.php?id=<?php echo $package_id; ?>" method="POST" class="mt-8 space-y-6">
                
                <div>
                    <label for="package_name" class="block text-sm font-medium text-gray-700 mb-1">Package Name:</label>
                    <input type="text" id="package_name" name="package_name" required 
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                           value="<?php echo htmlspecialchars($display_data['package_name'] ?? ''); ?>">
                </div>
                
                <div>
                    <label for="package_description" class="block text-sm font-medium text-gray-700 mb-1">Package Description:</label>
                    <textarea id="package_description" name="package_description" rows="4" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <?php echo htmlspecialchars($display_data['package_description'] ?? ''); ?>
                    </textarea>
                </div>
                
                <div>
                    <label for="package_price" class="block text-sm font-medium text-gray-700 mb-1">Package Price (₦):</label>
                    <input type="number" id="package_price" name="package_price" required min="0.01" step="0.01" 
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                           value="<?php echo htmlspecialchars($display_data['package_price'] ?? ''); ?>">
                </div>
                
                <div>
                    <label for="package_plan" class="block text-sm font-medium text-gray-700 mb-1">Package Plan Level:</label>
                    <select id="package_plan" name="package_plan" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                        <?php foreach ($allowed_plans as $plan): ?>
                            <option value="<?php echo $plan; ?>" 
                                <?php echo (($display_data['package_plan'] ?? '') === $plan) ? 'selected' : ''; ?>>
                                <?php echo ucwords($plan); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="package_code" class="block text-sm font-medium text-gray-700 mb-1">Package Code (Unique):</label>
                    <input type="text" id="package_code" name="package_code" required 
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                           value="<?php echo htmlspecialchars($display_data['package_code'] ?? ''); ?>">
                </div>
                
                <div class="pt-4">
                    <button type="submit" name="update_package"
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150">
                        💾 **Save Changes**
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>