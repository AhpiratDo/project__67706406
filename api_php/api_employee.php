<?php

include 'condb.php';

$action = $_POST['action'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action) {
    // เพิ่ม / แก้ไข / ลบ
    switch($action) {

        case 'add':
            $first_name = $_POST['first_name'];
            $last_name = $_POST['last_name'];
            $username = $_POST['username'];
            $password = $_POST['password'];
            
            // เข้ารหัสรหัสผ่าน
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            // อัพโหลดไฟล์รูป
            $filename = null;
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
                $targetDir = "uploads/";
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }
                $filename = time() . '_' . basename($_FILES['profile_image']['name']);
                $targetFile = $targetDir . $filename;
                move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetFile);
            }

            $sql = "INSERT INTO employees (first_name, last_name, username, password, profile_image)
                    VALUES (:first_name, :last_name, :username, :password, :profile_image)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':first_name', $first_name);
            $stmt->bindParam(':last_name', $last_name);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':profile_image', $filename);

            if ($stmt->execute()) {
                echo json_encode(["message" => "เพิ่มพนักงานสำเร็จ"]);
            } else {
                echo json_encode(["error" => "เพิ่มพนักงานล้มเหลว"]);
            }
            break;

        case 'update':
            $employee_id = $_POST['employee_id'];
            $first_name = $_POST['first_name'];
            $last_name = $_POST['last_name'];
            $username = $_POST['username'];
            $password = $_POST['password'] ?? null;

            // อัพโหลดไฟล์รูป
            $imageSQL = "";
            $filename = null;
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
                $targetDir = "uploads/";
                $filename = time() . '_' . basename($_FILES['profile_image']['name']);
                $targetFile = $targetDir . $filename;
                move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetFile);
                $imageSQL = ", profile_image = :profile_image";
            }

            // ตรวจสอบว่าต้องอัปเดตรหัสผ่านหรือไม่
            $passwordSQL = "";
            if ($password) {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $passwordSQL = ", password = :password";
            }

            $sql = "UPDATE employees SET 
                        first_name = :first_name,
                        last_name = :last_name,
                        username = :username
                        $passwordSQL
                        $imageSQL
                    WHERE employee_id = :employee_id";
            $stmt = $conn->prepare($sql);

            $stmt->bindParam(':first_name', $first_name);
            $stmt->bindParam(':last_name', $last_name);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':employee_id', $employee_id);
            if ($password) $stmt->bindParam(':password', $hashed_password);
            if ($filename) $stmt->bindParam(':profile_image', $filename);

            if ($stmt->execute()) {
                echo json_encode(["message" => "แก้ไขพนักงานสำเร็จ"]);
            } else {
                echo json_encode(["error" => "แก้ไขพนักงานล้มเหลว"]);
            }
            break;

        case 'delete':
            $employee_id = $_POST['employee_id'];
            $stmt = $conn->prepare("DELETE FROM employees WHERE employee_id = :employee_id");
            $stmt->bindParam(':employee_id', $employee_id);
            if ($stmt->execute()) {
                echo json_encode(["message" => "ลบพนักงานสำเร็จ"]);
            } else {
                echo json_encode(["error" => "ลบพนักงานล้มเหลว"]);
            }
            break;

        default:
            echo json_encode(["error" => "Action ไม่ถูกต้อง"]);
            break;
    }

} else {
    // GET: ดึงข้อมูลพนักงาน
    $stmt = $conn->prepare("SELECT employee_id, first_name, last_name, username, profile_image, created_at FROM employees ORDER BY employee_id DESC");
    if ($stmt->execute()) {
        $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["success" => true, "data" => $employees]);
    } else {
        echo json_encode(["success" => false, "data" => []]);
    }
}
?>