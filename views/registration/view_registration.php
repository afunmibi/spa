<?php
require_once __DIR__ . '/../../db.php';

// Require staff login
if (empty($_SESSION['staff_id']) && empty($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    echo "<p>Invalid enrollee ID. <a href=\"search_registration.php\">Back to search</a></p>";
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT * FROM enrolment WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($res);
if (!$row) {
    echo "<p>Enrollee not found. <a href=\"search_registration.php\">Back to search</a></p>";
    exit;
}

// Fetch dependants
$depStmt = mysqli_prepare($conn, "SELECT * FROM dependants WHERE enrolment_id = ? ORDER BY id ASC");
mysqli_stmt_bind_param($depStmt, 'i', $id);
mysqli_stmt_execute($depStmt);
$depRes = mysqli_stmt_get_result($depStmt);
$dependants = [];
while ($d = mysqli_fetch_assoc($depRes)) {
    $dependants[] = $d;
}

?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>View Registration #<?php echo htmlspecialchars($row['id']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <?php if (file_exists(__DIR__ . '/../../csrf.php')) { require_once __DIR__ . '/../../csrf.php'; echo csrf_meta_tag(); } ?>
    <script src="/spa/assets/js/csrf_fetch.js"></script>
</head>
<body class="p-6 bg-gray-50">
    <div class="max-w-3xl mx-auto bg-white p-6 rounded shadow">
        <h1 class="text-2xl font-bold mb-4">Enrollee Details — ID #<?php echo htmlspecialchars($row['id']); ?></h1>
        <div class="mb-4">
            <strong>Policy No:</strong> <?php echo htmlspecialchars($row['policy_no']); ?><br>
            <strong>Name:</strong> <?php echo htmlspecialchars($row['principal_name']); ?><br>
            <strong>Email:</strong> <?php echo htmlspecialchars($row['email']); ?><br>
            <strong>Phone:</strong> <?php echo htmlspecialchars($row['phone']); ?><br>
            <strong>DOB:</strong> <?php echo htmlspecialchars($row['dob']); ?><br>
            <strong>Plan:</strong> <?php echo htmlspecialchars($row['plan_type']); ?><br>
            <strong>Organization:</strong> <?php echo htmlspecialchars($row['organization_name']); ?><br>
            <strong>Location:</strong> <?php echo htmlspecialchars($row['location']); ?><br>
            <strong>HCP:</strong> <?php echo htmlspecialchars($row['hcp']); ?><br>
        </div>

        <?php if (!empty($row['photo_path']) && file_exists(dirname(__DIR__,2) . '/' . $row['photo_path'])): ?>
            <div class="mb-4">
                <img src="/spa/<?php echo htmlspecialchars($row['photo_path']); ?>" alt="Photo" class="w-40 h-40 object-cover rounded">
            </div>
        <?php endif; ?>

        <div class="flex space-x-3 mb-6">
            <a class="px-4 py-2 bg-indigo-600 text-white rounded" href="update_registration.php?id=<?php echo $row['id']; ?>">Edit</a>
            <?php if (!empty($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin'): ?>
                <a class="px-4 py-2 bg-red-600 text-white rounded" href="delete_registration.php?id=<?php echo $row['id']; ?>">Delete</a>
            <?php endif; ?>
            <a class="px-4 py-2 bg-green-600 text-white rounded" href="download_id_card.php?type=principal&id=<?php echo $row['id']; ?>&policy=<?php echo urlencode($row['policy_no']); ?>">Download ID Card</a>
            <a class="px-4 py-2 bg-gray-200 text-gray-800 rounded" href="search_registration.php">Back to Search</a>
        </div>

        <h2 class="text-xl font-semibold mb-2">Dependants (<?php echo count($dependants); ?>)</h2>
        <?php if (count($dependants) === 0): ?>
            <p class="text-gray-600">No dependants recorded.</p>
        <?php else: ?>
            <ul class="space-y-2">
            <?php foreach ($dependants as $d): ?>
                <li class="p-3 border rounded flex justify-between items-center">
                    <div>
                        <div class="font-semibold"><?php echo htmlspecialchars($d['name']); ?></div>
                        <div class="text-sm text-gray-600">Relationship: <?php echo htmlspecialchars($d['relationship']); ?> | DOB: <?php echo htmlspecialchars($d['dob']); ?></div>
                    </div>
                    <div class="space-x-2">
                        <a class="text-indigo-600" href="download_id_card.php?type=dependant&id=<?php echo $d['id']; ?>&policy=<?php echo urlencode($row['policy_no']); ?>">Download</a>
                    </div>
                </li>
            <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</body>
</html>