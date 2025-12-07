<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/_auth.php';
header('Content-Type: application/json');

// Only allow logged-in staff
require_staff_auth();

// Expect a 'plan' GET parameter (e.g., ?plan=basic). If not provided, return empty.
$plan = isset($_GET['plan']) ? trim($_GET['plan']) : '';
if ($plan === '') {
    echo json_encode([]);
    exit;
}

// Ensure packages table has package_plan column (basic sanity check)
$colCheckSql = "SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'packages' AND COLUMN_NAME = 'package_plan'";
$res = mysqli_query($conn, $colCheckSql);
$row = $res ? mysqli_fetch_assoc($res) : null;
if (!$row || (int)$row['cnt'] === 0) {
    echo json_encode([]);
    exit;
}

// Query packages by package_plan
$sql = "SELECT package_name, package_description, package_plan FROM packages WHERE package_plan = ? ORDER BY package_id DESC";
$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    echo json_encode([]);
    exit;
}
mysqli_stmt_bind_param($stmt, 's', $plan);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$out = [];
while ($r = mysqli_fetch_assoc($result)) {
    $out[] = $r;
}

echo json_encode($out);
