<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/_auth.php';
header('Content-Type: application/json');

// Require staff login to use this endpoint
require_staff_auth();

$q = trim($_GET['q'] ?? '');
if ($q === '') {
    echo json_encode([]);
    exit;
}

// Use PDO for clean parameter binding
try {
    // Search enrolment by policy_no, principal_name or phone
    // Use a hybrid approach: prefer package_id join, fall back to plan_type match
    $like = "%" . $q . "%";
    $sql = "SELECT 
                e.id, e.policy_no, e.principal_name, e.phone, e.dob, e.plan_type,
                p.id AS package_id, p.package_name, p.package_description, p.package_price, p.package_plan
            FROM enrolment e
            LEFT JOIN packages p ON (
                e.package_id = p.id 
                OR (e.package_id IS NULL OR e.package_id = 0) AND LOWER(TRIM(e.plan_type)) COLLATE utf8mb4_unicode_ci = LOWER(TRIM(p.package_plan)) COLLATE utf8mb4_unicode_ci
            )
            WHERE e.policy_no LIKE :like OR e.principal_name LIKE :like OR e.phone LIKE :like
            ORDER BY e.id DESC
            LIMIT 50";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':like' => $like]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // DEBUG: Log results
    error_log("policy_search.php: query='$q', like='$like', rows_found=" . count($rows));
    foreach ($rows as $r) {
        error_log("  row: id={$r['id']}, policy_no={$r['policy_no']}, package_id={$r['package_id']}, plan_type={$r['plan_type']}, package_plan={$r['package_plan']}");
    }

    if (empty($rows)) {
        echo json_encode([]);
        exit;
    }

    // Group packages by enrolment id
    $enrolmentMap = [];
    foreach ($rows as $row) {
        $eid = $row['id'];
        if (!isset($enrolmentMap[$eid])) {
            $enrolmentMap[$eid] = [
                'id' => $row['id'],
                'policy_no' => $row['policy_no'],
                'principal_name' => $row['principal_name'],
                'phone' => $row['phone'],
                'dob' => $row['dob'],
                'plan_type' => $row['plan_type'],
                'packages' => []
            ];
        }
        // Add package if present (LEFT JOIN may have NULL package fields)
        if (!empty($row['package_id'])) {
            $enrolmentMap[$eid]['packages'][] = [
                'package_id' => $row['package_id'],
                'package_name' => $row['package_name'],
                'package_description' => $row['package_description'],
                'package_price' => $row['package_price'],
                'package_plan' => $row['package_plan']
            ];
        }
    }

    $out = array_values($enrolmentMap);
    echo json_encode($out);

} catch (Exception $e) {
    // Don't leak internals; log and return empty
    error_log('policy_search error: ' . $e->getMessage());
    echo json_encode([]);
}
