<?php
// admin_dashboard.php
// session_start();

// // Check if the user is logged in, if not then redirect to login page
// if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
//     header("Location: ../login.html");
//     exit;
// }

// User is logged in, dashboard content starts below
// $username = htmlspecialchars($_SESSION['username']);
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
                <a href="#" class="block p-3 rounded-lg hover:bg-gray-700 transition duration-150">
                    👥 Users
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
                        <p class="text-sm font-medium text-gray-500">Total Users</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1">2,450</p>
                    </div>
                    <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-green-500">
                        <p class="text-sm font-medium text-gray-500">Revenue (MTD)</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1">$12,890</p>
                    </div>
                    <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-yellow-500">
                        <p class="text-sm font-medium text-gray-500">Pending Orders</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1">45</p>
                    </div>
                    <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-red-500">
                        <p class="text-sm font-medium text-gray-500">Support Tickets</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1">12</p>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-lg">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">Recent Activity</h3>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Activity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">New Product added: "Fusion Keyboard"</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">admin_one</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2 min ago</td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">User registered: JaneDoe</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">system</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">15 min ago</td>
                            </tr>
                            </tbody>
                    </table>
                </div>

            </div>
        </main>
    </div>

</body>
</html>