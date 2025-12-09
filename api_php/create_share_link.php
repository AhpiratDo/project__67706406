<?php
// create_share_link.php
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
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false, 
        'message' => 'Method not allowed'
    ]);
    exit();
}

// อ่านข้อมูล JSON
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
        'message' => 'ไม่พบ file_id'
    ]);
    exit();
}

$file_id = intval($input['file_id']);

// ตรวจสอบว่าไฟล์มีอยู่จริง
$stmt = $conn->prepare("SELECT id, filename FROM files WHERE id = ?");
$stmt->bind_param("i", $file_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        'success' => false, 
        'message' => 'ไม่พบไฟล์'
    ]);
    exit();
}

// สร้าง share token
$share_token = bin2hex(random_bytes(16));
$expires_at = date('Y-m-d H:i:s', strtotime('+7 days'));

// ตรวจสอบว่ามี share link ที่ยังใช้ได้อยู่หรือไม่
$stmt = $conn->prepare("
    SELECT share_token, share_link 
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
        'share_link' => $row['share_link'],
        'message' => 'ใช้ลิงก์ที่มีอยู่แล้ว'
    ]);
} else {
    // สร้าง link ใหม่
    $share_link = "http://localhost:8080/shared/" . $share_token;
    
    $stmt = $conn->prepare("
        INSERT INTO shared_links (file_id, share_token, share_link, expires_at) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param("isss", $file_id, $share_token, $share_link, $expires_at);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'share_link' => $share_link,
            'expires_at' => $expires_at,
            'message' => 'สร้างลิงก์แชร์สำเร็จ'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'ไม่สามารถสร้างลิงก์แชร์ได้: ' . $conn->error
        ]);
    }
}

$stmt->close();
$conn->close();
?>