<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

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
        "message" => "เชื่อมต่อฐานข้อมูลล้มเหลว"
    ]));
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_GET['user_id'])) {
        echo json_encode([
            "success" => false, 
            "message" => "ไม่พบ user_id"
        ]);
        exit();
    }

    $user_id = intval($_GET['user_id']);

    $sql = "SELECT id, filename, filepath, filesize, filetype, upload_date 
            FROM files 
            WHERE user_id = ? 
            ORDER BY upload_date DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $files = [];
    while ($row = $result->fetch_assoc()) {
        $files[] = $row;
    }

    echo json_encode([
        "success" => true,
        "data" => $files
    ]);

    $stmt->close();
} else {
    http_response_code(405);
    echo json_encode([
        "success" => false, 
        "message" => "Method not allowed"
    ]);
}

$conn->close();
?>