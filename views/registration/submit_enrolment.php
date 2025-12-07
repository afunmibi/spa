<?php
// submit_enrolment.php

// 1. Database Connection
require_once dirname(__DIR__, 2) . '/db.php';
require_once dirname(__DIR__, 2) . '/csrf.php';

// Require staff login for submission
if (empty($_SESSION['staff_id']) && empty($_SESSION['user_id'])) {
    header('HTTP/1.1 403 Forbidden');
    die('Forbidden: you must be logged in to submit enrolments.');
}

// Verify CSRF token on POST
if (!verify_csrf()) {
    header('HTTP/1.1 403 Forbidden');
    die('Invalid CSRF token.');
}

// 2. Retrieve Data (Basic Sanitization)
$submitted_policy_no = $_POST['policy_no'] ?? null; // Keep for reference, but we'll regenerate to ensure uniqueness
$principal_name = $_POST['principal_name'] ?? null;
$email = $_POST['email'] ?? null;
$phone = $_POST['phone'] ?? null;
$dob = $_POST['dob'] ?? null;
$address = $_POST['address'] ?? null;
$location = $_POST['location'] ?? null;
$organization_name = $_POST['organization_name'] ?? null;
$hcp = $_POST['hcp'] ?? null;
$plan_type = $_POST['plan_type'] ?? 'INDV';
$staff_id = $_POST['staff_id'] ?? $_SESSION['staff_id'] ?? null;
$dependants_count = $_POST['dependants_count'] ?? 0;

if (!$principal_name) {
    die("Error: Missing required principal name.");
}

// Regenerate policy_no to ensure uniqueness and prevent duplicates
$hmo_code = '051';
$hmo_name = 'NONSUCH';
$rand = rand(10000, 99999); // Use 5-digit random to reduce collision chance
$current_year = date('Y');
$current_month = date('m');

// Query for last ID with this plan combination and increment
$last_id = 0;
if (isset($conn) && $conn instanceof mysqli) {
    $sql_check = "SELECT MAX(id) AS max_id FROM enrolment WHERE plan_type = ?";
    if ($stmt_check = $conn->prepare($sql_check)) {
        $stmt_check->bind_param('s', $plan_type);
        $stmt_check->execute();
        $stmt_check->bind_result($max_id);
        $stmt_check->fetch();
        $last_id = $max_id ? (int)$max_id : 0;
        $stmt_check->close();
    }
}
$new_id = $last_id + 1;

// Build unique policy number
$policy_no = $hmo_code . '/' . strtoupper($hmo_name) . '/' . $rand . '/' . $current_year . '/' . $current_month . '/' . $plan_type . '/' . $new_id;

// 3. File Handling (Important: needs a safe upload directory)
// Central uploads directory (project root)/uploads/registration
$uploadsRoot = dirname(__DIR__, 2) . '/uploads/registration/';
if (!is_dir($uploadsRoot)) mkdir($uploadsRoot, 0755, true);

