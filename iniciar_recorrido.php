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

$id_ruta = $_POST["id_ruta"] ?? null;

if (!$id_ruta) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "msg" => "Falta id_ruta"
    ]);
    exit;
}

/*
  Actualiza todos los registros que pertenezcan a esa ruta
  y pone STAT_PED = 'C'
*/
$stmt = $conn->prepare("
    UPDATE rutas
    SET STAT_PED = 'C'
    WHERE id_ruta = ?
");

$stmt->bind_param("i", $id_ruta);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "msg" => "Recorrido iniciado correctamente",
        "id_ruta" => (int)$id_ruta,
        "filas_afectadas" => $stmt->affected_rows
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