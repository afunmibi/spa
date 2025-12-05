<?php
// view_packages.php

// 1. Start session for connection/error handling
session_start();

// Check if the database configuration file exists and include it.
if (!file_exists('../config/db.php')) {
    die("Error: Database configuration file 'config/db.php' not found. Please create it.");
}
require_once '../config/db.php'; 

$packages = [];
$error = null;

// --- 2. Database Retrieval ---
// Ensure all necessary columns are selected
$sql = "SELECT package_name, package_description, package_price, package_plan, package_code, created_at FROM packages ORDER BY package_id DESC";

if ($result = $conn->query($sql)) {
    // Fetch all results into an associative array
    while ($row = $result->fetch_assoc()) {
        $packages[] = $row;
    }
    $result->free();
} else {
    $error = "Database query failed: " . $conn->error;
}

$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View All Packages</title>
    <!-- Tailwind CSS link -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen">

    <div class="container mx-auto p-4 sm:p-8">
        
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-extrabold text-gray-900">
                📋 All Created Packages (<?php echo count($packages); ?>)
            </h1>
            <a href="create_package.html" class="px-4 py-2 bg-indigo-600 text-white font-medium rounded-lg shadow-md hover:bg-indigo-700 transition duration-150">
                + Create New Package
            </a>
        </div>

        <!-- Display Success/Error Messages from previous actions (e.g., Edit) -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative mb-6" role="alert">
                <?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative mb-6" role="alert">
                <span class="font-bold">Error:</span> <?php echo $error; ?>
            </div>
        <?php elseif (empty($packages)): ?>
            <div class="text-center bg-white p-10 rounded-xl shadow-lg">
                <p class="text-xl text-gray-600">No packages found in the database.</p>
                <p class="mt-2 text-gray-500">Click "Create New Package" to add your first one.</p>
            </div>
        <?php else: ?>

        <!-- Responsive Table Container -->
        <div class="shadow-lg overflow-hidden border-b border-gray-200 sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Code
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Name / Description
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Plan
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Price
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Created
                        </th>
                        <!-- New Actions Column -->
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    
                    <?php foreach ($packages as $pkg): ?>
                    <tr class="hover:bg-gray-50 transition duration-150">
                        <!-- Package Code -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-indigo-600">
                            <?php echo htmlspecialchars($pkg['package_code']); ?>
                        </td>
                        
                        <!-- Name & Description -->
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($pkg['package_name']); ?></div>
                            <div class="text-xs text-gray-500 truncate w-64"><?php echo htmlspecialchars($pkg['package_description']); ?></div>
                        </td>
                        
                        <!-- Plan Badge -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php
                                $color = [
                                    'basic' => 'yellow', 
                                    'standard' => 'blue', 
                                    'premium' => 'green', 
                                    'custom' => 'purple'
                                ][$pkg['package_plan']] ?? 'gray';
                            ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-<?php echo $color; ?>-100 text-<?php echo $color; ?>-800 capitalize">
                                <?php echo htmlspecialchars($pkg['package_plan']); ?>
                            </span>
                        </td>
                        
                        <!-- Price -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">
                            $<?php echo number_format($pkg['package_price'], 2); ?>
                        </td>
                        
                        <!-- Created Date -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo date('Y-m-d', strtotime($pkg['created_at'])); ?>
                        </td>

                        <!-- Actions (Edit) -->
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="edit_package.php?code=<?php echo urlencode($pkg['package_code']); ?>" class="text-indigo-600 hover:text-indigo-900 transition duration-150">Edit</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>

                </tbody>
            </table>
        </div>

        <?php endif; ?>
    </div>

</body>
</html>