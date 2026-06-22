<?php
// db.php
$host = "host";
$user = "user";
$pass = "password";
$dbname = "dbname";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    exit("Database connection failed.");
}
$conn->set_charset("utf8mb4");
?>
