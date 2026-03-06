<?php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");

require_once __DIR__ . "/db.php";

try {
    $conn = db_conn();
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "msg" => "DB fail",
        "error" => $e->getMessage()
    ]);
    exit;
}

// DEBUG opcional
// file_put_contents("debug.txt", print_r($_POST, true));
// file_put_contents("debug_raw.txt", file_get_contents("php://input"));

$id  = $_POST["traker_id"] ?? null;
$lat = $_POST["lat"] ?? null;
$lon = $_POST["lon"] ?? null;

// Si POST viene vacío, intentar JSON
$data = null;
if ($id === null || $lat === null || $lon === null) {
    $raw = file_get_contents("php://input");
    $data = json_decode($raw, true);

    if (is_array($data)) {
        $id  = $data["traker_id"] ?? $id;
        $lat = $data["lat"] ?? $lat;
        $lon = $data["lon"] ?? $lon;
    }
}

if ($id === null || $lat === null || $lon === null || $id === '' || $lat === '' || $lon === '') {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "msg" => "Datos faltantes",
        "post" => $_POST,
        "json" => $data
    ]);
    exit;
}

$sql = "INSERT INTO ubicaciones (nombre, lat, lon)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE
            lat = VALUES(lat),
            lon = VALUES(lon)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $id, $lat, $lon);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "msg" => "Registro guardado o actualizado",
        "data" => [
            "id_usuario" => $id,
            "lat" => $lat,
            "lon" => $lon
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "msg" => $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>