
<?php
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = '2xtreme';
$conn = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) { http_response_code(500); exit('DB connection failed'); }
$conn->set_charset('utf8mb4');
