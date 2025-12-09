<?php
// download_shared.php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if (!isset($_GET['share_link'])) {
    http_response_code(400);
    die('Share link required');
}

$share_token = $_GET['share_link'];

// เชื่อมต่อฐานข้อมูล
require_once 'db.php';

// ดึงข้อมูลไฟล์จาก shared_links
$stmt = $conn->prepare("
    SELECT f.filename, f.filepath, f.filetype 
    FROM files f
    INNER JOIN shared_links sl ON f.id = sl.file_id
    WHERE sl.share_token = ?
    AND (sl.expires_at IS NULL OR sl.expires_at > NOW())
");
$stmt->bind_param("s", $share_token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    die('File not found or link has expired');
}

$file = $result->fetch_assoc();

// ลองหา path หลายแบบ
$possible_paths = [
    __DIR__ . '/' . $file['filepath'],
    __DIR__ . '/uploads/' . basename($file['filepath']),
    __DIR__ . '/../uploads/' . basename($file['filepath'])
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
    die('File not found on server');
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