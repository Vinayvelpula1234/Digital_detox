<?php
header("Content-Type: application/json");
include __DIR__ . "/db.php";

/* Check DB connection */
if (!isset($conn)) {
    echo json_encode(["status"=>"error","message"=>"DB not connected"]);
    exit;
}

/* Allow POST only */
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["status"=>"error","message"=>"POST required"]);
    exit;
}

/* Check POST data */
if (empty($_POST)) {
    echo json_encode(["status"=>"error","message"=>"POST empty"]);
    exit;
}

$name = $_POST["name"] ?? "";
$email = $_POST["email"] ?? "";
$password = $_POST["password"] ?? "";

if ($name=="" || $email=="" || $password=="") {
    echo json_encode(["status"=>"error","message"=>"All fields required"]);
    exit;
}

/* Hash password */
$hash = password_hash($password, PASSWORD_BCRYPT);

/* Prepare SQL */
$sql = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);

/* 🔴 CRITICAL FIX */
if ($stmt === false) {
    echo json_encode([
        "status"=>"error",
        "message"=>"SQL prepare failed",
        "sql_error"=>$conn->error
    ]);
    exit;
}

/* Bind + execute */
$stmt->bind_param("sss", $name, $email, $hash);

if ($stmt->execute()) {
    echo json_encode(["status"=>"success","message"=>"Registered"]);
} else {
    echo json_encode([
        "status"=>"error",
        "message"=>"Insert failed",
        "sql_error"=>$stmt->error
    ]);
}
?>
