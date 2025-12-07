<?php
// view_packages.php

// 1. Start session for connection/error handling
session_start();

// --- DATABASE CONNECTION SETUP ---
// Define the path to the database configuration file.
$db_path = dirname(__DIR__, 2) . '/db.php';

// Check if the database configuration file exists.
if (!file_exists($db_path)) {
    die("Error: Database configuration file '{$db_path}' not found. Please create it and ensure it defines the function get_db_connection().");
}

// 2. Include the Database Connection function (now defines get_db_connection())
require_once $db_path; 
// --- END DATABASE CONNECTION SETUP ---

$packages = [];
$error = null;
$searchTerm = '';

// Attempt to get the database connection object
$conn = get_db_connection();

// Set error if connection failed
if ($conn === null) {
    $error = "Failed to establish a database connection. Check 'db.php'.";
}


// --- 3. Function to render the table body rows ---
function render_package_rows($packages) {
    $html = '';
    
    if (empty($packages)) {
        // Return a single row spanning all columns if no results are found
        return '
            <tr>
                <td colspan="6" class="px-6 py-10 text-center text-gray-500 italic">
                    No packages found matching the current criteria.
                </td>
            </tr>
        ';
    }

    foreach ($packages as $pkg) {
        // Calculate color for plan badge
        $color = [
            'basic' => 'yellow', 
            'standard' => 'blue', 
            'premium' => 'green', 
            'custom' => 'purple'
        ][$pkg['package_plan']] ?? 'gray';

        $html .= '
        <tr class="hover:bg-gray-50 transition duration-150">
            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-indigo-600">
                ' . htmlspecialchars($pkg['package_code']) . '
            </td>
            
            <td class="px-6 py-4">
                <div class="text-sm font-medium text-gray-900">' . htmlspecialchars($pkg['package_name']) . '</div>
                <div class="text-xs text-gray-500 truncate w-64">' . htmlspecialchars($pkg['package_description']) . '</div>
            </td>
            
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-' . $color . '-100 text-' . $color . '-800 capitalize">
                    ' . htmlspecialchars($pkg['package_plan']) . '
                </span>
            </td>
            
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">
                $' . number_format($pkg['package_price'], 2) . '
            </td>
            
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                ' . date('Y-m-d', strtotime($pkg['created_at'])) . '
            </td>

            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <a href="edit_package.php?code=' . urlencode($pkg['package_code']) . '" class="text-indigo-600 hover:text-indigo-900 transition duration-150 mr-4">Edit</a>
                <a href="delete_package.php?code=' . urlencode($pkg['package_code']) . '" 
                    onclick="return confirm(\'Are you sure you want to delete package: ' . htmlspecialchars($pkg['package_name'], ENT_QUOTES) . ' (' . htmlspecialchars($pkg['package_code'], ENT_QUOTES) . ')? This action cannot be undone.\')" 
                    class="text-red-600 hover:text-red-900 transition duration-150">Delete</a>
            </td>
        </tr>';
    }
    return $html;
}


// --- 2. Database Retrieval (for both full page load and AJAX) ---
if ($conn !== null) { // Only proceed if connection is successful
    if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
        $searchTerm = trim($_GET['search']);
        $searchWildcard = "%" . $searchTerm . "%"; 
        
        // Using $conn->prepare() for security on search terms
        $sql = "SELECT package_name, package_description, package_price, package_plan, package_code, created_at 
                FROM packages 
                WHERE package_code LIKE ? OR package_name LIKE ? OR package_description LIKE ?
                ORDER BY package_id DESC";

        if ($stmt = $conn->prepare($sql)) {
            // Bind the same wildcard value to all three placeholders
            $stmt->bind_param("sss", $searchWildcard, $searchWildcard, $searchWildcard);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                $packages[] = $row;
            }
            $stmt->close();
        } else {
            $error = "Database query preparation failed: " . $conn->error;
        }

    } else {
        // No search term, fetch all
        $sql = "SELECT package_name, package_description, package_price, package_plan, package_code, created_at FROM packages ORDER BY package_id DESC";

        if ($result = $conn->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                $packages[] = $row;
            }
            $result->free();
        } else {
            $error = "Database query failed: " . $conn->error;
        }
    }
}


