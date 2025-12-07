<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/_auth.php';
header('Content-Type: application/json');

// Require staff to be logged in to access enrollee list
require_staff_auth();

// Simple GET endpoint to return all enrolments
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$sql = "SELECT id, policy_no, principal_name, phone, dob, plan_type FROM enrolment ORDER BY id DESC";
$res = mysqli_query($conn, $sql);
$out = [];
$plans = [];
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $plan = $row['plan_type'] ?? null;
        if ($plan) $plans[$plan] = true;
        $out[] = [
            'id' => $row['id'],
            'policy_no' => $row['policy_no'],
            'principal_name' => $row['principal_name'],
            'phone' => $row['phone'],
            'dob' => $row['dob'],
            'plan_type' => $plan,
            // packages will be attached later
            'packages' => [],
        ];
    }
}

// If we found plan types, fetch packages for those plans and attach
if (!empty($plans) && isset($pdo)) {
    $planList = array_keys($plans);
    // build named placeholders for PDO
    $placeholders = implode(',', array_fill(0, count($planList), '?'));
    // prepare WHERE clause for substring, case-insensitive matching
    $whereParts = array_fill(0, count($planList), 'LOWER(package_plan) LIKE LOWER(?)');
    $sql2 = "SELECT package_name, package_description, package_plan FROM packages WHERE (" . implode(' OR ', $whereParts) . ") ORDER BY package_id DESC";
    try {
        $stmt = $pdo->prepare($sql2);
        // execute with patterns (substring match)
        $patterns = array_map(function($p){ return "%{$p}%"; }, $planList);
        $stmt->execute($patterns);
        $rows = $stmt->fetchAll();
        $pkgsByPlan = [];
        foreach ($rows as $prow) {
            $pkgsByPlan[$prow['package_plan']][] = [
                'package_name' => $prow['package_name'],
                'package_description' => $prow['package_description'],
                'package_plan' => $prow['package_plan'],
            ];
        }

        // attach packages to each enrollee
        foreach ($out as &$en) {
            $pt = $en['plan_type'];
            if ($pt && isset($pkgsByPlan[$pt])) {
                $en['packages'] = $pkgsByPlan[$pt];
            }
        }
        unset($en);
    } catch (Exception $e) {
        // failed to fetch packages via PDO; skip attaching
        error_log('Failed to fetch packages by plan: ' . $e->getMessage());
    }
}

echo json_encode($out);
