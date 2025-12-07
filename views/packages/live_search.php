<?php
// live_search.php - Returns HTML table rows based on a search query

// 1. Include the Database Connection
require_once dirname(__DIR__, 2) . '/db.php'; 

header('Content-Type: text/html');

$query = trim($_GET['q'] ?? '');
$plan_filter = trim($_GET['plan'] ?? '');

if (empty($query) && empty($plan_filter)) {
    // Colspan is 6 (ID, Name, Plan, Price, Code, Actions)
    echo '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">Enter a keyword or select a plan to search.</td></tr>';
    exit;
}

// 2. Prepare SQL for live search
$sql = "SELECT package_id, package_name, package_description, package_price, package_plan, package_code 
        FROM packages 
        WHERE 1=1 "; 

$bind_types = '';
$bind_values = [];
$output_html = '';

// Add keyword search condition
if (!empty($query)) {
    $sql .= " AND (package_name LIKE ? OR package_code LIKE ? OR package_description LIKE ?)";
    $like_query = '%' . $query . '%';
    
    $bind_types .= 'sss';
    $bind_values[] = $like_query;
    $bind_values[] = $like_query;
    $bind_values[] = $like_query;
}

// Add plan filter condition
if (!empty($plan_filter)) {
    $sql .= " AND package_plan = ?";
    $bind_types .= 's';
    $bind_values[] = $plan_filter;
}

$sql .= " ORDER BY package_id DESC LIMIT 10"; 

try {
    $stmt = $mysqli->prepare($sql);
    
    if ($stmt) {
        // Dynamic binding fix
        if (!empty($bind_types)) {
            $bind_refs = [];
            foreach ($bind_values as $key => $value) {
                $bind_refs[$key] = &$bind_values[$key];
            }
            array_unshift($bind_refs, $bind_types);
            call_user_func_array([$stmt, 'bind_param'], $bind_refs);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            while ($package = $result->fetch_assoc()) {
                
                $packageId = htmlspecialchars($package['package_id']);
                
                // Build the HTML table row
                $output_html .= '<tr class="hover:bg-gray-50">';
                $output_html .= '<td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">' . $packageId . '</td>';
                $output_html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">';
                $output_html .= '<p class="font-semibold">' . htmlspecialchars($package['package_name']) . '</p>';
                $output_html .= '<p class="text-xs text-gray-500 truncate w-48">' . htmlspecialchars($package['package_description']) . '</p>';
                $output_html .= '</td>';
                $output_html .= '<td class="px-6 py-4 whitespace-nowrap"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800">' . htmlspecialchars(ucwords($package['package_plan'])) . '</span></td>';
                $output_html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-bold">₦' . number_format($package['package_price'], 2) . '</td>';
                $output_html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-mono">' . htmlspecialchars($package['package_code']) . '</td>';
                
                // --- Actions Column (Styled Links) ---
                $output_html .= '<td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">';
                
                // Edit Link/Button
                $output_html .= '<a href="update_packages.php?id=' . $packageId . '" ';
                $output_html .= 'class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-2">';
                $output_html .= 'Edit';
                $output_html .= '</a>';
                
                // Delete Link/Button with Confirmation
                $output_html .= '<a href="delete_packages.php?id=' . $packageId . '" ';
                $output_html .= 'class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500" ';
                $output_html .= 'onclick="return confirm(\'Are you sure you want to delete package ID ' . $packageId . '?\')">';
                $output_html .= 'Delete';
                $output_html .= '</a>';
                
                $output_html .= '</td>';
                // --- END Actions Column ---
                
                $output_html .= '</tr>';
            }
        } else {
             $output_html .= '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">No live results found. Try a broader search.</td></tr>';
        }
        
        $stmt->close();
    } else {
        error_log("MySQLi Prepare failed in live search: " . $mysqli->error);
        $output_html = '<tr><td colspan="6" class="px-6 py-4 text-center text-red-500">A database preparation error occurred.</td></tr>';
    }
} catch (Exception $e) {
    error_log("Live Search Error: " . $e->getMessage());
    $output_html = '<tr><td colspan="6" class="px-6 py-4 text-center text-red-500">A system error occurred.</td></tr>';
}

echo $output_html;
?>