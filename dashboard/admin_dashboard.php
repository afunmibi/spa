<?php
// admin_dashboard.php
session_start();

// Check if the user is logged in, if not then redirect to the login page
// Assuming your login page is now 'login.php' and it's located in the parent directory (../)
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}

// User is logged in, retrieve and sanitize the username
$username = htmlspecialchars($_SESSION['username']);

// --- DATABASE CONNECTION (Optional: Uncomment if you need to fetch live data) ---
/*
require_once '../db.php'; // Adjust path if db.php is in a different location

// Example: Fetch Total Users (You would replace the hardcoded stats in the HTML)
$total_users = 0;
$stmt = $conn->prepare("SELECT COUNT(id) AS total FROM users");
if ($stmt->execute()) {
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $total_users = $data['total'];
}
$stmt->close();
// Note: Only close $conn if you won't use it again in this script.
*/
// --- END DATABASE CONNECTION ---
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo $username; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 font-sans antialiased">

    <div id="app" class="flex h-screen">

        <aside class="w-64 bg-gray-800 text-white flex flex-col">
            <div class="p-6 text-2xl font-semibold text-indigo-400 border-b border-gray-700">
                Dashboard SPA
            </div>
            <nav class="flex-1 p-4 space-y-2">
                <a href="#" class="block p-3 rounded-lg bg-gray-900 text-indigo-400 hover:bg-gray-700 transition duration-150">
                    🏠 Overview
                </a>
                <a href="../views/registration/create_registration.php" class="block p-3 rounded-lg hover:bg-gray-700 transition duration-150">
                    📝 Create Registration
                </a>
                <a href="../views/registration/search_registration.php" class="block p-3 rounded-lg hover:bg-gray-700 transition duration-150">
                    🔎 Search Registrations
                </a>
                <a href="../views/registration/policy_noANDpackageBenefits.php" class="block p-3 rounded-lg hover:bg-gray-700 transition duration-150">
                    🔍 Policy Search
                </a>
                <a href="#" class="block p-3 rounded-lg hover:bg-gray-700 transition duration-150">
                    👥 Users
                </a>
                <a href="roles/index.php" class="block p-3 rounded-lg hover:bg-gray-700 transition duration-150">
                    🛡️ Roles
                </a>
                <a href="#" class="block p-3 rounded-lg hover:bg-gray-700 transition duration-150">
                    📦 Products
                </a>
                <a href="#" class="block p-3 rounded-lg hover:bg-gray-700 transition duration-150">
                    📊 Analytics
                </a>
            </nav>
            <div class="p-4 border-t border-gray-700">
                <a href="../logout.php" class="block p-3 rounded-lg bg-red-600 text-white text-center hover:bg-red-700 transition duration-150">
                    🚪 Logout
                </a>
            </div>
        </aside>

        <main class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white shadow-md p-4 flex justify-between items-center">
                <h1 class="text-xl font-semibold text-gray-700">Dashboard Overview</h1>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-600">Welcome, **<?php echo $username; ?>**</span>
                    <div class="w-10 h-10 bg-indigo-200 rounded-full flex items-center justify-center font-bold text-indigo-600">
                        <?php echo strtoupper(substr($username, 0, 1)); ?>
                    </div>
                </div>
            </header>

            <div class="flex-1 overflow-x-hidden overflow-y-auto p-6 space-y-6">

                <h2 class="text-2xl font-bold text-gray-800">Quick Stats</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-indigo-500">
                        <p class="text-sm font-medium text-gray-500">Total Enrollees PHIS</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1"><?php // echo number_format($total_users); 
                                                                            ?> 2,450</p>
                    </div>
                    <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-green-500">
                        <p class="text-sm font-medium text-gray-500">Total PA today</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1"></p>
                    </div>
                    <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-yellow-500">
                        <p class="text-sm font-medium text-gray-500">Pending Requests</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1">45</p>
                    </div>
                    <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-red-500">
                        <p class="text-sm font-medium text-gray-500">Total Enrollees NHIA</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1">12</p>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-lg">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">Recent Activity</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                        <div class="m-2 p-4 bg-white shadow-lg rounded-xl">

                            <h3 class="text-2xl font-bold text-gray-800 mb-4">🔍 Search Enrollee</h3>

                            <div class="flex space-x-3 items-center">

                                <input
                                    type="search"
                                    name="searchText"
                                    id="searchText"
                                    placeholder="Enter policy No, name, phone, or DOB to search enrollee details"
                                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150 text-gray-700">

                                <button
                                    class="px-6 py-2 bg-indigo-600 text-white font-semibold rounded-lg shadow-md hover:bg-indigo-700 transition duration-150 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    Search Details
                                </button>

                            </div>

                            <div id="displayDetails" class="mt-4 p-4 border border-gray-200 rounded-lg bg-gray-50 text-gray-700 min-h-[50px]">
                            </div>

                        </div>

                        <div class="m-2 p-4 bg-white shadow-md rounded-lg">
                            <h3 class="text-xl font-semibold mb-2">Div 2</h3>
                            <p class="text-gray-700">
                                Lorem ipsum dolor, sit amet consectetur adipisicing elit. Nam nulla laborum magnam, eaque fuga enim nemo a cum suscipit? Voluptate dicta laborum ab asperiores maxime, itaque velit numquam vitae saepe sit, laboriosam ut omnis beatae vero eveniet ipsum, molestiae quasi ratione accusantium facere praesentium quos possimus natus. Officia, ratione ea.
                            </p>
                        </div>

                        <div class="m-2 p-4 bg-white shadow-md rounded-lg">
                            <h3 class="text-xl font-semibold mb-2">Div 3</h3>
                            <p class="text-gray-700">Lorem ipsum dolor, sit amet consectetur adipisicing elit. Nam nulla laborum magnam, eaque fuga enim nemo a cum suscipit? Voluptate dicta laborum ab asperiores maxime, itaque velit numquam vitae saepe sit, laboriosam ut omnis beatae vero eveniet ipsum, molestiae quasi ratione accusantium facere praesentium quos possimus natus. Officia, ratione ea.</p>
                        </div>

                    </div>
                </div>

            </div>
        </main>
    </div>

</body>

</html>