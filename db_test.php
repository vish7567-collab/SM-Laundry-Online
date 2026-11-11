<?php
header("Content-Type: application/json; charset=UTF-8");
mysqli_report(MYSQLI_REPORT_OFF);

$host="localhost";
$user="root";
$pass="";
$dbname="smlaundry_db";

$conn = new mysqli($host,$user,$pass,$dbname);

if ($conn->connect_error) {
    echo json_encode([
        "success"=>false,
        "step"=>"connection",
        "message"=>$conn->connect_error,
        "database"=>$dbname
    ], JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
    exit;
}

$conn->set_charset("utf8mb4");

$check=$conn->query("SHOW TABLES LIKE 'orders'");
if (!$check || $check->num_rows===0) {
    echo json_encode([
        "success"=>false,
        "step"=>"table",
        "message"=>"orders table not found",
        "database"=>$dbname
    ], JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
    exit;
}

$r=$conn->query("SELECT COUNT(*) AS total FROM orders");
$total=$r ? (int)$r->fetch_assoc()["total"] : 0;

echo json_encode([
    "success"=>true,
    "database"=>$dbname,
    "orders_table"=>true,
    "rows"=>$total,
    "message"=>"Database connection successful"
], JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
?>
