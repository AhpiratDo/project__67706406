<?php
// download_file.php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if (!isset($_GET['file_id'])) {
    http_response_code(400);
    die('File ID required');
}

$file_id = intval($_GET['file_id']);

// เชื่อมต่อฐานข้อมูล
require_once 'db.php';

// ดึงข้อมูลไฟล์
$stmt = $conn->prepare("SELECT filename, filepath, filetype FROM files WHERE id = ?");
$stmt->bind_param("i", $file_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    die('File not found in database');
}

$file = $result->fetch_assoc();

// ลองหา path หลายแบบ
$possible_paths = [
    __DIR__ . '/' . $file['filepath'],                    // uploads/xxx.jpg
    __DIR__ . '/uploads/' . basename($file['filepath']),  // uploads/xxx.jpg
    __DIR__ . '/../uploads/' . basename($file['filepath']) // ../uploads/xxx.jpg
];

$file_path = null;
foreach ($possible_paths as $path) {
    if (file_exists($path)) {
        $file_path = $path;
        break;
    }
}

// ตรวจสอบว่าไฟล์มีอยู่จริง
if (!$file_path) {
    http_response_code(404);
    error_log("File not found. Tried paths: " . implode(", ", $possible_paths));
    die('File not found on server. Filepath in DB: ' . $file['filepath']);
}

// ส่งไฟล์
header('Content-Type: ' . ($file['filetype'] ?: 'application/octet-stream'));
header('Content-Disposition: attachment; filename="' . basename($file['filename']) . '"');
header('Content-Length: ' . filesize($file_path));
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: public');

readfile($file_path);
exit;
?>