// --- 4. AJAX Response Handler ---
if (isset($_GET['is_ajax']) && $_GET['is_ajax'] === 'true') {
    // Only output the table body rows and exit
    header('Content-Type: text/html');
    
    // If we have an error, show an error message instead of table rows
    if ($error) {
        echo '<tr><td colspan="6" class="px-6 py-4 text-center text-red-500 font-semibold">AJAX Error: ' . htmlspecialchars($error) . '</td></tr>';
    } else {
        echo render_package_rows($packages);
    }

    // Close connection if it's open before exiting AJAX call
    if (isset($conn) && $conn->ping()) {
        $conn->close();
    }
    exit;
}

// Close connection if open for the full page load
if (isset($conn) && $conn->ping()) {
    $conn->close();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View All Packages</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen">

    <div class="container mx-auto p-4 sm:p-8">
        
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-extrabold text-gray-900">
                📋 All Created Packages (<span id="package-count"><?php echo count($packages); ?></span>)
            </h1>
            <a href="create_package.html" class="px-4 py-2 bg-indigo-600 text-white font-medium rounded-lg shadow-md hover:bg-indigo-700 transition duration-150">
                + Create New Package
            </a>
        </div>

        <div class="mb-6 flex space-x-3">
            <input 
                type="text" 
                id="search-input"
                name="search" 
                placeholder="Search by Code, Name, or Description..." 
                value="<?php echo htmlspecialchars($searchTerm); ?>"
                class="flex-grow px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
            >
            <button 
                id="clear-search-btn"
                class="px-4 py-2 bg-gray-300 text-gray-700 font-medium rounded-lg shadow-md hover:bg-gray-400 transition duration-150 flex items-center <?php echo (empty($searchTerm) ? 'hidden' : ''); ?>"
            >
                Clear Search
            </button>
        </div>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative mb-6" role="alert">
                <?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
            </div>
        <?php elseif (isset($_SESSION['error_message'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative mb-6" role="alert">
                <span class="font-bold">Error:</span> <?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative mb-6" role="alert">
                <span class="font-bold">Error:</span> <?php echo $error; ?>
            </div>
        <?php else: ?>

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
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody id="packages-tbody" class="bg-white divide-y divide-gray-200">
                    
                    <?php 
                    // Render the rows based on the initial fetch (or search if present in URL)
                    echo render_package_rows($packages); 
                    ?>

                </tbody>
            </table>
        </div>

        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('search-input');
            const tbody = document.getElementById('packages-tbody');
            const countDisplay = document.getElementById('package-count');
            const clearButton = document.getElementById('clear-search-btn');

            let searchTimeout;

            // Function to fetch and update results
            const fetchResults = async (query) => {
                // Show loading state
                tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-indigo-500 font-semibold">Loading results...</td></tr>';
                
                // Construct the URL for AJAX request
                const url = `view_packages.php?search=${encodeURIComponent(query)}&is_ajax=true`;

                try {
                    const response = await fetch(url);
                    const html = await response.text();
                    
                    // Update table body
                    tbody.innerHTML = html;

                    // Update package count
                    const rowCount = tbody.querySelectorAll('tr').length;
                    
                    // Check for the "No packages found" row (colspan="6")
                    if (rowCount === 1 && tbody.querySelector('td[colspan="6"]')) {
                        countDisplay.textContent = '0';
                    } else {
                        countDisplay.textContent = rowCount;
                    }

                } catch (error) {
                    console.error("Live search failed:", error);
                    tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-red-500">Error loading data. See console for details.</td></tr>';
                    countDisplay.textContent = '...';
                }
            };

            // Input Event Listener
            searchInput.addEventListener('input', () => {
                const query = searchInput.value.trim();
                
                // Show/hide clear button
                if (query.length > 0) {
                    clearButton.classList.remove('hidden');
                } else {
                    clearButton.classList.add('hidden');
                }

                // Clear any existing timeout
                clearTimeout(searchTimeout);

                // Set a new timeout for debounce
                searchTimeout = setTimeout(() => {
                    fetchResults(query);
                }, 300);
            });

            // Clear Button Listener
            clearButton.addEventListener('click', (e) => {
                e.preventDefault();
                searchInput.value = '';
                clearButton.classList.add('hidden');
                clearTimeout(searchTimeout);
                fetchResults(''); // Fetch all results
            });
            
            // Initial check for clear button visibility (in case of URL search)
            if (searchInput.value.length > 0) {
                 clearButton.classList.remove('hidden');
            }
        });
    </script>
</body>
</html>