<?php
header("Content-Type: application/json");

// include db.php from SAME folder
include __DIR__ . "/db.php";

// check DB connection
if (!isset($conn)) {
    echo json_encode([
        "status" => "error",
        "message" => "Database connection not found"
    ]);
    exit;
}

// allow POST only
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "status" => "error",
        "message" => "POST method required"
    ]);
    exit;
}

// check POST data
if (empty($_POST)) {
    echo json_encode([
        "status" => "error",
        "message" => "POST empty (use Postman x-www-form-urlencoded)"
    ]);
    exit;
}

$email = $_POST["email"] ?? "";
$password = $_POST["password"] ?? "";

if ($email === "" || $password === "") {
    echo json_encode([
        "status" => "error",
        "message" => "Email and password required"
    ]);
    exit;
}

// prepare SQL
$sql = "SELECT id, password FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    echo json_encode([
        "status" => "error",
        "message" => "SQL prepare failed",
        "sql_error" => $conn->error
    ]);
    exit;
}

// bind + execute
$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        "status" => "error",
        "message" => "User not found"
    ]);
    exit;
}

$row = $result->fetch_assoc();

// verify password
if (password_verify($password, $row["password"])) {
    echo json_encode([
        "status" => "successfull",
        "message" => "Login successful",
        "user_id" => $row["id"]
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid password"
    ]);
}
