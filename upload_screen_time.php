<?php
header("Content-Type: application/json");
include __DIR__ . "/db.php";

/* Check DB */
if (!isset($conn)) {
    echo json_encode([
        "status" => "error",
        "message" => "DB connection missing"
    ]);
    exit;
}

/* Accept POST only */
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "status" => "error",
        "message" => "POST method required"
    ]);
    exit;
}

/* 1️⃣ Try normal POST (x-www-form-urlencoded) */
$data = $_POST;

/* 2️⃣ If POST empty, try raw JSON (Android / Postman raw) */
if (empty($data)) {
    $raw = file_get_contents("php://input");
    if (!empty($raw)) {
        $json = json_decode($raw, true);
        if (is_array($json)) {
            $data = $json;
        }
    }
}

/* If still empty → error */
if (empty($data)) {
    echo json_encode([
        "status" => "error",
        "message" => "No input received",
        "hint" => "Use x-www-form-urlencoded OR raw JSON"
    ]);
    exit;
}

/* Read values */
$user_id = $data["user_id"] ?? "";
$date = $data["date"] ?? "";
$total_minutes = $data["total_minutes"] ?? "";

/* Validate */
if ($user_id === "" || $date === "" || $total_minutes === "") {
    echo json_encode([
        "status" => "error",
        "message" => "Missing required fields",
        "received" => $data
    ]);
    exit;
}

/* Insert */
$sql = "INSERT INTO screen_time (user_id, date, total_minutes)
        VALUES (?, ?, ?)";

$stmt = $conn->prepare($sql);

if ($stmt === false) {
    echo json_encode([
        "status" => "error",
        "message" => "SQL prepare failed",
        "sql_error" => $conn->error
    ]);
    exit;
}

$stmt->bind_param("isi", $user_id, $date, $total_minutes);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Screen time uploaded"
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Insert failed",
        "sql_error" => $stmt->error
    ]);
}
