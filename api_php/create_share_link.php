<?php
// create_share_link.php - FINAL VERSION
error_reporting(0); // ปิด error ไม่ให้แสดงใน output
ini_set('display_errors', 0);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// เชื่อมต่อฐานข้อมูล
$conn = new mysqli("localhost", "root", "", "file_management");
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed'
    ]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false, 
        'message' => 'Method not allowed'
    ]);
    exit();
}

// อ่านข้อมูล JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['file_id'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid request data'
    ]);
    exit();
}

$file_id = intval($input['file_id']);

// ตรวจสอบว่าไฟล์มีอยู่จริง
$stmt = $conn->prepare("SELECT id FROM files WHERE id = ?");
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

// ตรวจสอบว่ามี share link อยู่แล้วหรือไม่
$stmt = $conn->prepare("
    SELECT share_link 
    FROM shared_links 
    WHERE file_id = ? 
    AND (expires_at IS NULL OR expires_at > NOW())
    LIMIT 1
");
$stmt->bind_param("i", $file_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // ใช้ link เดิม
    $row = $result->fetch_assoc();
    echo json_encode([
        'success' => true,
        'share_link' => $row['share_link']
    ]);
} else {
    // สร้าง link ใหม่
    $share_token = bin2hex(random_bytes(16));
    $share_link = "http://localhost:8080/shared/" . $share_token;
    $expires_at = date('Y-m-d H:i:s', strtotime('+7 days'));
    
    $stmt = $conn->prepare("
        INSERT INTO shared_links (file_id, share_token, share_link, expires_at) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param("isss", $file_id, $share_token, $share_link, $expires_at);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'share_link' => $share_link
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to create share link'
        ]);
    }
}

$stmt->close();
$conn->close();
?>