<?php
// fetch_plans.php - Returns unique package plans as JSON

header('Content-Type: application/json');

// 1. Include the Database Connection
require_once dirname(__DIR__, 2) . '/db.php'; 

$plans = [];

try {
    $sql = "SELECT DISTINCT package_plan FROM packages ORDER BY package_plan ASC";
    $result = $mysqli->query($sql);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $plans[] = $row['package_plan'];
        }
        $result->free();
    }
} catch (Exception $e) {
    error_log("Error fetching distinct plans: " . $e->getMessage());
}

echo json_encode($plans);
?>