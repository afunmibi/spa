<?php
// CLI migration: safely add package_id, indexes and foreign key only if they don't exist.
// Run from CLI: php tools\migrate_add_package_id.php

require_once __DIR__ . '/../db.php';

if (php_sapi_name() !== 'cli') {
    echo "This migration must be run from the command line.\n";
    exit(1);
}

$pdo = isset($pdo) ? $pdo : null;
if (!$pdo) {
    echo "PDO connection not available (check db.php).\n";
    exit(1);
}

function columnExists($pdo, $table, $column) {
    $sql = "SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table AND COLUMN_NAME = :col";
    $s = $pdo->prepare($sql);
    $s->execute([':table' => $table, ':col' => $column]);
    return (bool) $s->fetchColumn();
}

function constraintExists($pdo, $table, $constraintName) {
    $sql = "SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = :table AND CONSTRAINT_NAME = :cname";
    $s = $pdo->prepare($sql);
    $s->execute([':table' => $table, ':cname' => $constraintName]);
    return (bool) $s->fetchColumn();
}

function indexExists($pdo, $table, $indexName) {
    $sql = "SHOW INDEX FROM `" . $table . "`";
    try {
        $s = $pdo->query($sql);
        $rows = $s->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $r) {
            if (isset($r['Key_name']) && $r['Key_name'] === $indexName) return true;
        }
    } catch (Exception $e) {
        return false;
    }
    return false;
}

echo "Starting migration: add package_id and related indexes/constraints\n";

// 1) Add column package_id to enrolment if missing
if (!columnExists($pdo, 'enrolment', 'package_id')) {
    echo "Adding column enrolment.package_id... ";
    $pdo->exec("ALTER TABLE `enrolment` ADD COLUMN `package_id` INT NULL");
    echo "done\n";
} else {
    echo "Column enrolment.package_id already exists\n";
}

// 2) Add index on enrolment.package_id if missing
if (!indexExists($pdo, 'enrolment', 'idx_package_id')) {
    echo "Adding index enrolment.idx_package_id... ";
    $pdo->exec("CREATE INDEX idx_package_id ON `enrolment` (`package_id`)");
    echo "done\n";
} else {
    echo "Index enrolment.idx_package_id already exists\n";
}

// 3) Ensure packages.package_plan index exists
if (!indexExists($pdo, 'packages', 'idx_package_plan')) {
    echo "Adding index packages.idx_package_plan... ";
    // Use a prefix length to avoid very long index on TEXT fields; 60 is often safe
    $pdo->exec("ALTER TABLE `packages` ADD INDEX idx_package_plan (package_plan(60))");
    echo "done\n";
} else {
    echo "Index packages.idx_package_plan already exists\n";
}

// 4) Add foreign key constraint if not exists
$fkName = 'fk_enrolment_package';
if (!constraintExists($pdo, 'enrolment', $fkName)) {
    echo "Adding foreign key $fkName on enrolment(package_id) -> packages(id)... ";
    try {
        $pdo->exec("ALTER TABLE `enrolment` ADD CONSTRAINT $fkName FOREIGN KEY (`package_id`) REFERENCES `packages`(`id`) ON DELETE SET NULL");
        echo "done\n";
    } catch (Exception $e) {
        echo "failed: " . $e->getMessage() . "\n";
    }
} else {
    echo "Foreign key $fkName already exists\n";
}

echo "Migration finished. Please inspect output for any errors.\n";

return 0;

?>
