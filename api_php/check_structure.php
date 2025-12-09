<?php
// check_structure.php - ไฟล์สำหรับตรวจสอบโครงสร้าง
header('Content-Type: application/json; charset=utf-8');

$info = [
    'current_dir' => __DIR__,
    'php_version' => phpversion(),
    'files_exist' => [
        'db.php' => file_exists(__DIR__ . '/db.php'),
        'upload_file.php' => file_exists(__DIR__ . '/upload_file.php'),
        'get_files.php' => file_exists(__DIR__ . '/get_files.php'),
        'download_file.php' => file_exists(__DIR__ . '/download_file.php'),
        'delete_file.php' => file_exists(__DIR__ . '/delete_file.php'),
        'create_share_link.php' => file_exists(__DIR__ . '/create_share_link.php'),
        'get_shared_file.php' => file_exists(__DIR__ . '/get_shared_file.php'),
        'download_shared.php' => file_exists(__DIR__ . '/download_shared.php'),
    ],
    'uploads_dir' => [
        'path' => __DIR__ . '/uploads',
        'exists' => is_dir(__DIR__ . '/uploads'),
        'writable' => is_writable(__DIR__ . '/uploads'),
        'files_count' => is_dir(__DIR__ . '/uploads') ? count(scandir(__DIR__ . '/uploads')) - 2 : 0
    ]
];

// เชื่อมต่อฐานข้อมูล
try {
    require_once 'db.php';
    $info['database'] = [
        'connected' => true,
        'host' => 'localhost',
        'database' => 'file_management'
    ];
    
    // นับจำนวนไฟล์ในฐานข้อมูล
    $result = $conn->query("SELECT COUNT(*) as count FROM files");
    $row = $result->fetch_assoc();
    $info['database']['files_count'] = $row['count'];
    
    // นับจำนวน share links
    $result = $conn->query("SELECT COUNT(*) as count FROM shared_links");
    $row = $result->fetch_assoc();
    $info['database']['shared_links_count'] = $row['count'];
    
    $conn->close();
} catch (Exception $e) {
    $info['database'] = [
        'connected' => false,
        'error' => $e->getMessage()
    ];
}

echo json_encode($info, JSON_PRETTY_PRINT);
?>