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

/* Allow POST only */
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "status" => "error",
        "message" => "POST method required"
    ]);
    exit;
}

/* 1️⃣ Read normal POST (Postman x-www-form-urlencoded) */
$data = $_POST;

/* 2️⃣ If empty, read raw JSON (Android / Postman raw) */
if (empty($data)) {
    $raw = file_get_contents("php://input");
    if (!empty($raw)) {
        $json = json_decode($raw, true);
        if (is_array($json)) {
            $data = $json;
        }
    }
}

/* If still empty */
if (empty($data)) {
    echo json_encode([
        "status" => "error",
        "message" => "No input received",
        "hint" => "Use x-www-form-urlencoded or raw JSON"
    ]);
    exit;
}

/* Read values */
$user_id = $data["user_id"] ?? "";
$streak_days = $data["streak_days"] ?? "";
$points = $data["points"] ?? "";

/* Validate */
if ($user_id === "" || $streak_days === "" || $points === "") {
    echo json_encode([
        "status" => "error",
        "message" => "Missing required fields",
        "received" => $data
    ]);
    exit;
}

/* Insert or update rewards */
$sql = "
INSERT INTO rewards (user_id, streak_days, points)
VALUES (?, ?, ?)
ON DUPLICATE KEY UPDATE
streak_days = VALUES(streak_days),
points = VALUES(points),
updated_at = CURRENT_TIMESTAMP
";

$stmt = $conn->prepare($sql);

if ($stmt === false) {
    echo json_encode([
        "status" => "error",
        "message" => "SQL prepare failed",
        "sql_error" => $conn->error
    ]);
    exit;
}

$stmt->bind_param("iii", $user_id, $streak_days, $points);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Rewards updated"
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Insert/Update failed",
        "sql_error" => $stmt->error
    ]);
}
