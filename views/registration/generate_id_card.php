<?php
/**
 * Identity Card PDF Generator
 * Generates ID cards for enrolment members with photos
 */

// Check if TCPDF is available, if not provide fallback HTML
function generateIdentityCardPDF($member_data, $output_file = null) {
    $html = generateIdentityCardHTML($member_data);
    
    // Try using TCPDF if available
    if (class_exists('TCPDF')) {
        return generateWithTCPDF($html, $member_data, $output_file);
    } else {
        // Fallback: output HTML for browser print-to-PDF
        return $html;
    }
}

function generateIdentityCardHTML($member_data) {
    $name = htmlspecialchars($member_data['name'] ?? 'N/A');
    $policy_no = htmlspecialchars($member_data['policy_no'] ?? 'N/A');
    $relationship = htmlspecialchars($member_data['relationship'] ?? 'Principal');
    $dob = htmlspecialchars($member_data['dob'] ?? 'N/A');
    $photo_path = $member_data['photo_path'] ?? null;
    $email = htmlspecialchars($member_data['email'] ?? '');
    $phone = htmlspecialchars($member_data['phone'] ?? '');
    $organization = htmlspecialchars($member_data['organization_name'] ?? '');
    $hcp = htmlspecialchars($member_data['hcp'] ?? '');
    $generated_date = date('Y-m-d H:i:s');
    $this_date = date('Y-m-d');
    $is_principal = ($relationship === 'Principal');
    
    // Determine card colors based on member type - both professional colors
    $primary_color = $is_principal ? '#003366' : '#1B5E75';
    $light_color = $is_principal ? '#e3f2fd' : '#e0f2f7';
    $badge_bg = $is_principal ? '#003366' : '#1B5E75';
    
    $photo_html = '';
    if ($photo_path && file_exists($photo_path)) {
        $photo_base64 = base64_encode(file_get_contents($photo_path));
        $photo_ext = strtolower(pathinfo($photo_path, PATHINFO_EXTENSION));
        $photo_mime = ($photo_ext === 'png') ? 'image/png' : 'image/jpeg';
        $photo_html = "<img src='data:{$photo_mime};base64,{$photo_base64}' alt='Photo'>";
    } else {
        $photo_html = "<div style='display: flex; align-items: center; justify-content: center; font-size: 12px; color: #999;'>No Photo</div>";
    }
    
    // Build conditional info rows
    $email_row = $email ? "<div class='info-row'><span class='info-label'>Email:</span><span class='info-value'>{$email}</span></div>" : '';
    $phone_row = $phone ? "<div class='info-row'><span class='info-label'>Phone:</span><span class='info-value'>{$phone}</span></div>" : '';
    $org_row = $organization ? "<div class='info-row'><span class='info-label'>Organization:</span><span class='info-value'>{$organization}</span></div>" : '';
    $hcp_row = $hcp ? "<div class='info-row'><span class='info-label'>HCP:</span><span class='info-value'>{$hcp}</span></div>" : '';
    
    $html = <<<EOT
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: #f0f0f0;
                padding: 40px 20px;
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
            }
            .card-wrapper {
                perspective: 1000px;
                margin-bottom: 20px;
            }
            .id-card {
                width: 400px;
                height: 620px;
                background: linear-gradient(135deg, {$primary_color} 0%, {$primary_color}dd 100%);
                border-radius: 16px;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                overflow: hidden;
                position: relative;
                transform: translateZ(0);
                page-break-inside: avoid;
            }
            .card-inner {
                width: 100%;
                height: 100%;
                display: flex;
                flex-direction: column;
                padding: 0;
                position: relative;
            }
            .card-header {
                background: linear-gradient(90deg, {$primary_color}, {$primary_color}cc);
                padding: 20px;
                text-align: center;
                color: white;
                border-bottom: 3px solid rgba(255, 255, 255, 0.3);
                position: relative;
                overflow: hidden;
            }
            .card-header::before {
                content: '';
                position: absolute;
                top: -50%;
                right: -50%;
                width: 200px;
                height: 200px;
                background: rgba(255, 255, 255, 0.05);
                border-radius: 50%;
            }
            .card-header-content {
                position: relative;
                z-index: 2;
            }
            .hmo-logo {
                font-size: 24px;
                font-weight: bold;
                margin-bottom: 4px;
            }
            .hmo-name {
                font-size: 14px;
                font-weight: 600;
                letter-spacing: 2px;
                text-transform: uppercase;
                opacity: 0.95;
            }
            .member-type-badge {
                display: inline-block;
                background: rgba(255, 255, 255, 0.25);
                color: white;
                padding: 6px 14px;
                border-radius: 20px;
                font-size: 11px;
                font-weight: bold;
                text-transform: uppercase;
                letter-spacing: 1px;
                margin-top: 8px;
            }
            .card-body {
                flex: 1;
                padding: 20px;
                background: white;
                display: flex;
                flex-direction: column;
            }
            .photo-container {
                display: flex;
                justify-content: center;
                margin-bottom: 16px;
            }
            .photo-box {
                width: 100px;
                height: 130px;
                border: 3px solid {$primary_color};
                border-radius: 6px;
                overflow: hidden;
                background: {$light_color};
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .photo-box img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }
            .photo-placeholder {
                text-align: center;
                font-size: 11px;
                color: #999;
                width: 100%;
            }
            .member-info {
                flex: 1;
                display: flex;
                flex-direction: column;
            }
            .info-item {
                margin-bottom: 12px;
                border-bottom: 1px solid {$light_color};
                padding-bottom: 8px;
            }
            .info-item:last-of-type {
                border-bottom: none;
            }
            .info-label {
                font-size: 10px;
                font-weight: bold;
                color: {$primary_color};
                text-transform: uppercase;
                letter-spacing: 0.5px;
                margin-bottom: 2px;
            }
            .info-value {
                font-size: 13px;
                color: #333;
                font-weight: 500;
                word-break: break-word;
            }
            .policy-number-section {
                background: {$light_color};
                padding: 12px;
                border-radius: 8px;
                margin-top: 12px;
                border-left: 4px solid {$primary_color};
            }
            .policy-label {
                font-size: 9px;
                color: {$primary_color};
                font-weight: bold;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                margin-bottom: 4px;
            }
            .policy-value {
                font-size: 14px;
                font-weight: bold;
                color: {$primary_color};
                font-family: 'Courier New', monospace;
                letter-spacing: 0.3px;
                word-break: break-all;
            }
            .card-footer {
                background: {$primary_color};
                color: white;
                padding: 12px 20px;
                text-align: center;
                font-size: 9px;
                border-top: 1px solid rgba(255, 255, 255, 0.2);
            }
            .card-footer-text {
                font-size: 8px;
                opacity: 0.9;
                margin: 2px 0;
            }
            .security-line {
                margin-top: 4px;
                padding-top: 4px;
                border-top: 1px dashed rgba(255, 255, 255, 0.4);
                font-size: 7px;
                letter-spacing: 2px;
                font-weight: bold;
            }
            @media print {
                body {
                    background: white;
                    padding: 0;
                }
                .id-card {
                    margin: 20px auto;
                    box-shadow: none;
                }
            }
        </style>
    </head>
    <body>
        <div class="card-wrapper">
            <div class="id-card">
                <div class="card-inner">
                    <!-- Card Header -->
                    <div class="card-header">
                        <div class="card-header-content">
                            <div class="hmo-logo">🏥 NONSUCH</div>
                            <div class="hmo-name">Health Management Organization</div>
                            <div class="member-type-badge">{$relationship}</div>
                        </div>
                    </div>
                    
                    <!-- Card Body -->
                    <div class="card-body">
                        <!-- Photo Section -->
                        <div class="photo-container">
                            <div class="photo-box">
                                {$photo_html}
                            </div>
                        </div>
                        
                        <!-- Member Info -->
                        <div class="member-info">
                            <div class="info-item">
                                <div class="info-label">Full Name</div>
                                <div class="info-value">{$name}</div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-label">Date of Birth</div>
                                <div class="info-value">{$dob}</div>
                            </div>
                            
                            {$email_row}
                            {$phone_row}
                            {$org_row}
                            {$hcp_row}
                            
                            <div class="policy-number-section">
                                <div class="policy-label">Policy Number</div>
                                <div class="policy-value">{$policy_no}</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Card Footer -->
                    <div class="card-footer">
                        <div class="card-footer-text">✓ Valid HMO Membership Card</div>
                        <div class="card-footer-text">Issued: {$this_date}</div>
                        <div class="security-line">••••••••••••••••</div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    EOT;
    
    return $html;
}

function generateWithTCPDF($html, $member_data, $output_file) {
    require_once(dirname(__FILE__) . '/vendor/autoload.php');
    
    $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_PAGE_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('HMO System');
    $pdf->SetTitle('Identity Card - ' . $member_data['name']);
    
    $pdf->SetMargins(10, 10, 10);
    $pdf->SetAutoPageBreak(TRUE, 10);
    $pdf->AddPage();
    
    $pdf->writeHTML($html, true, false, true, false, '');
    
    if ($output_file) {
        $pdf->Output($output_file, 'F');
        return $output_file;
    } else {
        return $pdf->Output('ID_Card_' . sanitize_filename($member_data['name']) . '.pdf', 'S');
    }
}

function sanitize_filename($filename) {
    $filename = preg_replace("/[^A-Za-z0-9_-]/", '', $filename);
    return $filename ?: 'document';
}

?>
