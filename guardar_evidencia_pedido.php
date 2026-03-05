<?php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");

$conn = new mysqli("localhost","root","root","rastreo");
if ($conn->connect_error) {
  echo json_encode(["status"=>"error","msg"=>"DB Error"]);
  exit;
}

$id_ruta    = $_POST["id_ruta"] ?? null;
$cve_pedido = $_POST["cve_pedido"] ?? null;
$comentario = $_POST["comentario"] ?? "";

if (!$id_ruta || !$cve_pedido) {
  echo json_encode(["status"=>"error","msg"=>"Falta id_ruta o cve_pedido"]);
  exit;
}

if (!isset($_FILES["foto"]) || $_FILES["foto"]["error"] !== UPLOAD_ERR_OK) {
  echo json_encode(["status"=>"error","msg"=>"Falta foto o error al subir"]);
  exit;
}

// Validación básica de tipo
$allowed = ["image/jpeg"=>"jpg","image/png"=>"png","image/webp"=>"webp"];
$mime = mime_content_type($_FILES["foto"]["tmp_name"]);
if (!isset($allowed[$mime])) {
  echo json_encode(["status"=>"error","msg"=>"Formato no permitido"]);
  exit;
}
$ext = $allowed[$mime];

// Carpeta donde guardar
$dir = __DIR__ . "/uploads/evidencias_pedidos/";
if (!is_dir($dir)) mkdir($dir, 0755, true);

// Limpiar cve_pedido para filename seguro
$safePedido = preg_replace('/[^A-Za-z0-9_-]/', '_', $cve_pedido);

$filename = "ruta_" . intval($id_ruta) . "_pedido_" . $safePedido . "_" . date("Ymd_His") . "." . $ext;
$dest = $dir . $filename;

if (!move_uploaded_file($_FILES["foto"]["tmp_name"], $dest)) {
  echo json_encode(["status"=>"error","msg"=>"No se pudo guardar la foto"]);
  exit;
}

// URL relativa (igual que tu ejemplo)
$foto_url = "uploads/evidencias_pedidos/" . $filename;

// UPSERT por (id_ruta, cve_pedido) (requiere UNIQUE uq_ruta_pedido)
$stmt = $conn->prepare(
  "INSERT INTO evidencia_pedido (id_ruta, cve_pedido, comentario, foto_url)
   VALUES (?, ?, ?, ?)
   ON DUPLICATE KEY UPDATE comentario=VALUES(comentario), foto_url=VALUES(foto_url)"
);

$stmt->bind_param("isss", $id_ruta, $cve_pedido, $comentario, $foto_url);

if ($stmt->execute()) {
  echo json_encode(["status"=>"success","msg"=>"Evidencia de pedido guardada","foto_url"=>$foto_url]);
} else {
  echo json_encode(["status"=>"error","msg"=>$stmt->error]);
}

$stmt->close();
$conn->close();
?>
