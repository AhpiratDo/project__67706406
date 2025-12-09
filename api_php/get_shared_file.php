<?php
// get_shared_file.php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if (!isset($_GET['share_link'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ไม่พบ share link'
    ]);
    exit();
}

$share_token = $_GET['share_link'];

// เชื่อมต่อฐานข้อมูล
require_once 'db.php';

// ดึงข้อมูลไฟล์
$stmt = $conn->prepare("
    SELECT 
        f.id,
        f.filename,
        f.filepath,
        f.filetype,
        f.filesize,
        f.upload_date,
        sl.expires_at
    FROM files f
    INNER JOIN shared_links sl ON f.id = sl.file_id
    WHERE sl.share_token = ?
    AND (sl.expires_at IS NULL OR sl.expires_at > NOW())
");
$stmt->bind_param("s", $share_token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'ไม่พบไฟล์หรือลิงก์หมดอายุแล้ว'
    ]);
    exit();
}

$file = $result->fetch_assoc();

echo json_encode([
    'success' => true,
    'data' => [
        'id' => $file['id'],
        'filename' => $file['filename'],
        'filepath' => $file['filepath'],
        'filetype' => $file['filetype'],
        'filesize' => $file['filesize'],
        'upload_date' => $file['upload_date'],
        'expires_at' => $file['expires_at']
    ]
]);

$stmt->close();
$conn->close();
?>