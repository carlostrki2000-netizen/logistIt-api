<?php
header("Content-Type: application/json; charset=utf-8");

// CONEXION
function db_conn_pdo(): PDO {

require_once __DIR__ . '/db.php';

    return new PDO(
        "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
}

try {

    $db = db_conn_pdo();

    $ruta_id = $_POST['ruta_id'] ?? null;

    if(!$ruta_id){
        echo json_encode([
            "success"=>false,
            "message"=>"ruta_id requerido"
        ]);
        exit;
    }

    $sql = "UPDATE rutas 
            SET STAT_PED = 'C'
            WHERE id = :ruta_id";

    $stmt = $db->prepare($sql);
    $stmt->bindParam(":ruta_id",$ruta_id);
    $stmt->execute();

    echo json_encode([
        "success"=>true,
        "message"=>"Recorrido iniciado"
    ]);

} catch (Exception $e) {

    echo json_encode([
        "success"=>false,
        "message"=>$e->getMessage()
    ]);

}