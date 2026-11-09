<?php
header("Content-Type: application/json; charset=utf-8");
header("Cache-Control: no-store, max-age=0");

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    header("Allow: GET");
    http_response_code(405);
    echo json_encode(["error" => "Gunakan metode GET."]);
    exit;
}

try {
    require __DIR__ . "/config/db.php";
    require_once __DIR__ . "/config/panggilan.php";
    echo json_encode(["antrian" => panggilan_aktif($conn)], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR);
} catch (Throwable $e) {
    error_log("Gagal membaca panggilan antrian: " . $e->getMessage());
    http_response_code(503);
    echo json_encode(["error" => "Data panggilan belum dapat dimuat."]);
}
