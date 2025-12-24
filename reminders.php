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

/* 1️⃣ Read normal POST */
$data = $_POST;

/* 2️⃣ If empty, read raw JSON */
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

/* Read fields safely */
$user_id = $data["user_id"] ?? "";
$time    = $data["time"] ?? "";
$message = $data["message"] ?? "";
$enabled = $data["enabled"] ?? 1;

/* Validate */
if ($user_id === "" || $time === "" || $message === "") {
    echo json_encode([
        "status" => "error",
        "message" => "Missing required fields",
        "received" => $data
    ]);
    exit;
}

/* Insert reminder */
$sql = "INSERT INTO reminders (user_id, reminder_time, message, enabled)
        VALUES (?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if ($stmt === false) {
    echo json_encode([
        "status" => "error",
        "message" => "SQL prepare failed",
        "sql_error" => $conn->error
    ]);
    exit;
}

$stmt->bind_param("issi", $user_id, $time, $message, $enabled);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Reminder saved"
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Insert failed",
        "sql_error" => $stmt->error
    ]);
}
