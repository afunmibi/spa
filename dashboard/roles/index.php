<?php
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../csrf.php';

// Require login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../login.php');
    exit;
}

// Only super_admin can manage roles
if (!isset($_SESSION['role']) || $_SESSION['role'] !== ROLE_SUPER_ADMIN) {
    echo "<h2>Access denied</h2><p>You must be a super admin to manage roles.</p>";
    exit;
}

// Fetch users
try {
    $stmt = $pdo->query("SELECT id, username, staff_id, role, email FROM users ORDER BY username ASC");
    $users = $stmt->fetchAll();
} catch (Exception $e) {
    $users = [];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Role Management</title>
    <link href="/spa/dashboard/../assets/js/tailwind.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="p-6 bg-gray-50">
    <div class="max-w-5xl mx-auto bg-white p-6 rounded shadow">
        <h1 class="text-2xl font-bold mb-4">Role Management</h1>
        <p class="mb-4">Only <strong>super_admin</strong> can assign roles.</p>

        <?php if (!empty($_SESSION['role_change_success'])): ?>
            <div class="p-3 bg-green-100 border border-green-300 text-green-800 rounded mb-4"><?php echo htmlspecialchars($_SESSION['role_change_success']); unset($_SESSION['role_change_success']); ?></div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['role_change_error'])): ?>
            <div class="p-3 bg-red-100 border border-red-300 text-red-800 rounded mb-4"><?php echo htmlspecialchars($_SESSION['role_change_error']); unset($_SESSION['role_change_error']); ?></div>
        <?php endif; ?>

        <table class="w-full table-auto border-collapse">
            <thead>
                <tr class="bg-gray-100">
                    <th class="p-2 text-left">Username</th>
                    <th class="p-2 text-left">Email</th>
                    <th class="p-2 text-left">Staff ID</th>
                    <th class="p-2 text-left">Role</th>
                    <th class="p-2 text-left">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr class="border-t">
                        <td class="p-2"><?php echo htmlspecialchars($u['username']); ?></td>
                        <td class="p-2"><?php echo htmlspecialchars($u['email'] ?? ''); ?></td>
                        <td class="p-2"><?php echo htmlspecialchars($u['staff_id'] ?? ''); ?></td>
                        <td class="p-2"><?php echo htmlspecialchars($u['role'] ?? ''); ?></td>
                        <td class="p-2">
                            <form method="POST" action="update_role.php" class="flex items-center space-x-2">
                                <?php echo csrf_input(); ?>
                                <input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>">
                                <select name="new_role" class="px-2 py-1 border rounded">
                                    <?php foreach ($ALLOWED_ROLES as $r): ?>
                                        <option value="<?php echo $r; ?>" <?php echo ($u['role'] === $r) ? 'selected' : ''; ?>><?php echo $r; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="px-3 py-1 bg-indigo-600 text-white rounded">Save</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
