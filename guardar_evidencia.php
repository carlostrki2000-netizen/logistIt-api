<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$conn = new mysqli("localhost","root","root","rastreo");
if ($conn->connect_error) {
  echo json_encode(["status"=>"error","msg"=>"DB Error"]);
  exit;
}

$id_ruta = $_POST["id_ruta"] ?? null;
$comentario = $_POST["comentario"] ?? "";

if (!$id_ruta) {
  echo json_encode(["status"=>"error","msg"=>"Falta id_ruta"]);
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
$dir = __DIR__ . "/uploads/evidencias/";
if (!is_dir($dir)) mkdir($dir, 0755, true);

$filename = "ruta_" . intval($id_ruta) . "_" . date("Ymd_His") . "." . $ext;
$dest = $dir . $filename;

if (!move_uploaded_file($_FILES["foto"]["tmp_name"], $dest)) {
  echo json_encode(["status"=>"error","msg"=>"No se pudo guardar la foto"]);
  exit;
}

$foto_url = "uploads/evidencias/" . $filename;

// UPSERT por id_ruta (porque pusimos UNIQUE)
$stmt = $conn->prepare(
  "INSERT INTO evidencia_unidad (id_ruta, comentario, foto_url)
   VALUES (?, ?, ?)
   ON DUPLICATE KEY UPDATE comentario=VALUES(comentario), foto_url=VALUES(foto_url)"
);
$stmt->bind_param("iss", $id_ruta, $comentario, $foto_url);

if ($stmt->execute()) {
  echo json_encode(["status"=>"success","msg"=>"Evidencia guardada","foto_url"=>$foto_url]);
} else {
  echo json_encode(["status"=>"error","msg"=>$stmt->error]);
}

$stmt->close();
$conn->close();
?>