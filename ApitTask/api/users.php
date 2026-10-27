<?php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// طلب الـ preflight اللي بيبعته المتصفح قبل PUT و DELETE
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require "db.php";
$db = new DB_con();

$method = $_SERVER['REQUEST_METHOD'];
$input  = json_decode(file_get_contents("php://input"), true) ?? [];
$id     = $_GET['id'] ?? ($input['id'] ?? null);

function respond($data, $code = 200)
{
    http_response_code($code);
    echo json_encode($data);
    exit;
}

try {
    switch ($method) {

        case 'GET':
            if ($id) {
                $user = $db->fetchOne($id);
                if (!$user) respond(["error" => "User not found"], 404);
                respond($user);
            }
            respond($db->fetchdata());
            break;

        case 'POST':
            $name     = trim($input['name'] ?? "");
            $email    = trim($input['email'] ?? "");
            $password = $input['password'] ?? "";

            if ($name === "" || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
                respond(["error" => "Invalid data (name, valid email, password min 8 chars)"], 422);
            }
            $newId = $db->create($name, $email, $password);
            respond(["message" => "User created", "id" => $newId], 201);
            break;

        case 'PUT':
            if (!$id) respond(["error" => "ID is required"], 400);

            $name     = trim($input['name'] ?? "");
            $email    = trim($input['email'] ?? "");
            $password = $input['password'] ?? "";

            if ($name === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                respond(["error" => "Invalid name or email"], 422);
            }
            if ($password !== "" && strlen($password) < 8) {
                respond(["error" => "Password must be at least 8 chars"], 422);
            }
            $db->update($id, $name, $email, $password);
            respond(["message" => "User updated"]);
            break;

        case 'DELETE':
            if (!$id) respond(["error" => "ID is required"], 400);
            $db->delete($id);
            respond(["message" => "User deleted"]);
            break;

        default:
            respond(["error" => "Method not allowed"], 405);
    }
} catch (PDOException $e) {
    // مثلاً إيميل مكرر
    respond(["error" => "Database error"], 500);
}
