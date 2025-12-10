<?php
// download_shared.php - FINAL VERSION WITH DEBUG
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/download_errors.log');

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
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Share link required']);
    exit();
}

$share_token = trim($_GET['share_link']);

// เชื่อมต่อฐานข้อมูล
$conn = new mysqli("localhost", "root", "", "file_management");
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

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
    header('Content-Type: application/json');
    echo json_encode(['error' => 'File not found or link has expired']);
    exit();
}

$file = $result->fetch_assoc();

// ลองหา path หลายแบบ
$possible_paths = [
    __DIR__ . '/uploads/' . $file['filepath'],
    __DIR__ . '/../uploads/' . $file['filepath'],
    __DIR__ . '/uploads/' . basename($file['filepath']),
    dirname(__DIR__) . '/uploads/' . $file['filepath'],
    dirname(__DIR__) . '/uploads/' . basename($file['filepath'])
];

$file_path = null;
$tried_paths = [];

foreach ($possible_paths as $path) {
    $tried_paths[] = $path;
    if (file_exists($path)) {
        $file_path = $path;
        break;
    }
}

// ตรวจสอบว่าไฟล์มีอยู่จริง
if (!$file_path) {
    http_response_code(404);
    header('Content-Type: application/json');
    
    // Debug: แสดง path ที่ลอง
    echo json_encode([
        'error' => 'File not found on server',
        'filepath_in_db' => $file['filepath'],
        'tried_paths' => $tried_paths,
        'current_dir' => __DIR__,
        'uploads_exists' => is_dir(__DIR__ . '/uploads'),
        'parent_uploads_exists' => is_dir(dirname(__DIR__) . '/uploads')
    ]);
    exit();
}

// ส่งไฟล์
header('Content-Type: ' . ($file['filetype'] ?: 'application/octet-stream'));
header('Content-Disposition: attachment; filename="' . basename($file['filename']) . '"');
header('Content-Length: ' . filesize($file_path));
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: public');

// ล้าง output buffer ก่อนส่งไฟล์
if (ob_get_level()) {
    ob_end_clean();
}

readfile($file_path);
exit;
?>