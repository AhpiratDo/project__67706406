<?php
// test_api.php - ทดสอบ API
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

echo json_encode([
    'success' => true,
    'message' => 'API is working!',
    'php_version' => phpversion(),
    'current_dir' => __DIR__,
    'test' => 'This is a test'
]);
?>