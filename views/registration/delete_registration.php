<?php
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../csrf.php';

// Require staff login
if (empty($_SESSION['staff_id']) && empty($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}

// Require admin role to delete
if (empty($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    echo "<p>Access denied. Admin role required. <a href=\"search_registration.php\">Back</a></p>";
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
        echo "Invalid ID"; exit;
    }
    if (!verify_csrf()) {
        die('Invalid CSRF token');
    }
    // fetch file paths to clean up
    $pstmt = mysqli_prepare($conn, "SELECT photo_path, id_document_path FROM enrolment WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($pstmt, 'i', $id);
    mysqli_stmt_execute($pstmt);
    $pres = mysqli_stmt_get_result($pstmt);
    $prow = mysqli_fetch_assoc($pres);

    // get dependants photos
    $dstmt = mysqli_prepare($conn, "SELECT photo_path FROM dependants WHERE enrolment_id = ?");
    mysqli_stmt_bind_param($dstmt, 'i', $id);
    mysqli_stmt_execute($dstmt);
    $dres = mysqli_stmt_get_result($dstmt);
    $depPhotos = [];
    while ($r = mysqli_fetch_assoc($dres)) { $depPhotos[] = $r['photo_path']; }

    // delete enrolment (dependants cascade if FK set)
    $stmt = mysqli_prepare($conn, "DELETE FROM enrolment WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);

    // cleanup files (only files under uploads/registration/)
    $paths = [];
    if (!empty($prow['photo_path'])) $paths[] = $prow['photo_path'];
    if (!empty($prow['id_document_path'])) $paths[] = $prow['id_document_path'];
    foreach ($depPhotos as $pp) { if ($pp) $paths[] = $pp; }
    foreach ($paths as $rel) {
        if (strpos($rel, 'uploads/registration/') === 0) {
            $abs = dirname(__DIR__,2) . '/' . $rel;
            if (file_exists($abs)) @unlink($abs);
        }
    }
    header('Location: search_registration.php');
    exit;
}

// GET: show confirm
if ($id <= 0) {
    echo "<p>Invalid ID. <a href=\"search_registration.php\">Back</a></p>";
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT id, principal_name, policy_no FROM enrolment WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($res);
if (!$row) {
    echo "<p>Not found. <a href=\"search_registration.php\">Back</a></p>";
    exit;
}

?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Delete Registration #<?php echo htmlspecialchars($row['id']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6 bg-gray-50">
    <div class="max-w-xl mx-auto bg-white p-6 rounded shadow">
        <h1 class="text-xl font-bold mb-4 text-red-600">Confirm Deletion</h1>
        <p>Are you sure you want to delete enrollee <strong><?php echo htmlspecialchars($row['principal_name']); ?></strong> (Policy: <?php echo htmlspecialchars($row['policy_no']); ?>)? This action cannot be undone.</p>
        <form method="post" class="mt-6">
            <?php echo csrf_input(); ?>
            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
            <div class="flex space-x-3">
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded">Yes, Delete</button>
                <a href="view_registration.php?id=<?php echo $row['id']; ?>" class="px-4 py-2 bg-gray-200 rounded">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>
