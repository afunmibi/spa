<?php

require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../csrf.php';

// Require staff login
if (empty($_SESSION['staff_id']) && empty($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // process update
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
        echo "Invalid ID"; exit;
    }
    // verify csrf
    if (!verify_csrf()) {
        die('Invalid CSRF token');
    }
    $principal_name = trim($_POST['principal_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $dob = trim($_POST['dob'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $organization_name = trim($_POST['organization_name'] ?? '');
    $plan_type = trim($_POST['plan_type'] ?? '');

    // optional photo upload (validated)
    $photo_path = null;
    if (!empty($_FILES['principal_photo']) && $_FILES['principal_photo']['error'] === UPLOAD_ERR_OK) {
        $uploadsRoot = dirname(__DIR__,2) . '/uploads/registration/';
        if (!is_dir($uploadsRoot)) mkdir($uploadsRoot, 0755, true);

        // reuse validation logic similar to submit_enrolment
        $f = $_FILES['principal_photo'];
        if ($f['size'] > 2 * 1024 * 1024) {
            die('Photo exceeds 2MB size limit');
        }
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($f['tmp_name']);
        $allowed = ['image/jpeg','image/png','image/webp'];
        if (!in_array($mime, $allowed, true)) {
            die('Invalid photo file type');
        }
        // extra check
        $img = @getimagesize($f['tmp_name']);
        if ($img === false) die('Not a valid image file');

        $ext = ($mime === 'image/jpeg') ? 'jpg' : (($mime === 'image/png') ? 'png' : 'webp');
        $filename = 'principal_' . $id . '_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $abs = $uploadsRoot . $filename;
        if (!move_uploaded_file($f['tmp_name'], $abs)) {
            die('Failed to move uploaded file.');
        }
        $photo_path = 'uploads/registration/' . $filename;

        // delete old photo if present and within uploads folder
        if (!empty($row['photo_path']) && strpos($row['photo_path'], 'uploads/registration/') === 0) {
            $old = dirname(__DIR__,2) . '/' . $row['photo_path'];
            if (file_exists($old)) @unlink($old);
        }
    }

    // Build update query
    $fields = 'principal_name=?, email=?, phone=?, dob=?, address=?, location=?, organization_name=?, plan_type=?, updated_at=NOW()';
    $params = [$principal_name, $email, $phone, $dob, $address, $location, $organization_name, $plan_type];
    $types = 'ssssssss';
    if ($photo_path) {
        $fields = 'principal_name=?, email=?, phone=?, dob=?, address=?, location=?, organization_name=?, plan_type=?, photo_path=?, updated_at=NOW()';
        $params[] = $photo_path;
        $types = 'sssssssss';
    }
    $params[] = $id;
    $types .= 'i';

    $sql = "UPDATE enrolment SET $fields WHERE id=?";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        die('Prepare failed: ' . mysqli_error($conn));
    }
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    $ok = mysqli_stmt_execute($stmt);
    if (!$ok) {
        $err = mysqli_stmt_error($stmt);
        die('Update failed: ' . $err);
    }
    header('Location: view_registration.php?id=' . $id);
    exit;
}

// GET: render form
if ($id <= 0) {
    echo "<p>Invalid ID. <a href=\"search_registration.php\">Back</a></p>";
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT * FROM enrolment WHERE id = ? LIMIT 1");
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
    <title>Edit Registration #<?php echo htmlspecialchars($row['id']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>label{display:block;margin-bottom:.25rem;font-weight:600}</style>
</head>
<body class="p-6 bg-gray-50">
    <div class="max-w-3xl mx-auto bg-white p-6 rounded shadow">
        <h1 class="text-2xl font-bold mb-4">Edit Enrollee — ID #<?php echo htmlspecialchars($row['id']); ?></h1>
                <form method="post" enctype="multipart/form-data">
                    <?php echo csrf_input(); ?>
            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
            <div class="mb-4">
                <label>Full name</label>
                <input name="principal_name" value="<?php echo htmlspecialchars($row['principal_name']); ?>" class="w-full border px-3 py-2 rounded">
            </div>
            <div class="mb-4">
                <label>Email</label>
                <input name="email" value="<?php echo htmlspecialchars($row['email']); ?>" class="w-full border px-3 py-2 rounded">
            </div>
            <div class="mb-4">
                <label>Phone</label>
                <input name="phone" value="<?php echo htmlspecialchars($row['phone']); ?>" class="w-full border px-3 py-2 rounded">
            </div>
            <div class="mb-4">
                <label>DOB</label>
                <input type="date" name="dob" value="<?php echo htmlspecialchars($row['dob']); ?>" class="w-full border px-3 py-2 rounded">
            </div>
            <div class="mb-4">
                <label>Address</label>
                <input name="address" value="<?php echo htmlspecialchars($row['address']); ?>" class="w-full border px-3 py-2 rounded">
            </div>
            <div class="mb-4">
                <label>Organization</label>
                <input name="organization_name" value="<?php echo htmlspecialchars($row['organization_name']); ?>" class="w-full border px-3 py-2 rounded">
            </div>
            <div class="mb-4">
                <label>Location</label>
                <input name="location" value="<?php echo htmlspecialchars($row['location']); ?>" class="w-full border px-3 py-2 rounded">
            </div>
            <div class="mb-4">
                <label>Plan Type</label>
                <select name="plan_type" class="w-full border px-3 py-2 rounded">
                    <?php $opts = ['INDV','FAMILY','CORPORATE','GROUP']; foreach ($opts as $o): ?>
                        <option value="<?php echo $o; ?>" <?php if ($row['plan_type']==$o) echo 'selected'; ?>><?php echo $o; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-4">
                <label>Upload Principal Photo (optional)</label>
                <input type="file" name="principal_photo" accept="image/*">
            </div>
            <div class="flex space-x-3">
                <button class="px-4 py-2 bg-indigo-600 text-white rounded">Save</button>
                <a href="view_registration.php?id=<?php echo $row['id']; ?>" class="px-4 py-2 bg-gray-200 rounded">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>