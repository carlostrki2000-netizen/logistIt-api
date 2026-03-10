<?php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");

$servername = getenv("MYSQLHOST");
$username   = getenv("MYSQLUSER");
$password   = getenv("MYSQLPASSWORD");
$dbname     = getenv("MYSQLDATABASE");
$port       = getenv("MYSQLPORT");

$conn = new mysqli($servername, $username, $password, $dbname, (int)$port);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["status" => "error", "msg" => "DB fail", "error" => $conn->connect_error]);
    exit;
}

$id_pedido = $_POST["id_pedido"] ?? null;

if (!$id_pedido) {
    http_response_code(400);
    echo json_encode(["status" => "error", "msg" => "Falta id_pedido"]);
    exit;
}

$stmt = $conn->prepare("
    UPDATE rutas 
    SET STAT_PED = 'E', hora_entrega = NOW() 
    WHERE pedido_id = ?
");
$stmt->bind_param("s", $id_pedido);

if ($stmt->execute()) {
    echo json_encode([
        "status"          => "success",
        "msg"             => "Pedido completado",
        "id_pedido"       => (int)$id_pedido,
        "filas_afectadas" => $stmt->affected_rows
    ]);
} else {
    http_response_code(500);
    echo json_encode(["status" => "error", "msg" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>