<?php
// download_id_card.php - Download ID cards as HTML (can be printed to PDF)

require_once dirname(__DIR__, 2) . '/db.php';
require_once 'generate_id_card.php';

$type = $_GET['type'] ?? 'principal'; // 'principal' or 'dependants'
$policy_no = $_GET['policy'] ?? null;
$enrolment_id = $_GET['id'] ?? null;

if (!$policy_no || !$enrolment_id) {
    die("Error: Missing required parameters.");
}

if ($type === 'principal') {
    downloadPrincipalCard($conn, $policy_no, $enrolment_id);
} elseif ($type === 'dependants') {
    downloadDependantsCards($conn, $policy_no, $enrolment_id);
} else {
    die("Error: Invalid type.");
}

function downloadPrincipalCard($conn, $policy_no, $enrolment_id) {
    // Fetch principal data
    $sql = "SELECT id, principal_name, email, phone, dob, organization_name, hcp, photo_path FROM enrolment WHERE id = ? AND policy_no = ?";
    
    if ($conn instanceof mysqli) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('is', $enrolment_id, $policy_no);
        $stmt->execute();
        $result = $stmt->get_result();
        $principal = $result->fetch_assoc();
        $stmt->close();
    } elseif ($conn instanceof PDO) {
        $stmt = $conn->prepare($sql);
        $stmt->execute([$enrolment_id, $policy_no]);
        $principal = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    if (!$principal) {
        die("Error: Principal record not found.");
    }
    
    // Prepare member data for card generation
    $member_data = [
        'name' => $principal['principal_name'],
        'policy_no' => $policy_no,
        'relationship' => 'Principal',
        'dob' => $principal['dob'],
        'email' => $principal['email'],
        'phone' => $principal['phone'],
        'organization_name' => $principal['organization_name'],
        'hcp' => $principal['hcp'],
        'photo_path' => $principal['photo_path']
    ];
    
    // Generate HTML card
    $html = generateIdentityCardHTML($member_data);
    
    // Output as HTML for print-to-PDF
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: inline; filename="ID_Card_Principal_' . sanitize_filename($principal['principal_name']) . '.html"');
    
    // Add navigation and styling
    echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width, initial-scale=1.0'><style>";
    echo "body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; }";
    echo ".nav-bar { background: #003366; color: white; padding: 15px 20px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px; }";
    echo ".nav-bar button { background: white; color: #003366; border: none; padding: 8px 16px; margin: 0 5px; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 14px; }";
    echo ".nav-bar button:hover { background: #e0e0e0; }";
    echo ".print-btn { background: #4CAF50; color: white; }";
    echo ".print-btn:hover { background: #45a049; }";
    echo "@media print { .nav-bar { display: none; } }";
    echo "</style></head><body>";
    echo "<div class='nav-bar'>";
    echo "<button onclick='window.history.back()'>← Back</button>";
    echo "<button class='print-btn' onclick='window.print()'>🖨️ Print / Save as PDF</button>";
    echo "<button onclick=\"window.open('create_registration.php')\">+ New Registration</button>";
    echo "</div>";
    echo $html;
    echo "</body></html>";
}

function downloadDependantsCards($conn, $policy_no, $enrolment_id) {
    // Fetch all dependants for this enrolment
    $sql = "SELECT name, relationship, dob FROM dependants WHERE enrolment_id = ? ORDER BY id ASC";
    
    if ($conn instanceof mysqli) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $enrolment_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $dependants = [];
        while ($row = $result->fetch_assoc()) {
            $dependants[] = $row;
        }
        $stmt->close();
    } elseif ($conn instanceof PDO) {
        $stmt = $conn->prepare($sql);
        $stmt->execute([$enrolment_id]);
        $dependants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    if (empty($dependants)) {
        die("Error: No dependants found for this enrollment.");
    }
    
    // Fetch principal info for organization/hcp
    $sql_principal = "SELECT organization_name, hcp FROM enrolment WHERE id = ?";
    if ($conn instanceof mysqli) {
        $stmt_p = $conn->prepare($sql_principal);
        $stmt_p->bind_param('i', $enrolment_id);
        $stmt_p->execute();
        $result_p = $stmt_p->get_result();
        $principal_info = $result_p->fetch_assoc();
        $stmt_p->close();
    } elseif ($conn instanceof PDO) {
        $stmt_p = $conn->prepare($sql_principal);
        $stmt_p->execute([$enrolment_id]);
        $principal_info = $stmt_p->fetch(PDO::FETCH_ASSOC);
    }
    
    // Generate cards for all dependants
    $html = "<!DOCTYPE html><html><head><meta charset='UTF-8'><style>body { margin: 0; padding: 20px; background: #f5f5f5; } .page-break { page-break-after: always; }</style></head><body>";
    
    foreach ($dependants as $index => $dep) {
        $dep_policy_no = $policy_no . '-DEP' . ($index + 1); // Generate dependant policy number
        
        $member_data = [
            'name' => $dep['name'],
            'policy_no' => $dep_policy_no,
            'relationship' => ucfirst($dep['relationship']),
            'dob' => $dep['dob'],
            'email' => '',
            'phone' => '',
            'organization_name' => $principal_info['organization_name'] ?? '',
            'hcp' => $principal_info['hcp'] ?? '',
            'photo_path' => null // Dependants may not have photos in current schema
        ];
        
        $card_html = generateIdentityCardHTML($member_data);
        // Extract body content
        preg_match('/<body>(.*)<\/body>/s', $card_html, $matches);
        $body_content = $matches[1] ?? '';
        
        $html .= "<div class='page-break'>" . $body_content . "</div>";
    }
    
    $html .= "</body></html>";
    
    // Output as HTML for print-to-PDF with navigation
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: inline; filename="ID_Cards_Dependants_' . sanitize_filename($policy_no) . '.html"');
    
    // Add navigation and styling
    echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width, initial-scale=1.0'><style>";
    echo "body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; }";
    echo ".nav-bar { background: #003366; color: white; padding: 15px 20px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px; }";
    echo ".nav-bar button { background: white; color: #003366; border: none; padding: 8px 16px; margin: 0 5px; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 14px; }";
    echo ".nav-bar button:hover { background: #e0e0e0; }";
    echo ".print-btn { background: #4CAF50; color: white; }";
    echo ".print-btn:hover { background: #45a049; }";
    echo "@media print { .nav-bar { display: none; } }";
    echo "</style></head><body>";
    echo "<div class='nav-bar'>";
    echo "<button onclick='window.history.back()'>← Back</button>";
    echo "<button class='print-btn' onclick='window.print()'>🖨️ Print / Save as PDF</button>";
    echo "<button onclick=\"window.open('create_registration.php')\">+ New Registration</button>";
    echo "</div>";
    echo $html;
    echo "</body></html>";
}

?>
