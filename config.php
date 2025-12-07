<?php
// config.php - central constants

// Upload paths (filesystem)
define('UPLOADS_BASE_DIR', __DIR__ . '/uploads');
define('UPLOADS_REGISTRATION_DIR', UPLOADS_BASE_DIR . '/registration');

// Web-accessible URI base (adjust if your site isn't served from /spa)
define('UPLOADS_URI_BASE', '/spa/uploads');
define('UPLOADS_REGISTRATION_URI', UPLOADS_URI_BASE . '/registration');

// Role constants
define('ROLE_SUPER_ADMIN', 'super_admin');
define('ROLE_ADMIN', 'admin');
define('ROLE_STAFF', 'staff');
define('ROLE_HOSPITAL', 'hospital');

// Allowed roles list
$ALLOWED_ROLES = [ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_STAFF, ROLE_HOSPITAL];

// Ensure upload directories exist
if (!is_dir(UPLOADS_BASE_DIR)) {
    @mkdir(UPLOADS_BASE_DIR, 0755, true);
}
if (!is_dir(UPLOADS_REGISTRATION_DIR)) {
    @mkdir(UPLOADS_REGISTRATION_DIR, 0755, true);
}

?>