<?php
// เชื่อมต่อฐานข้อมูล
include 'condb.php';

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// จัดการ preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === "GET") {
        // ✅ ดึงข้อมูลนักศึกษาทั้งหมด
        $stmt = $conn->prepare("SELECT * FROM students ORDER BY student_id DESC");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(["success" => true, "data" => $result]);
    }

    elseif ($method === "POST") {
        // ✅ เพิ่มข้อมูลนักศึกษาใหม่
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data["first_name"]) || !isset($data["last_name"])) {
            echo json_encode(["success" => false, "message" => "กรุณากรอกข้อมูลให้ครบถ้วน"]);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO students (first_name, last_name, email, phone) VALUES (:first_name, :last_name, :email, :phone)");
        $stmt->bindParam(":first_name", $data["first_name"]);
        $stmt->bindParam(":last_name", $data["last_name"]);
        $stmt->bindParam(":email", $data["email"]);
        $stmt->bindParam(":phone", $data["phone"]);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "เพิ่มข้อมูลเรียบร้อย"]);
        } else {
            echo json_encode(["success" => false, "message" => "ไม่สามารถเพิ่มข้อมูลได้"]);
        }
    }

    elseif ($method === "PUT") {
        // ✅ แก้ไขข้อมูลนักศึกษา
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data["student_id"])) {
            echo json_encode(["success" => false, "message" => "ไม่พบค่า student_id"]);
            exit;
        }

        $student_id = intval($data["student_id"]);

        $stmt = $conn->prepare("UPDATE students SET first_name = :first_name, last_name = :last_name, email = :email, phone = :phone WHERE student_id = :id");
        $stmt->bindParam(":first_name", $data["first_name"]);
        $stmt->bindParam(":last_name", $data["last_name"]);
        $stmt->bindParam(":email", $data["email"]);
        $stmt->bindParam(":phone", $data["phone"]);
        $stmt->bindParam(":id", $student_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "แก้ไขข้อมูลเรียบร้อย"]);
        } else {
            echo json_encode(["success" => false, "message" => "ไม่สามารถแก้ไขข้อมูลได้"]);
        }
    }

    elseif ($method === "DELETE") {
        // ✅ ลบข้อมูลนักศึกษาตาม student_id
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data["student_id"])) {
            echo json_encode(["success" => false, "message" => "ไม่พบค่า student_id"]);
            exit;
        }

        $student_id = intval($data["student_id"]);

        $stmt = $conn->prepare("DELETE FROM students WHERE student_id = :id");
        $stmt->bindParam(":id", $student_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "ลบข้อมูลเรียบร้อย"]);
        } else {
            echo json_encode(["success" => false, "message" => "ไม่สามารถลบข้อมูลได้"]);
        }
    }

    else {
        echo json_encode(["success" => false, "message" => "Method ไม่ถูกต้อง"]);
    }

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>