<?php
declare(strict_types=1);

/*
 * Full MySQL database backup
 * File name: dbname_ddmmyyhhmm.sql
 */

$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'smlaundry_db';

mysqli_report(MYSQLI_REPORT_OFF);

$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

if ($conn->connect_errno) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    exit("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset('utf8mb4');

$timestamp = date('dmyHi');
$fileName = $dbName . '_' . $timestamp . '.sql';

header('Content-Type: application/sql; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

echo "-- ======================================================\n";
echo "-- Database Backup: {$dbName}\n";
echo "-- Generated: " . date('Y-m-d H:i:s') . "\n";
echo "-- File: {$fileName}\n";
echo "-- ======================================================\n\n";
echo "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
echo "SET FOREIGN_KEY_CHECKS = 0;\n";
echo "SET NAMES utf8mb4;\n\n";

$tablesResult = $conn->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");

if (!$tablesResult) {
    exit("-- ERROR: Could not read database tables: " . $conn->error . "\n");
}

while ($tableRow = $tablesResult->fetch_row()) {
    $tableName = $tableRow[0];
    $safeTable = '`' . str_replace('`', '``', $tableName) . '`';

    echo "\n-- ------------------------------------------------------\n";
    echo "-- Table: {$tableName}\n";
    echo "-- ------------------------------------------------------\n\n";

    // Structure
    $createResult = $conn->query("SHOW CREATE TABLE {$safeTable}");
    if ($createResult && ($createRow = $createResult->fetch_assoc())) {
        $createSql = $createRow['Create Table'];
        echo "DROP TABLE IF EXISTS {$safeTable};\n";
        echo $createSql . ";\n\n";
    }

    // Data
    $dataResult = $conn->query("SELECT * FROM {$safeTable}");
    if ($dataResult && $dataResult->num_rows > 0) {
        $fields = $dataResult->fetch_fields();
        $columnNames = array_map(
            fn($field) => '`' . str_replace('`', '``', $field->name) . '`',
            $fields
        );
        $columnSql = implode(', ', $columnNames);

        while ($row = $dataResult->fetch_assoc()) {
            $values = [];
            foreach ($fields as $field) {
                $value = $row[$field->name];
                if ($value === null) {
                    $values[] = 'NULL';
                } else {
                    $values[] = "'" . $conn->real_escape_string($value) . "'";
                }
            }

            echo "INSERT INTO {$safeTable} ({$columnSql}) VALUES (" .
                 implode(', ', $values) . ");\n";
        }
        echo "\n";
    }
}

echo "\nSET FOREIGN_KEY_CHECKS = 1;\n";
echo "-- End of backup\n";

$conn->close();
exit;
?>
