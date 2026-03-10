<?php
header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . '/db.php';

try {
    $conn = db_conn();

    $ruta_id = $_POST['ruta_id'] ?? null;

    if (!$ruta_id) {
        echo json_encode([
            "success" => false,
            "message" => "ruta_id requerido"
        ]);
        exit;
    }

    $sql = "UPDATE rutas SET STAT_PED = 'C' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $ruta_id);
    $stmt->execute();

    echo json_encode([
        "success" => true,
        "message" => "Recorrido iniciado",
        "ruta_id" => (int)$ruta_id,
        "affected_rows" => $stmt->affected_rows
    ]);

    $stmt->close();
    $conn->close();

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}