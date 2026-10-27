<?php
// ملاحظة: شلنا echo "conn 200" وشلنا session_start لأنهم بخربوا الـ JSON

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'register_task');
define('DB_PORT', '3307');

class DB_con
{
    private $connect;

    public function __construct()
    {
        try {
            $this->connect = new PDO(
                "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        } catch (PDOException $e) {
            http_response_code(500);
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(["error" => "Database connection failed"]);
            exit;
        }
    }

    // READ (all)
    public function fetchdata()
    {
        return $this->connect->query("SELECT id, name, email FROM users")->fetchAll();
    }

    // READ (one)
    public function fetchOne($id)
    {
        $stmt = $this->connect->prepare("SELECT id, name, email FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // CREATE
    public function create($name, $email, $password)
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->connect->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $hash]);
        return $this->connect->lastInsertId();
    }

    // UPDATE (الباسورد اختياري: إذا فاضي ما بنغيره)
    public function update($id, $name, $email, $password = "")
    {
        if ($password !== "") {
            $stmt = $this->connect->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?");
            return $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $id]);
        }
        $stmt = $this->connect->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        return $stmt->execute([$name, $email, $id]);
    }

    // DELETE
    public function delete($id)
    {
        $stmt = $this->connect->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
