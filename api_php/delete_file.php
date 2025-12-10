<?php
// delete_file.php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// รับข้อมูล JSON
$input = json_decode(file_get_contents('php://input'), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid JSON: ' . json_last_error_msg()
    ]);
    exit();
}

if (!isset($input['file_id'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'File ID required'
    ]);
    exit();
}

$file_id = intval($input['file_id']);

// เชื่อมต่อฐานข้อมูล
require_once 'db.php';

// ดึงข้อมูลไฟล์
$stmt = $conn->prepare("SELECT filepath FROM files WHERE id = ?");
$stmt->bind_param("i", $file_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        'success' => false, 
        'message' => 'File not found'
    ]);
    exit();
}

$file = $result->fetch_assoc();

// ลองหา path หลายแบบ
$possible_paths = [
    __DIR__ . '/' . $file['filepath'],
    __DIR__ . '/uploads/' . basename($file['filepath']),
    __DIR__ . '/../uploads/' . basename($file['filepath'])
];

// ลบไฟล์จากระบบ
$file_deleted = false;
foreach ($possible_paths as $path) {
    if (file_exists($path)) {
        if (unlink($path)) {
            $file_deleted = true;
            break;
        }
    }
}

// ลบข้อมูลจากฐานข้อมูล (ไม่ว่าไฟล์จะลบได้หรือไม่)
$stmt = $conn->prepare("DELETE FROM files WHERE id = ?");
$stmt->bind_param("i", $file_id);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true, 
        'message' => 'File deleted successfully',
        'file_deleted' => $file_deleted
    ]);
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to delete file from database: ' . $conn->error
    ]);
}

$stmt->close();
$conn->close();
?>