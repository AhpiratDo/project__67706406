<?php
// ===== เพิ่ม CORS Headers ที่สมบูรณ์ =====
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

// ===== จัดการ Preflight Request (OPTIONS) =====
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// เชื่อมต่อฐานข้อมูล
$conn = new mysqli("localhost", "root", "", "file_management");
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode([
        "success" => false, 
        "message" => "เชื่อมต่อฐานข้อมูลล้มเหลว: " . $conn->connect_error
    ]));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ตรวจสอบข้อมูลที่จำเป็น
    if (!isset($_POST['user_id']) || !isset($_POST['filename']) || !isset($_FILES['file'])) {
        echo json_encode([
            "success" => false, 
            "message" => "ข้อมูลไม่ครบถ้วน"
        ]);
        exit();
    }

    $user_id = intval($_POST['user_id']);
    $filename = $_POST['filename'];
    $file = $_FILES['file'];
    
    // ตรวจสอบ error ของไฟล์
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode([
            "success" => false, 
            "message" => "เกิดข้อผิดพลาดในการอัปโหลด (Error: " . $file['error'] . ")"
        ]);
        exit();
    }
    
    // ใช้ path แบบ absolute
    $upload_dir = dirname(__DIR__) . "/uploads/";
    
    // สร้างชื่อไฟล์ไม่ซ้ำ
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $unique_filename = time() . "_" . uniqid() . "." . $file_extension;
    $target = $upload_dir . $unique_filename;
    
    // สร้างโฟลเดอร์ถ้ายังไม่มี
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0777, true)) {
            echo json_encode([
                "success" => false, 
                "message" => "ไม่สามารถสร้างโฟลเดอร์ uploads ได้ กรุณาสร้างด้วยตัวเอง"
            ]);
            exit();
        }
    }
    
    // ตรวจสอบสิทธิ์การเขียนไฟล์
    if (!is_writable($upload_dir)) {
        echo json_encode([
            "success" => false, 
            "message" => "ไม่มีสิทธิ์เขียนไฟล์ในโฟลเดอร์ uploads กรุณารัน: chmod 777 uploads"
        ]);
        exit();
    }
    
    if (move_uploaded_file($file['tmp_name'], $target)) {
        $filesize = $file['size'];
        $filetype = $file['type'];
        
        $sql = "INSERT INTO files (user_id, filename, filepath, filesize, filetype) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            echo json_encode([
                "success" => false, 
                "message" => "เตรียม statement ล้มเหลว: " . $conn->error
            ]);
            exit();
        }
        
        $stmt->bind_param("issis", $user_id, $filename, $unique_filename, $filesize, $filetype);
        
        if ($stmt->execute()) {
            echo json_encode([
                "success" => true, 
                "message" => "อัปโหลดไฟล์สำเร็จ ✅",
                "file_id" => $stmt->insert_id
            ]);
        } else {
            echo json_encode([
                "success" => false, 
                "message" => "บันทึกฐานข้อมูลล้มเหลว: " . $stmt->error
            ]);
        }
        $stmt->close();
    } else {
        echo json_encode([
            "success" => false, 
            "message" => "ย้ายไฟล์ล้มเหลว กรุณาตรวจสอบสิทธิ์โฟลเดอร์"
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        "success" => false, 
        "message" => "Method not allowed"
    ]);
}

$conn->close();
?>