function validate_and_move_upload($fieldName, $allowedMime, $maxBytes, $prefix, $uploadsRoot) {
    if (empty($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) return null;
    $f = $_FILES[$fieldName];
    if ($f['size'] > $maxBytes) {
        throw new Exception("Uploaded file for $fieldName exceeds size limit.");
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($f['tmp_name']);
    // Additional image validation for image types
    if (strpos($mime, 'image/') === 0) {
        $img = @getimagesize($f['tmp_name']);
        if ($img === false) throw new Exception("Uploaded file for $fieldName is not a valid image.");
    }
    if (is_array($allowedMime)) {
        if (!in_array($mime, $allowedMime, true)) throw new Exception("Invalid file type for $fieldName: $mime");
    } else {
        if ($mime !== $allowedMime) throw new Exception("Invalid file type for $fieldName: $mime");
    }
    $ext = '';
    switch ($mime) {
        case 'image/jpeg': $ext = 'jpg'; break;
        case 'image/png': $ext = 'png'; break;
        case 'image/webp': $ext = 'webp'; break;
        case 'application/pdf': $ext = 'pdf'; break;
        default: $ext = pathinfo($f['name'], PATHINFO_EXTENSION); break;
    }
    $filename = $prefix . '_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    $absPath = $uploadsRoot . $filename;
    if (!move_uploaded_file($f['tmp_name'], $absPath)) {
        throw new Exception("Failed to move uploaded file for $fieldName.");
    }
    // Return web-accessible relative path from project root
    return 'uploads/registration/' . $filename;
}

$photo_path = null;
$iddoc_path = null;
try {
    // principal photo: images up to 2MB
    $photo_path = validate_and_move_upload('principal_photo', ['image/jpeg','image/png','image/webp'], 2 * 1024 * 1024, $policy_no . '_photo', $uploadsRoot);
    // principal id doc: pdf or images up to 5MB
    $iddoc_path = validate_and_move_upload('principal_id', ['application/pdf','image/jpeg','image/png','image/webp'], 5 * 1024 * 1024, $policy_no . '_id', $uploadsRoot);
} catch (Exception $e) {
    die('Upload error: ' . $e->getMessage());
}

// 4. INSERT INTO ENROLMENT TABLE
// include id_document_path column
$sql_principal = "INSERT INTO enrolment (policy_no, principal_name, email, phone, dob, address, location, organization_name, hcp, plan_type, staff_id, photo_path, id_document_path, date_enrolled) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

try {
    if ($conn instanceof PDO) {
        // PDO path
        $stmt = $conn->prepare($sql_principal);
        $stmt->execute([$policy_no, $principal_name, $email, $phone, $dob, $address, $location, $organization_name, $hcp, $plan_type, $staff_id, $photo_path, $iddoc_path]);
        $principal_id = $conn->lastInsertId();

        for ($i = 1; $i <= $dependants_count; $i++) {
            $dep_name = $_POST["dependant_{$i}_name"] ?? null;
            $dep_rel = $_POST["dependant_{$i}_relationship"] ?? null;
            $dep_dob = $_POST["dependant_{$i}_dob"] ?? null;

            if ($dep_name && $dep_rel) {
                $sql_dependant = "INSERT INTO dependants (enrolment_id, policy_no, name, relationship, dob) VALUES (?, ?, ?, ?, ?)";
                $dep_stmt = $conn->prepare($sql_dependant);
                $dep_stmt->execute([$principal_id, $policy_no, $dep_name, $dep_rel, $dep_dob]);
            }
        }

    } elseif ($conn instanceof mysqli) {
        // mysqli path
        $stmt = $conn->prepare($sql_principal);
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        $stmt->bind_param('sssssssssssss', $policy_no, $principal_name, $email, $phone, $dob, $address, $location, $organization_name, $hcp, $plan_type, $staff_id, $photo_path, $iddoc_path);
        $stmt->execute();
        $principal_id = $conn->insert_id;

        for ($i = 1; $i <= $dependants_count; $i++) {
            $dep_name = $_POST["dependant_{$i}_name"] ?? null;
            $dep_rel = $_POST["dependant_{$i}_relationship"] ?? null;
            $dep_dob = $_POST["dependant_{$i}_dob"] ?? null;

            if ($dep_name && $dep_rel) {
                $sql_dependant = "INSERT INTO dependants (enrolment_id, policy_no, name, relationship, dob) VALUES (?, ?, ?, ?, ?)";
                $dep_stmt = $conn->prepare($sql_dependant);
                if (!$dep_stmt) {
                    throw new Exception('Prepare failed: ' . $conn->error);
                }
                $dep_stmt->bind_param('issss', $principal_id, $policy_no, $dep_name, $dep_rel, $dep_dob);
                $dep_stmt->execute();
                $dep_stmt->close();
            }
        }

        $stmt->close();

    } else {
        throw new Exception('Unknown DB connection type.');
    }

    // 6. Success Response with ID Card Download Options
    echo "<!DOCTYPE html>";
    echo "<html>";
    echo "<head>";
    echo "<meta charset='UTF-8'>";
    echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
    echo "<title>Registration Successful</title>";
    echo "<style>";
    echo "body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }";
    echo ".success-container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
    echo ".success-header { text-align: center; color: #27ae60; margin-bottom: 20px; }";
    echo ".success-header h1 { font-size: 28px; margin: 10px 0; }";
    echo ".policy-info { background: #e8f5e9; border-left: 4px solid #4CAF50; padding: 15px; margin: 20px 0; border-radius: 4px; }";
    echo ".policy-info strong { color: #2e7d32; }";
    echo ".cards-section { margin: 30px 0; }";
    echo ".cards-section h2 { font-size: 20px; color: #333; margin-bottom: 15px; }";
    echo ".card-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }";
    echo ".card-item { border: 1px solid #ddd; padding: 15px; border-radius: 6px; background: #fafafa; text-align: center; }";
    echo ".card-item h3 { font-size: 16px; color: #333; margin-bottom: 10px; }";
    echo ".btn { display: inline-block; padding: 10px 20px; margin: 5px; border-radius: 5px; text-decoration: none; font-weight: bold; cursor: pointer; border: none; font-size: 14px; }";
    echo ".btn-primary { background-color: #2196F3; color: white; }";
    echo ".btn-primary:hover { background-color: #1976D2; }";
    echo ".btn-success { background-color: #4CAF50; color: white; }";
    echo ".btn-success:hover { background-color: #45a049; }";
    echo ".action-buttons { text-align: center; margin-top: 30px; border-top: 1px solid #ddd; padding-top: 20px; }";
    echo ".generated-date { font-size: 12px; color: #999; text-align: center; margin-top: 20px; }";
    echo "</style>";
    echo "</head>";
    echo "<body>";
    
    echo "<div class='success-container'>";
    echo "<div class='success-header'>";
    echo "<h1>✅ Registration Successful!</h1>";
    echo "<p>Your enrollment has been processed successfully.</p>";
    echo "</div>";
    
    echo "<div class='policy-info'>";
    echo "<strong>Policy Number:</strong> " . htmlspecialchars($policy_no) . "<br>";
    echo "<strong>Principal Name:</strong> " . htmlspecialchars($principal_name) . "<br>";
    echo "<strong>Plan Type:</strong> " . htmlspecialchars($plan_type);
    echo "</div>";
    
    echo "<div class='cards-section'>";
    echo "<h2>📋 Download Identity Cards</h2>";
    
    // Principal Card
    echo "<div class='card-grid'>";
    echo "<div class='card-item'>";
    echo "<h3>Principal Card</h3>";
    echo "<p style='font-size: 13px; color: #666; margin-bottom: 10px;'>" . htmlspecialchars($principal_name) . "</p>";
    echo "<a href='download_id_card.php?type=principal&policy=" . urlencode($policy_no) . "&id=" . $principal_id . "' class='btn btn-primary'>📥 Download Card</a>";
    echo "</div>";
    
    // Dependants Cards (if any)
    if ($dependants_count > 0) {
        echo "<div class='card-item'>";
        echo "<h3>Dependants Cards</h3>";
        echo "<p style='font-size: 13px; color: #666; margin-bottom: 10px;'>(" . $dependants_count . " dependant(s))</p>";
        echo "<a href='download_id_card.php?type=dependants&policy=" . urlencode($policy_no) . "&id=" . $principal_id . "' class='btn btn-primary'>📥 Download All</a>";
        echo "</div>";
    }
    
    echo "</div>";
    echo "</div>";
    
    echo "<div class='action-buttons'>";
    echo "<a href='create_registration.php' class='btn btn-success'>← Register Another Member</a>";
    echo "<a href='../../../dashboard/admin_dashboard.php' class='btn' style='background-color: #9C27B0; color: white;'>Go to Dashboard →</a>";
    echo "</div>";
    
    echo "<div class='generated-date'>";
    echo "Generated on: " . date('Y-m-d H:i:s');
    echo "</div>";
    
    echo "</div>";
    echo "</body>";
    echo "</html>";

} catch (Exception $e) {
    echo "Error inserting data: " . $e->getMessage();
    echo "<p style='margin-top: 20px;'><a href='create_registration.php' style='background-color: #2196F3; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>← Back to Registration</a></p>";
}

// Close/cleanup connection
if ($conn instanceof mysqli) {
    $conn->close();
} else {
    $conn = null;
}
?>